<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $order->isRental() ? 'Rental' : 'Purchase' }} Agreement — {{ $property->title }}</title>
    <style>
        @page {
            margin: 36px 42px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #22333b;
            font-size: 12px;
            line-height: 1.6;
        }
        .brand-bar {
            background: #1d3c34;
            color: #f9f3e9;
            padding: 18px 22px;
            border-radius: 10px;
        }
        .brand-bar table {
            width: 100%;
        }
        .brand-name {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .brand-tagline {
            font-size: 10px;
            color: #d9cab3;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .brand-contact {
            font-size: 10px;
            color: #e6ede6;
        }
        .campaign-banner {
            margin-top: 12px;
            background: #f1e4cf;
            border: 1px solid #d9cab3;
            border-radius: 8px;
            padding: 10px 16px;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: #1d3c34;
            font-weight: bold;
        }
        h1 {
            font-size: 18px;
            color: #1d3c34;
            margin: 24px 0 4px;
        }
        .doc-ref {
            font-size: 10px;
            color: #6b7f76;
            margin-bottom: 16px;
        }
        .party-box {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 18px;
        }
        .party-box td {
            vertical-align: top;
            width: 50%;
            padding: 0 8px;
        }
        .party-card {
            border: 1px solid #d9cab3;
            border-radius: 8px;
            padding: 12px 14px;
            background: #fbf7ef;
        }
        .party-card .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #587165;
            font-weight: bold;
        }
        .party-card .value {
            font-size: 12px;
            color: #1d3c34;
            margin-top: 4px;
        }
        table.terms {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 14px;
        }
        table.terms th, table.terms td {
            border: 1px solid #d9cab3;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
        }
        table.terms th {
            background: #edf2ee;
            color: #1d3c34;
            width: 34%;
        }
        .clauses p {
            margin: 8px 0;
            text-align: justify;
        }
        .clauses h2 {
            font-size: 12px;
            color: #1d3c34;
            margin: 16px 0 4px;
        }
        .signatures {
            margin-top: 34px;
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            padding: 0 20px;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px solid #22333b;
            padding-top: 4px;
            font-size: 10px;
            color: #4a5d55;
        }
        .digital-signatures {
            margin-top: 22px;
            width: 100%;
            border-collapse: collapse;
        }
        .digital-signatures td {
            width: 50%;
            padding: 0 8px;
            vertical-align: top;
        }
        .ds-heading {
            font-size: 11px;
            color: #1d3c34;
            margin: 26px 0 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .digital-sig {
            border: 1px dashed #587165;
            border-radius: 8px;
            padding: 10px 12px;
            background: #f2f7f3;
            font-size: 9px;
            line-height: 1.7;
            color: #4a5d55;
        }
        .digital-sig .ds-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #1d3c34;
            margin-bottom: 4px;
        }
        .digital-sig .ds-verified {
            display: inline-block;
            background: #1d3c34;
            color: #f2f7f3;
            border-radius: 4px;
            padding: 1px 7px;
            font-weight: bold;
            letter-spacing: 1px;
            font-size: 8px;
        }
        .digital-sig .ds-code {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: #1d3c34;
            letter-spacing: 1px;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 28px;
            border-top: 2px solid #1d3c34;
            padding-top: 8px;
            font-size: 9px;
            color: #6b7f76;
        }
    </style>
