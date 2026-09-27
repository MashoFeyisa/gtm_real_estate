<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your {{ $order->isRental() ? 'rental' : 'buy' }} request was {{ $order->status === 'accepted' ? 'accepted' : 'rejected' }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f1e8; color: #1d3c34; margin: 0; padding: 24px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #d9cab3;">
        <h2 style="margin-top: 0; color: #1d3c34;">
            @if ($order->status === 'accepted')
                🎉 Your {{ $order->isRental() ? 'rental' : 'buy' }} request was accepted!
            @else
                Update on your {{ $order->isRental() ? 'rental' : 'buy' }} request
            @endif
        </h2>

        <p>Hello {{ $order->name }},</p>

        @if ($order->status === 'accepted')
            <p>Good news — the agent accepted your request for <strong>{{ $order->property?->title ?? 'the property' }}</strong>. The official agreement document has been generated and is ready for you.</p>
            <p>Please contact us at <strong>{{ $siteBrand['email'] }}</strong> or <strong>{{ $siteBrand['phone'] }}</strong> to receive and sign your agreement.</p>
        @else
            <p>Thank you for your interest in <strong>{{ $order->property?->title ?? 'the property' }}</strong>. Unfortunately, the agent could not accept this request at the moment. Our team may suggest similar properties — feel free to browse more listings.</p>
        @endif

        @if ($order->agent_note)
            <div style="margin-top: 16px; padding: 16px; background: #f8f3eb; border-radius: 12px; border: 1px solid #e7ddca;">
                <p style="margin: 0;"><strong>Note from the agent:</strong> {{ $order->agent_note }}</p>
            </div>
        @endif

        <p style="margin-top: 20px;">
            <a href="{{ route('properties.index') }}" style="display: inline-block; background: #1d3c34; color: #ffffff; padding: 10px 20px; border-radius: 999px; text-decoration: none; font-weight: bold;">Browse Properties</a>
        </p>

        <p style="margin-top: 16px; font-size: 12px; color: #6b7f76;">{{ $siteBrand['name'] }} · {{ $siteBrand['address'] }}</p>
    </div>
</body>
</html>
