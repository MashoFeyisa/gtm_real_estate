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
        $order->loadMissing(['property', 'agent']);

        $pdf = Pdf::loadView('agreements.document', [
            'order' => $order,
            'property' => $order->property,
            'agent' => $order->agent,
            'company' => $this->company(),
            'signature' => $this->signature($order),
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
}
