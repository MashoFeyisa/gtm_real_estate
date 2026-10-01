<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>የመኖሪያ ቤት ኪራይ ውል — {{ $property->title }}</title>
    <style>
        @page {
            margin: 30px 40px;
        }
        * {
            box-sizing: border-box;
        }
        @font-face {
            font-family: 'NotoSansEthiopic';
            font-weight: normal;
            src: url('{{ storage_path('fonts/NotoSansEthiopic-Regular.ttf') }}');
        }
        @font-face {
            font-family: 'NotoSansEthiopic';
            font-weight: bold;
            src: url('{{ storage_path('fonts/NotoSansEthiopic-Bold.ttf') }}');
        }
        body {
            font-family: 'NotoSansEthiopic', 'Helvetica', sans-serif;
            color: #22333b;
            font-size: 11px;
            line-height: 1.7;
        }
        .brand-bar {
            background: #1d3c34;
            color: #f9f3e9;
            padding: 14px 18px;
            border-radius: 10px;
        }
        .brand-bar table {
            width: 100%;
        }
        .brand-name {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .brand-tagline {
            font-size: 9px;
            color: #d9cab3;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .brand-contact {
            font-size: 9px;
            color: #e6ede6;
        }
        .campaign-banner {
            margin-top: 10px;
            background: #f1e4cf;
            border: 1px solid #d9cab3;
            border-radius: 8px;
            padding: 8px 14px;
            text-align: center;
            font-size: 9px;
            letter-spacing: 1.5px;
            color: #1d3c34;
            font-weight: bold;
        }
        h1 {
            font-size: 16px;
            color: #1d3c34;
            margin: 20px 0 4px;
            text-align: center;
        }
        .doc-ref {
            font-size: 9px;
            color: #6b7f76;
            margin-bottom: 14px;
            text-align: center;
        }
        .header-line {
            font-size: 11px;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1d3c34;
            margin: 16px 0 6px;
            border-bottom: 1px solid #d9cab3;
            padding-bottom: 3px;
        }
        .fill-line {
            color: #22333b;
        }
        .fill-line .blank {
            display: inline-block;
            border-bottom: 1px dotted #587165;
            min-width: 160px;
            text-align: center;
            color: #1d3c34;
            font-weight: bold;
        }
        .party-block {
            margin: 6px 0 10px;
        }
        .party-block p {
            margin: 3px 0;
        }
        .party-block .sub {
            margin-left: 18px;
        }
        .checkbox-line {
            margin: 4px 0 4px 10px;
        }
        .property-summary {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px;
        }
        .property-summary th, .property-summary td {
            border: 1px solid #d9cab3;
            padding: 6px 10px;
            text-align: left;
            font-size: 10px;
        }
        .property-summary th {
            background: #edf2ee;
            color: #1d3c34;
            width: 32%;
        }
        .clauses ol {
            margin: 4px 0 8px 0;
            padding-left: 22px;
        }
        .clauses li {
            margin: 3px 0;
        }
        .clauses p {
            margin: 5px 0;
            text-align: justify;
        }
        .signatures {
            margin-top: 26px;
            width: 100%;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            padding: 0 14px;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px solid #22333b;
            padding-top: 4px;
            font-size: 10px;
            color: #4a5d55;
        }
        .digital-signatures {
            margin-top: 18px;
            width: 100%;
            border-collapse: collapse;
        }
        .digital-signatures td {
            width: 50%;
            padding: 0 8px;
            vertical-align: top;
        }
        .ds-heading {
            font-size: 10px;
            color: #1d3c34;
            margin: 22px 0 8px;
            font-weight: bold;
        }
        .digital-sig {
            border: 1px dashed #587165;
            border-radius: 8px;
            padding: 8px 10px;
            background: #f2f7f3;
            font-size: 8px;
            line-height: 1.7;
            color: #4a5d55;
        }
        .digital-sig .ds-title {
            font-size: 9px;
            font-weight: bold;
            color: #1d3c34;
            margin-bottom: 3px;
        }
        .digital-sig .ds-verified {
            background: #1d3c34;
            color: #f2f7f3;
            border-radius: 4px;
            padding: 1px 6px;
            font-weight: bold;
        }
        .digital-sig .ds-code {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: #1d3c34;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 24px;
            border-top: 2px solid #1d3c34;
            padding-top: 8px;
            font-size: 8px;
            color: #6b7f76;
        }
        .signature-verification p {
            margin: 3px 0;
        }
        .signature-verification .blank {
            display: inline-block;
            border-bottom: 1px dotted #587165;
            min-width: 140px;
        }
    </style>
</head>
<body>
    @php
        $o = $overrides ?? [];
    @endphp
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

    <div class="campaign-banner">ኦፊሴላዊ የኪራይ ውል ሰነድ · {{ $company['name'] }} · {{ now()->format('Y') }}</div>

    <h1>የመኖሪያ ቤት ኪራይ ውል</h1>
    <div class="doc-ref">ማጣቀሻ፦ GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }} · ተዘጋጅቷል {{ now()->format('d/m/Y') }}</div>

    <div class="header-line">
        <p>የውል ቁጥር፦ <span class="blank">GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</span></p>
        <p>የውል ቀን፦ <span class="blank">{{ now()->format('d/m/Y') }}</span> ዓ.ም.</p>
        <p>የውሉ ቦታ፦ <span class="blank">{{ $company['address'] }}</span></p>
    </div>

    <div class="section-title">1. የውሉ ተዋዋይ ወገኖች</div>

    <div class="party-block">
        <p><strong>1.1 አከራይ</strong></p>
        <p class="sub">ሙሉ ስም፦ <span class="blank">{{ $o['seller_name'] ?? $seller['name'] }}</span></p>
        <p class="sub">ስልክ ቁጥር፦ <span class="blank">{{ $o['seller_phone'] ?? $seller['phone'] }}</span></p>
        <p class="sub">ኢሜይል፦ <span class="blank">{{ $o['seller_email'] ?? $seller['email'] }}</span></p>
        <p class="sub">አድራሻ፦ <span class="blank">{{ $o['seller_address'] ?? $seller['address'] }}</span></p>
        @if ($company['name'] !== ($o['seller_name'] ?? $seller['name']))
            <p class="sub">በ{{ $company['name'] }} የሪል እስቴት መድረክ የቀረበ።</p>
        @endif
        <p class="sub" style="font-size: 9px; color: #587165;">ከዚህ በኋላ “አከራይ” ተብሎ ይጠራል።</p>
    </div>

    <div class="party-block">
        <p><strong>1.2 ተከራይ</strong></p>
        <p class="sub">ሙሉ ስም፦ <span class="blank">{{ $o['buyer_name'] ?? $order->name }}</span></p>
        <p class="sub">ስልክ ቁጥር፦ <span class="blank">{{ $o['buyer_phone'] ?? $order->phone ?? '________________' }}</span></p>
        <p class="sub">ኢሜይል፦ <span class="blank">{{ $o['buyer_email'] ?? $order->email }}</span></p>
        <p class="sub" style="font-size: 9px; color: #587165;">ከዚህ በኋላ “ተከራይ” ተብሎ ይጠራል።</p>
    </div>

    <div class="section-title">2. የተከራዩ ንብረት መረጃ</div>
    <p>አከራዩ የሚከተለውን ንብረት ለተከራዩ በኪራይ ለመስጠት ተስማምቷል።</p>

    <table class="property-summary">
        <tr>
            <th>ንብረቱ</th>
            <td>{{ $o['property_title'] ?? $property->title }}</td>
        </tr>
        <tr>
            <th>ክልል/ከተማ</th>
            <td>{{ $o['property_city'] ?? $property->city ?? 'አይጠቀስም' }}</td>
        </tr>
        <tr>
            <th>የንብረቱ ሙሉ አድራሻ</th>
            <td>{{ $o['property_address'] ?? (($property->city ?? '').' '.($property->address ?? '')) }}</td>
        </tr>
        <tr>
            <th>የቤቱ አይነት</th>
            <td>{{ $o['property_category'] ?? ucfirst($property->property_category ?? 'ቤት') }}</td>
        </tr>
        <tr>
            <th>የመኝታ ክፍሎች ብዛት</th>
            <td>{{ $o['bedrooms'] ?? $property->bedrooms }}</td>
        </tr>
        <tr>
            <th>የመታጠቢያ ቤቶች ብዛት</th>
            <td>{{ $o['bathrooms'] ?? $property->bathrooms }}</td>
        </tr>
        <tr>
            <th>ስፋት</th>
            <td>{{ $o['area'] ?? number_format($property->area) }} ስኩዌር ሜትር</td>
        </tr>
    </table>

    <div class="section-title">3. የኪራይ ዓላማ</div>
    <p>ተከራዩ ንብረቱን ለመኖሪያ ዓላማ ብቻ ይጠቀማል። ተከራዩ የንብረቱን አጠቃቀም ዓላማ ያለአከራዩ የጽሁፍ ፈቃድ መቀየር አይችልም።</p>

    <div class="section-title">4. የኪራይ ዋጋ</div>
    <table class="property-summary">
        <tr>
            <th>የወር ኪራይ</th>
            <td><strong>{{ $o['offer_amount'] ?? number_format($order->offer_amount ?? $property->price, 2) }} ብር</strong></td>
        </tr>
        <tr>
            <th>የክፍያ ዘዴ</th>
            <td>በየወሩ በስምምነት መሠረት</td>
        </tr>
        <tr>
            <th>የክፍያ ቀን</th>
            <td>በየወሩ የመጀመሪያ ቀን</td>
        </tr>
        @if ($o['agent_note'] ?? $order->agent_note)
            <tr>
                <th>የስምምነት ማስታወሻ</th>
                <td>{{ $o['agent_note'] ?? $order->agent_note }}</td>
            </tr>
        @endif
    </table>

    <div class="section-title">6. የውሉ የኪራይ ጊዜ</div>
    <p>
        የኪራይ ጊዜ ከ፦ <span class="blank">{{ $order->lease_start?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span> ዓ.ም.
        እስከ፦ <span class="blank">{{ ($order->lease_start ?? now())->copy()->addMonths((int) ($order->lease_months ?? 12))->format('d/m/Y') }}</span> ዓ.ም.
        ድረስ ይሆናል።
    </p>
    <p>ጠቅላላ የኪራይ ጊዜ፦ <span class="blank">{{ $o['lease_months'] ?? $order->lease_months ?? 12 }} ወራት</span></p>
    <p>የውሉ ማደስ በሁለቱም ወገኖች የጽሁፍ ስምምነትና በሚመለከተው ሕግ መሠረት ይፈጸማል።</p>

    <div class="section-title">7. የአከራዩ ግዴታዎች</div>
    <div class="clauses">
        <ol>
            <li>ንብረቱን ለተከራዩ በተስማማበት ሁኔታ ያስረክባል።</li>
            <li>ተከራዩ ንብረቱን በሰላም እንዲጠቀም ተገቢውን ሁኔታ ያስጠብቃል።</li>
            <li>በአከራዩ ኃላፊነት ላይ የሚወድቁ አስፈላጊ ጥገናዎችን በሕግና በዚህ ውል መሠረት ያከናውናል።</li>
            <li>ተከራዩን ያለሕጋዊ ምክንያት ከንብረቱ አያስወጣም።</li>
            <li>በውሉ የተደነገጉትን ሌሎች ግዴታዎች ይፈጽማል።</li>
        </ol>
    </div>

    <div class="section-title">8. የተከራዩ ግዴታዎች</div>
    <div class="clauses">
        <ol>
            <li>የተስማማውን የኪራይ ገንዘብ በወቅቱ ይከፍላል።</li>
            <li>ንብረቱን በጥንቃቄ ይጠቀማል።</li>
            <li>በራሱ ጥፋት የተፈጠረን ጉዳት በሕግና በውሉ መሠረት ይጠግናል ወይም ወጪውን ይሸፍናል።</li>
            <li>ንብረቱን ያለአከራዩ ፈቃድ ለሌላ ሰው አያከራይም ወይም አያስተላልፍም፣ ሕጉ የሚፈቅደው ካልሆነ በስተቀር።</li>
            <li>ሕገወጥ ድርጊት ለማከናወን ንብረቱን አይጠቀምም።</li>
            <li>የጎረቤቶችን ሰላምና የንብረቱን ደህንነት ያከብራል።</li>
            <li>ንብረቱን ሲለቅ በተረከበበት ሁኔታ ወይም በተስማማበት ሁኔታ ይመልሳል።</li>
        </ol>
    </div>

    <div class="section-title">10. የጥገና ኃላፊነት</div>
    <p>
        በተከራዩ መደበኛ አጠቃቀም ምክንያት የሚከሰቱ መደበኛ የእርጅና ለውጦች እንደ ተከራዩ ጥፋት አይቆጠሩም።
        በተከራዩ ወይም በተከራዩ ኃላፊነት ስር ባሉ ሰዎች ጥፋት የተፈጠረ ጉዳት ግን በሕግ መሠረት ተገቢው ወጪ በተከራዩ ይሸፈናል።
    </p>

    <div class="section-title">13. የውል ማቋረጥ</div>
    <p>
        ይህ ውል በሕግ የተፈቀዱ ሁኔታዎች እና በተዋዋይ ወገኖች ስምምነት መሠረት ሊቋረጥ ይችላል።
        ውሉን ለማቋረጥ የሚያስፈልጉ ማስታወቂያዎች፣ የጊዜ ገደቦች እና ሌሎች መስፈርቶች በሚመለከተው የኢትዮጵያ ሕግ መሠረት ይፈጸማሉ።
    </p>

    <div class="section-title">15. ንብረቱን መመለስ</div>
    <div class="clauses">
        <ol>
            <li>ንብረቱን ያስረክባል።</li>
            <li>ሁሉንም ቁልፎች ይመልሳል።</li>
            <li>የራሱን ንብረቶች ከቤቱ ያስወግዳል።</li>
            <li>ያልተከፈሉ የኪራይ ወይም ሌሎች በእሱ ላይ የሚወድቁ ክፍያዎችን ይፈጽማል።</li>
            <li>ንብረቱን በርክክብ ሰነድ መሠረት ያስረክባል።</li>
        </ol>
    </div>

    <div class="section-title">16. አለመግባባት ሲፈጠር</div>
    <p>
        በዚህ ውል ላይ ከተዋዋይ ወገኖች መካከል አለመግባባት ከተፈጠረ በመጀመሪያ በውይይትና በስምምነት ለመፍታት ይሞከራል።
        በስምምነት መፍታት ካልተቻለ ጉዳዩ በሚመለከተው የኢትዮጵያ ሕግ መሠረት ለሚመለከተው የፍርድ ወይም ሌላ በሕግ የተፈቀደ አካል ሊቀርብ ይችላል።
    </p>

    <div class="section-title">17. የውሉ ተጨማሪ ስምምነቶች</div>
    <p>ተጨማሪ ልዩ ስምምነቶች፦</p>
    @if ($o['special_terms'] ?? $order->message)
        <div class="fill-line" style="margin-top: 4px; padding: 4px 8px; border-left: 2px solid #587165;"><span class="blank" style="display: block; text-align: left; min-width: 0; font-weight: normal;">{!! nl2br(e($o['special_terms'] ?? $order->message)) !!}</span></div>
    @else
        <p class="fill-line"><span class="blank">&nbsp;</span></p>
    @endif

    <div class="section-title">19. የወገኖች ማረጋገጫ</div>
    <p>
        እኛ ከዚህ በታች የተፈረምን አከራይና ተከራይ የዚህን ውል ይዘት አንብበንና ተረድተን በነፃ ፈቃዳችን ተስማምተን ፈርመናል።
        ይህ ውል በሕግ ከሚደነገጉ መስፈርቶች ጋር ተጣጥሞ እንዲፈጸም ሁለቱም ወገኖች ተስማምተዋል።
    </p>

    <div class="section-title">20 &amp; 21. የወገኖች ፊርማ</div>
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">
                    <strong>20. የአከራይ ፊርማ</strong><br>
                    {{ $o['seller_name'] ?? $seller['name'] }} — {{ now()->format('d/m/Y') }}
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>21. የተከራይ ፊርማ</strong><br>
                    {{ $o['buyer_name'] ?? $order->name }} — {{ now()->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="ds-heading">የሰነዱ ዲጂታል ማረጋገጫ</div>
    <table class="digital-signatures">
        <tr>
            <td>
                <div class="digital-sig">
                    <div class="ds-title">{{ $o['seller_name'] ?? $seller['name'] }} — አከራይ</div>
                    <div><span class="ds-verified">ተፈርሟል</span> · {{ $signature['method'] }}</div>
                    <div>የፊርማ ሰው፦ {{ $o['seller_name'] ?? $seller['name'] }}{{ ($o['seller_email'] ?? $seller['email']) ? ' ('.($o['seller_email'] ?? $seller['email']).')' : '' }}</div>
                    <div>የተፈረመበት ጊዜ፦ {{ $signature['signed_at']->format('d/m/Y · H:i') }}</div>
                    <div>የማረጋገጫ ኮድ፦ <span class="ds-code">{{ $signature['verification'] }}</span></div>
                    <div>የሰነድ ማጣቀሻ፦ GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
            <td>
                <div class="digital-sig">
                    <div class="ds-title">ተከራይ — {{ $o['buyer_name'] ?? $order->name }}</div>
                    <div><span class="ds-verified">ተፈርሟል</span> · {{ $signature['method'] }}</div>
                    <div>የፊርማ ሰው፦ {{ $o['buyer_name'] ?? $order->name }} ({{ $o['buyer_email'] ?? $order->email }})</div>
                    <div>የተፈረመበት ጊዜ፦ {{ $signature['signed_at']->format('d/m/Y · H:i') }}</div>
                    <div>የማረጋገጫ ኮድ፦ <span class="ds-code">{{ $signature['verification'] }}</span></div>
                    <div>የሰነድ ማጣቀሻ፦ GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        {{ $company['name'] }} · {{ $company['address'] }} · {{ $company['phone'] }} · {{ $company['email'] }}<br>
        ይህ ሰነድ በተዋዋይ ወገኖች ስምምነት መሠረት በራስ-ሰር ተዘጋጅቷል። ትክክለኛነቱን በማረጋገጫ ኮዱና በሰነድ ማጣቀሻው መሠረት በ{{ $company['email'] }} ማረጋገጥ ይቻላል።
    </div>
</body>
</html>
