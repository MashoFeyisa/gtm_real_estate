<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your {{ $order->isRental() ? 'rental' : 'buy' }} request was received</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f1e8; color: #1d3c34; margin: 0; padding: 24px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #d9cab3;">
        <h2 style="margin-top: 0; color: #1d3c34;">Thank you, {{ $order->name }}!</h2>

        <p>We received your {{ $order->isRental() ? 'rental' : 'purchase' }} request. Our agent will review it and contact you shortly.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 16px 0;" cellpadding="8">
            <tr style="background: #f8f3eb;">
                <td style="border: 1px solid #e7ddca; font-weight: bold;">Property</td>
                <td style="border: 1px solid #e7ddca;">{{ $order->property?->title ?? 'Property removed' }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #e7ddca; font-weight: bold;">{{ $order->isRental() ? 'Monthly rent' : 'Offer' }}</td>
                <td style="border: 1px solid #e7ddca;">ETB {{ number_format($order->offer_amount ?? 0, 2) }}@if ($order->isRental()) per month @endif</td>
            </tr>
        </table>

        <p style="margin-top: 20px;">
            <a href="{{ route('properties.show', $order->property?->slug ?? '') }}" style="display: inline-block; background: #1d3c34; color: #ffffff; padding: 10px 20px; border-radius: 999px; text-decoration: none; font-weight: bold;">View Property</a>
        </p>

        <p style="margin-top: 16px; font-size: 12px; color: #6b7f76;">{{ $siteBrand['name'] }} · {{ $siteBrand['address'] }}</p>
    </div>
</body>
</html>
