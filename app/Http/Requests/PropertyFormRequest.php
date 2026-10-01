<?php

namespace App\Http\Requests;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class PropertyFormRequest extends FormRequest
{
    /**
     * Shared validation rules for creating and updating properties,
     * used by both the admin dashboard and the agent portal.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'bedrooms' => ['required', 'integer', 'min:0'],
            'bathrooms' => ['required', 'integer', 'min:0'],
            'area' => ['required', 'integer', 'min:0'],
            'type' => ['required', 'in:sale,rent'],
            'category' => ['nullable', 'in:villa,home,apartment'],
            'status' => ['required', 'in:draft,published,archived,available,sold,rented'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'agent_id' => ['nullable', 'exists:agents,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
            'cover_image_id' => ['nullable', 'integer', 'exists:property_images,id'],
            'cover_preset' => ['nullable', 'string', 'in:hero-skyline,luxury-towers,central-plaza,panoramic-park,retail-boulevard'],
            'images' => ['nullable', 'array', 'max:50'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:20480'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:property_images,id'],
        ];
    }

    /**
     * Persist the uploaded listing primary image, replacing any previous file.
     */
    public function storeImage(?string $currentPath = null): ?string
    {
        // 1. If dedicated cover_image file was uploaded
        if ($this->hasFile('cover_image')) {
            if ($currentPath && Storage::disk('public')->exists($currentPath) && ! str_starts_with($currentPath, 'images/')) {
                Storage::disk('public')->delete($currentPath);
            }

            return $this->file('cover_image')->store('properties', 'public');
        }

        // 2. If single legacy 'image' file was uploaded
        if ($this->hasFile('image')) {
            if ($currentPath && Storage::disk('public')->exists($currentPath) && ! str_starts_with($currentPath, 'images/')) {
                Storage::disk('public')->delete($currentPath);
            }

            return $this->file('image')->store('properties', 'public');
        }

        // 3. If an existing gallery image was chosen as cover
        if ($this->filled('cover_image_id')) {
            $selectedCover = PropertyImage::find($this->input('cover_image_id'));
            if ($selectedCover && $selectedCover->image_path) {
                return $selectedCover->image_path;
            }
        }

        // 4. If a luxury background preset was selected
        if ($this->filled('cover_preset')) {
            $preset = (string) $this->input('cover_preset');
            $allowed = ['hero-skyline', 'luxury-towers', 'central-plaza', 'panoramic-park', 'retail-boulevard'];
            if (in_array($preset, $allowed, true)) {
                return 'images/luxury/'.$preset.'.jpg';
            }
        }

        return $currentPath;
    }

    /**
     * Synchronize all gallery images for the given property.
     */
    public function syncPropertyImages(Property $property): void
    {
        // 1. Delete requested images
        if ($this->has('delete_images') && is_array($this->input('delete_images'))) {
            $imagesToDelete = $property->images()->whereIn('id', $this->input('delete_images'))->get();
            foreach ($imagesToDelete as $img) {
                if ($img->image_path && Storage::disk('public')->exists($img->image_path) && ! str_starts_with($img->image_path, 'images/')) {
                    Storage::disk('public')->delete($img->image_path);
                }
                $img->delete();
            }
        }

        $maxOrder = (int) $property->images()->max('sort_order');

        // 2. If single 'cover_image' or 'image' file was stored, ensure it is represented in property_images
        if ($property->image_path && ! $property->images()->where('image_path', $property->image_path)->exists()) {
            $property->images()->create([
                'image_path' => $property->image_path,
                'sort_order' => 1,
            ]);
            $maxOrder = max($maxOrder, 1);
        }

        // 3. Process multiple uploaded files
        if ($this->hasFile('images')) {
            $files = $this->file('images');
            if (is_array($files)) {
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('properties', 'public');
                        $property->images()->create([
                            'image_path' => $path,
                            'sort_order' => ++$maxOrder,
                        ]);

                        if (! $property->image_path) {
                            $property->update(['image_path' => $path]);
                        }
                    }
                }
            }
        }

        // 4. If cover_image_id was explicitly selected from existing gallery
        if ($this->filled('cover_image_id')) {
            $selectedCover = $property->images()->find($this->input('cover_image_id'));
            if ($selectedCover) {
                $property->update(['image_path' => $selectedCover->image_path]);
                $selectedCover->update(['sort_order' => 1]);
            }
        }

        // 5. If cover_preset was selected and not yet represented in images
        if ($this->filled('cover_preset')) {
            $presetPath = 'images/luxury/'.$this->input('cover_preset').'.jpg';
            $property->update(['image_path' => $presetPath]);
            if (! $property->images()->where('image_path', $presetPath)->exists()) {
                $property->images()->create([
                    'image_path' => $presetPath,
                    'sort_order' => 1,
                ]);
            }
        }

        // 6. Ensure legacy or preset property with image_path has at least one record in property_images
        if ($property->image_path && $property->images()->count() === 0) {
            $property->images()->create([
                'image_path' => $property->image_path,
                'sort_order' => 1,
            ]);
        }

        // 7. If image_path was deleted or empty, set it to the first remaining image or best default luxury background
        $coverStillExists = $property->image_path && (
            str_starts_with($property->image_path, 'images/') ||
            $property->images()->where('image_path', $property->image_path)->exists()
        );

        if (! $coverStillExists) {
            $firstImg = $property->images()->first();
            $newCover = $firstImg?->image_path ?: $property->default_background_image;
            $property->update(['image_path' => $newCover]);

            if ($property->images()->count() === 0) {
                $property->images()->create([
                    'image_path' => $newCover,
                    'sort_order' => 1,
                ]);
            }
        }
    }
}
