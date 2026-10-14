<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageSecurityService
{
    /**
     * Strip EXIF, GPS coordinates, and camera metadata from an image file.
     *
     * @param  string  $absolutePath  The absolute path on disk to the image
     * @return bool True if stripping was applied, false if format unsupported or error
     */
    public static function stripExif(string $absolutePath): bool
    {
        if (! file_exists($absolutePath) || ! is_readable($absolutePath)) {
            return false;
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return false;
        }

        if (! extension_loaded('gd')) {
            return false;
        }

        try {
            $image = match ($extension) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($absolutePath),
                'png' => @imagecreatefrompng($absolutePath),
                'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
                default => false,
            };

            if (! $image) {
                return false;
            }

            // Re-encoding through GD strips all EXIF headers and GPS tags
            match ($extension) {
                'jpg', 'jpeg' => imagejpeg($image, $absolutePath, 90),
                'png' => imagepng($image, $absolutePath, 8),
                'webp' => function_exists('imagewebp') ? imagewebp($image, $absolutePath, 90) : false,
            };

            imagedestroy($image);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Store an uploaded image on disk and automatically strip all EXIF/GPS metadata.
     *
     * @param  UploadedFile  $file  Uploaded file instance
     * @param  string  $directory  Storage folder e.g. 'properties/gallery'
     * @param  string  $disk  Storage disk (defaults to 'public')
     * @return string Stored relative path
     */
    public static function storeAndStrip(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $path = $file->store($directory, $disk);
        $absolutePath = Storage::disk($disk)->path($path);

        self::stripExif($absolutePath);

        return $path;
    }
}
