<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;

class AgreementPdfService
{
    /**
     * Generate a branded buy or rent agreement PDF for an accepted order.
     */
    public function generate(Order $order): Order
    {
        $order->loadMissing(['property.agent', 'agent']);

        $template = $order->isRental()
            ? 'agreements.rental-amharic'
            : 'agreements.purchase-amharic';

        $company = $this->company();
        $poster = $order->property?->agent ?? $order->agent;

        $seller = [
            'name' => $poster?->name ?? $company['name'],
            'phone' => $poster?->phone ?: $company['phone'],
            'email' => $poster?->email ?: $company['email'],
            'address' => ($order->property?->city ? ($order->property->city.' · '.$order->property->address) : null) ?: $company['address'],
        ];

        $pdf = Pdf::loadView($template, [
            'order' => $order,
            'property' => $order->property,
            'agent' => $order->agent,
            'seller' => $seller,
            'company' => $company,
            'signature' => $this->signature($order),
            'overrides' => $order->agreement_content ?? [],
        ]);

        $path = 'agreements/order-'.$order->id.'-'.now()->format('YmdHis').'.pdf';

        Storage::disk('local')->put($path, $pdf->output());

        // Remove any previous agreement so the latest version is the one served.
        if ($order->agreement_path && $order->agreement_path !== $path) {
            Storage::disk('local')->delete($order->agreement_path);
        }

        $order->forceFill([
            'agreement_path' => $path,
            'agreed_at' => now(),
        ])->save();

        return $order->refresh();
    }

    /**
     * Build the digital signature details embedded in the agreement.
     *
     * @return array{signed_at: CarbonInterface, verification: string, method: string}
     */
    private function signature(Order $order): array
    {
        $fingerprint = implode('|', [
            $order->id,
            $order->type,
            $order->email,
            $order->property_id,
            $order->offer_amount,
            $order->agent_id,
        ]);

        $verification = strtoupper(substr(hash('sha256', $fingerprint), 0, 20));
        $verification = implode('-', str_split($verification, 5));

        return [
            'signed_at' => $order->agreed_at ?? now(),
            'verification' => $verification,
            'method' => 'Electronic acceptance on the '.SiteSetting::get('site_name', 'GTP Real Estate').' portal',
        ];
    }

    private function company(): array
    {
        return [
            'name' => SiteSetting::get('site_name', 'GTP Real Estate'),
            'tagline' => 'Real Estate — Buy · Rent · Invest',
            'email' => SiteSetting::get('contact_email', 'gtmrealstate@gmail.com'),
            'phone' => SiteSetting::get('contact_phone', '+251 993722346'),
            'address' => SiteSetting::get('contact_address', 'Harar, Ethiopia'),
            'logo' => public_path(SiteSetting::get('site_logo', 'images/logo1.jpg')),
        ];
    }

    /**
     * Build the editable content fields with defaults from order/property data.
     *
     * Used to pre-populate the admin edit form before generating the PDF.
     *
     * @return array<string, string|null>
     */
    public function getEditableContent(Order $order): array
    {
        $order->loadMissing(['property.agent', 'agent']);

        $company = $this->company();
        $poster = $order->property?->agent ?? $order->agent;

        $sellerName = $poster?->name ?? $company['name'];
        $sellerPhone = $poster?->phone ?: $company['phone'];
        $sellerEmail = $poster?->email ?: $company['email'];
        $sellerAddress = ($order->property?->city ? ($order->property->city.' · '.$order->property->address) : null) ?: $company['address'];

        $buyerName = $order->name;
        $buyerEmail = $order->email;
        $buyerPhone = $order->phone ?? '';

        $propertyTitle = $order->property?->title ?? '';
        $propertyCity = $order->property?->city ?? '';
        $propertyAddress = trim(($order->property?->city ?? '').' '.($order->property?->address ?? ''));
        $propertyCategory = ucfirst($order->property?->property_category ?? 'ቤት');
        $bedrooms = (string) ($order->property?->bedrooms ?? '');
        $bathrooms = (string) ($order->property?->bathrooms ?? '');
        $area = $order->property?->area ? number_format($order->property->area) : '';

        $offerAmount = number_format($order->offer_amount ?? $order->property?->price ?? 0, 2);
        $leaseMonths = (string) ($order->lease_months ?? 12);

        $specialTerms = $order->message ?? '';

        return [
            'seller_name' => $sellerName,
            'seller_phone' => $sellerPhone,
            'seller_email' => $sellerEmail,
            'seller_address' => $sellerAddress,
            'buyer_name' => $buyerName,
            'buyer_email' => $buyerEmail,
            'buyer_phone' => $buyerPhone,
            'property_title' => $propertyTitle,
            'property_city' => $propertyCity,
            'property_address' => $propertyAddress,
            'property_category' => $propertyCategory,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'area' => $area,
            'offer_amount' => $offerAmount,
            'lease_months' => $leaseMonths,
            'special_terms' => $specialTerms,
            'agent_note' => $order->agent_note ?? '',
        ];
    }
}