</head>
<body>
    <div class="brand-bar">
        <table>
            <tr>
                <td style="width: 64px;">
                    @if (file_exists($company['logo']))
                        <img src="{{ $company['logo'] }}" alt="Logo" style="width: 54px; height: 54px; border-radius: 10px;">
                    @endif
                </td>
                <td>
                    <div class="brand-name">{{ $company['name'] }}</div>
                    <div class="brand-tagline">{{ $company['tagline'] }}</div>
                    <div class="brand-contact">{{ $company['address'] }} · {{ $company['phone'] }} · {{ $company['email'] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="campaign-banner">Official {{ strtoupper($order->isRental() ? 'Rental' : 'Purchase') }} Agreement · {{ $company['name'] }} Property Campaign {{ now()->format('Y') }}</div>

    <h1>{{ $order->isRental() ? 'Residential Rental Agreement' : 'Property Purchase Agreement' }}</h1>
    <div class="doc-ref">Reference: GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }} · Issued {{ now()->format('F j, Y') }}</div>

    <table class="party-box">
        <tr>
            <td>
                <div class="party-card">
                    <div class="label">The Landlord / Seller</div>
                    <div class="value">{{ $seller['name'] }}</div>
                    <div class="value" style="font-size: 10px; color: #587165;">{{ $seller['email'] }}{{ $seller['phone'] ? ' · '.$seller['phone'] : '' }}</div>
                </div>
            </td>
            <td>
                <div class="party-card">
                    <div class="label">The {{ $order->isRental() ? 'Tenant' : 'Buyer' }}</div>
                    <div class="value">{{ $order->name }}</div>
                    <div class="value" style="font-size: 10px; color: #587165;">{{ $order->email }}{{ $order->phone ? ' · '.$order->phone : '' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="terms">
        <tr>
            <th>Property</th>
            <td>{{ $property->title }} — {{ $property->city ?? '' }} {{ $property->address ?? '' }}</td>
        </tr>
        <tr>
            <th>Agreement Type</th>
            <td>{{ $order->isRental() ? 'Long-term rental' : 'Outright purchase' }}</td>
        </tr>
        <tr>
            <th>{{ $order->isRental() ? 'Monthly Rent' : 'Agreed Price' }}</th>
            <td>
                ETB {{ number_format($order->offer_amount ?? $property->price, 2) }}{{ $order->isRental() ? ' per month' : '' }}
            </td>
        </tr>
        @if ($order->isRental())
            <tr>
                <th>Lease Start</th>
                <td>{{ $order->lease_start?->format('F j, Y') ?? 'To be confirmed with the agent' }}</td>
            </tr>
            <tr>
                <th>Lease Duration</th>
                <td>{{ $order->lease_months ?? 12 }} months, renewable by mutual written consent</td>
            </tr>
        @endif
        <tr>
            <th>Status</th>
            <td>Agreed by both parties on {{ now()->format('F j, Y') }}</td>
        </tr>
    </table>

    <div class="clauses">
        <h2>1. Subject of the Agreement</h2>
        <p>
            The {{ $company['name'] }} ("the {{ $order->isRental() ? 'Landlord' : 'Seller' }}") agrees to
            {{ $order->isRental() ? 'lease' : 'sell and transfer' }} the property described above to
            {{ $order->name }} ("the {{ $order->isRental() ? 'Tenant' : 'Buyer' }}") under the terms set out in this document.
        </p>

        <h2>2. Payment Terms</h2>
        <p>
            The {{ $order->isRental() ? 'Tenant shall pay the monthly rent in advance on or before the first day of each rental period' : 'Buyer shall complete payment according to the schedule agreed with the agent' }}.
            @if ($order->message)
                Special arrangements noted during the request: "{{ $order->message }}"
            @endif
        </p>

        <h2>3. {{ $order->isRental() ? 'Use and Care of the Property' : 'Transfer of Ownership' }}</h2>
        <p>
            @if ($order->isRental())
                The Tenant shall use the property solely for residential purposes, keep it in good condition, and return it at the end of the lease in the state it was received, allowing for fair wear and tear.
            @else
                Ownership transfers to the Buyer upon full settlement of the agreed price and completion of all registration formalities facilitated by the agent.
            @endif
        </p>

        <h2>4. General Provisions</h2>
        <p>
            This agreement reflects the full understanding between the parties. Any amendment must be made in writing and signed by both parties. The agreement is governed by the laws of Ethiopia.
        </p>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">
                    {{ $seller['name'] }}<br>
                    Authorized Signature — {{ now()->format('M d, Y') }}
                </div>
            </td>
            <td>
                <div class="sig-line">
                    {{ $order->isRental() ? 'Tenant' : 'Buyer' }}<br>
                    {{ $order->name }} — {{ now()->format('M d, Y') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="ds-heading">Digital Signature Verification</div>
    <table class="digital-signatures">
        <tr>
            <td>
                <div class="digital-sig">
                    <div class="ds-title">{{ $seller['name'] }} — {{ $order->isRental() ? 'Landlord' : 'Seller' }}</div>
                    <div><span class="ds-verified">SIGNED</span> · {{ $signature['method'] }}</div>
                    <div>Signatory: {{ $seller['name'] }}{{ $seller['email'] ? ' ('.$seller['email'].')' : '' }}</div>
                    <div>Signed at: {{ $signature['signed_at']->format('F j, Y · H:i') }} EAT</div>
                    <div>Verification code: <span class="ds-code">{{ $signature['verification'] }}</span></div>
                    <div>Document ref: GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
            <td>
                <div class="digital-sig">
                    <div class="ds-title">{{ $order->isRental() ? 'Tenant' : 'Buyer' }} — {{ $order->name }}</div>
                    <div><span class="ds-verified">SIGNED</span> · {{ $signature['method'] }}</div>
                    <div>Signatory: {{ $order->name }} ({{ $order->email }})</div>
                    <div>Signed at: {{ $signature['signed_at']->format('F j, Y · H:i') }} EAT</div>
                    <div>Verification code: <span class="ds-code">{{ $signature['verification'] }}</span></div>
                    <div>Document ref: GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        {{ $company['name'] }} · {{ $company['address'] }} · {{ $company['phone'] }} · {{ $company['email'] }}<br>
        This document was generated automatically upon agreement between the parties and is valid with the handwritten and digital signatures above. Authenticity can be confirmed with {{ $company['email'] }} using the verification code and document reference.
    </div>
</body>
</html>
