<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New {{ $order->isRental() ? 'rental' : 'buy' }} request</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f1e8; color: #1d3c34; margin: 0; padding: 24px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #d9cab3;">
        <h2 style="margin-top: 0; color: #1d3c34;">New {{ $order->isRental() ? 'rental' : 'buy' }} request</h2>

        <p>A client just requested to <strong>{{ $order->isRental() ? 'rent' : 'buy' }}</strong> one of your listings. Sign in to your agent portal to accept or reject it.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 16px 0;" cellpadding="8">
            <tr style="background: #f8f3eb;">
                <td style="border: 1px solid #e7ddca; font-weight: bold;">Property</td>
                <td style="border: 1px solid #e7ddca;">{{ $order->property?->title ?? 'Property removed' }}</td>
            </tr>
            <tr>
                <td style="border: 1px solid #e7ddca; font-weight: bold;">Client</td>
                <td style="border: 1px solid #e7ddca;">{{ $order->name }} ({{ $order->email }})@if ($order->phone) · {{ $order->phone }}@endif</td>
            </tr>
            <tr style="background: #f8f3eb;">
                <td style="border: 1px solid #e7ddca; font-weight: bold;">{{ $order->isRental() ? 'Monthly rent' : 'Offer' }}</td>
                <td style="border: 1px solid #e7ddca;">${{ number_format($order->offer_amount ?? 0, 2) }}@if ($order->isRental()) per month @endif</td>
            </tr>
            @if ($order->isRental())
                <tr>
                    <td style="border: 1px solid #e7ddca; font-weight: bold;">Lease</td>
                    <td style="border: 1px solid #e7ddca;">Starts {{ $order->lease_start?->format('M d, Y') ?? 'TBD' }} · {{ $order->lease_months ?? 12 }} months</td>
                </tr>
            @endif
        </table>

        @if ($order->message)
            <div style="margin-top: 12px; padding: 16px; background: #f8f3eb; border-radius: 12px; border: 1px solid #e7ddca;">
                <p style="margin: 0; white-space: pre-line;">{{ $order->message }}</p>
            </div>
        @endif

        <p style="margin-top: 20px;">
            <a href="{{ route('agent.portal') }}" style="display: inline-block; background: #1d3c34; color: #ffffff; padding: 10px 20px; border-radius: 999px; text-decoration: none; font-weight: bold;">Open Agent Portal</a>
        </p>
    </div>
</body>
</html>
