<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>የንብረት ግዢና ሽያጭ ውል — {{ $property->title }}</title>
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
        .header-line p {
            margin: 3px 0;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1d3c34;
            margin: 16px 0 6px;
            border-bottom: 1px solid #d9cab3;
            padding-bottom: 3px;
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
        .signature-verification {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }
        .signature-verification td {
            width: 50%;
            padding: 0 10px;
            vertical-align: top;
        }
        .signature-verification p {
            margin: 3px 0;
        }
        .signature-verification .blank {
            display: inline-block;
            border-bottom: 1px dotted #587165;
            min-width: 120px;
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

    <div class="campaign-banner">ኦፊሴላዊ የግዢና ሽያጭ ውል ሰነድ · {{ $company['name'] }} · {{ now()->format('Y') }}</div>

    <h1>የንብረት ግዢና ሽያጭ ውል</h1>
    <div class="doc-ref">ማጣቀሻ፦ GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }} · ተዘጋጅቷል {{ now()->format('d/m/Y') }}</div>

    <div class="header-line">
        <p>የውል ቁጥር፦ <span class="blank">GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</span></p>
        <p>የውል ቀን፦ <span class="blank">{{ now()->format('d/m/Y') }}</span> ዓ.ም.</p>
        <p>የውሉ ቦታ፦ <span class="blank">{{ $company['address'] }}</span></p>
    </div>

    <div class="section-title">1. የውሉ ተዋዋይ ወገኖች</div>

    <div class="party-block">
        <p><strong>1.1 ሻጭ</strong></p>
        <p class="sub">ሙሉ ስም፦ <span class="blank">{{ $o['seller_name'] ?? $seller['name'] }}</span></p>
        <p class="sub">ስልክ ቁጥር፦ <span class="blank">{{ $o['seller_phone'] ?? $seller['phone'] }}</span></p>
        <p class="sub">ኢሜይል፦ <span class="blank">{{ $o['seller_email'] ?? $seller['email'] }}</span></p>
        <p class="sub">አድራሻ፦ <span class="blank">{{ $o['seller_address'] ?? $seller['address'] }}</span></p>
        @if ($company['name'] !== ($o['seller_name'] ?? $seller['name']))
            <p class="sub">በ{{ $company['name'] }} የሪል እስቴት መድረክ የቀረበ።</p>
        @endif
        <p class="sub" style="font-size: 9px; color: #587165;">ከዚህ በኋላ “ሻጭ” ተብሎ ይጠራል።</p>
    </div>

    <div class="party-block">
        <p><strong>1.2 ገዢ</strong></p>
        <p class="sub">ሙሉ ስም፦ <span class="blank">{{ $o['buyer_name'] ?? $order->name }}</span></p>
        <p class="sub">ስልክ ቁጥር፦ <span class="blank">{{ $o['buyer_phone'] ?? $order->phone ?? '________________' }}</span></p>
        <p class="sub">ኢሜይል፦ <span class="blank">{{ $o['buyer_email'] ?? $order->email }}</span></p>
        <p class="sub" style="font-size: 9px; color: #587165;">ከዚህ በኋላ “ገዢ” ተብሎ ይጠራል።</p>
    </div>

    <div class="section-title">2. የሚሸጠው ንብረት መረጃ</div>
    <p>ሻጩ በሕጋዊ መብት የያዘውን የሚከተለውን ንብረት ለገዢው ለመሸጥ ተስማምቷል።</p>

    <table class="property-summary">
        <tr>
            <th>የንብረቱ አይነት</th>
            <td>{{ $o['property_category'] ?? ucfirst($property->property_category ?? 'መኖሪያ ቤት') }}</td>
        </tr>
        <tr>
            <th>ክልል/ከተማ</th>
            <td>{{ $o['property_city'] ?? $property->city ?? 'አይጠቀስም' }}</td>
        </tr>
        <tr>
            <th>የንብረቱ ስፋት</th>
            <td>{{ $o['area'] ?? number_format($property->area) }} ካሬ ሜትር</td>
        </tr>
        <tr>
            <th>የንብረቱ ሙሉ አድራሻ</th>
            <td>{{ $o['property_address'] ?? (($property->city ?? '').' '.($property->address ?? '')) }}</td>
        </tr>
    </table>

    <div class="section-title">3. የንብረቱ ዝርዝር መግለጫ</div>
    <table class="property-summary">
        <tr>
            <th>የመኝታ ክፍሎች</th>
            <td>{{ $o['bedrooms'] ?? $property->bedrooms }}</td>
        </tr>
        <tr>
            <th>የመታጠቢያ ቤቶች</th>
            <td>{{ $o['bathrooms'] ?? $property->bathrooms }}</td>
        </tr>
        @if ($property->description)
            <tr>
                <th>ተጨማሪ መግለጫ</th>
                <td>{{ \Illuminate\Support\Str::limit(strip_tags($property->description), 300) }}</td>
            </tr>
        @endif
    </table>

    <div class="section-title">4. የሽያጭ ዋጋ</div>
    <table class="property-summary">
        <tr>
            <th>ጠቅላላ የሽያጭ ዋጋ</th>
            <td><strong>{{ $o['offer_amount'] ?? number_format($order->offer_amount ?? $property->price, 2) }} ብር</strong></td>
        </tr>
    </table>
    <p>ይህ ዋጋ በሁለቱም ወገኖች በነፃ ፈቃድ የተስማሙበት የሽያጭ ዋጋ ነው።</p>

    <div class="section-title">5. የክፍያ ሁኔታ</div>
    <table class="property-summary">
        <tr>
            <th>የመጀመሪያ ክፍያ</th>
            <td>በስምምነት መሠረት</td>
        </tr>
        <tr>
            <th>የመጨረሻ ክፍያ</th>
            <td>ባለቤትነት ማስተላለፊያ ሂደት ከመጀመሩ በፊት</td>
        </tr>
        <tr>
            <th>ጠቅላላ</th>
            <td><strong>{{ $o['offer_amount'] ?? number_format($order->offer_amount ?? $property->price, 2) }} ብር</strong></td>
        </tr>
        @if ($o['agent_note'] ?? $order->agent_note)
            <tr>
                <th>የክፍያ ስምምነት ማስታወሻ</th>
                <td>{{ $o['agent_note'] ?? $order->agent_note }}</td>
            </tr>
        @endif
    </table>

    <div class="section-title">6. የክፍያ ዘዴ</div>
    <p>ክፍያው በሕጋዊ የክፍያ ዘዴ (በባንክ ወይም በተስማማ መንገድ) ይፈጸማል። የክፍያ ማስረጃ ከዚህ ውል ጋር እንደ አባሪ ሊያያዝ ይችላል።</p>

    <div class="section-title">7. የሻጩ ዋስትናና ማረጋገጫ</div>
    <div class="clauses">
        <ol>
            <li>የተሸጠው ንብረት በሕግ የተፈቀደ የባለቤትነት/የይዞታ መብት እንዳለው።</li>
            <li>ንብረቱን ለመሸጥ ሕጋዊ መብት እንዳለው።</li>
            <li>በንብረቱ ላይ ያለ የብድር፣ የዋስትና፣ የክርክር ወይም ሌላ ገደብ ካለ ለገዢው በግልጽ እንደሚገልጽ።</li>
            <li>ለገዢው የተሳሳተ ወይም የተደበቀ የባለቤትነት መረጃ እንደማይሰጥ።</li>
            <li>በሕግ የሚፈለጉ የሽያጭ ሰነዶችን ለማቅረብ እንደሚተባበር።</li>
        </ol>
    </div>

    <div class="section-title">8. የገዢው ማረጋገጫ</div>
    <div class="clauses">
        <ol>
            <li>የንብረቱን ሁኔታ እንደመረመረ ወይም ለመመርመር በቂ ዕድል እንዳገኘ።</li>
            <li>የንብረቱን አድራሻና መግለጫ እንደተረዳ።</li>
            <li>የሽያጭ ዋጋውን እንደተስማማ።</li>
            <li>በውሉ መሠረት ክፍያውን እንደሚፈጽም።</li>
            <li>በሕግ የሚፈለጉ የግዢ ሰነዶችን ለማቅረብ እንደሚተባበር።</li>
        </ol>
    </div>

    <div class="section-title">9. የንብረት ርክክብ</div>
    <p>
        ንብረቱ ለገዢው የሚረከበበት ቀን፦ <span class="blank">በስምምነት መሠረት</span> ዓ.ም.
        ርክክቡ የሚፈጸመው በተስማማው የክፍያና የሕጋዊ ሰነድ ሂደት መሠረት ይሆናል።
    </p>
    <p>በርክክብ ጊዜ የሚሰጡ ነገሮች፦ የቤት ቁልፎች፣ የንብረት ሰነዶች፣ የሜትር መረጃ እና ሌሎች በስምምነት የተወሰኑ ነገሮች።</p>

    <div class="section-title">10. የግብርና ሌሎች ወጪዎች</div>
    <p>ከዚህ ግዢና ሽያጭ ጋር የተያያዙ ግብር፣ የምዝገባ ክፍያ፣ የሰነድ ማረጋገጫ ክፍያ እና ሌሎች ሕጋዊ ወጪዎች በሚመለከተው ሕግና በተዋዋይ ወገኖች ስምምነት መሠረት ይከፈላሉ።</p>

    <div class="section-title">11. የንብረት ሰነዶች</div>
    <p>
        ሻጩ የባለቤትነት/የይዞታ ሰነድ፣ የንብረት ካርታ፣ የግብር ማስረጃ፣ የማንነት ሰነድ እና ሌሎች አስፈላጊ ሰነዶችን ለገዢው/ለሚመለከተው ባለሥልጣን ለማቅረብ ይተባበራል።
    </p>

    <div class="section-title">12. የንብረት ማስረከብና ባለቤትነት ማስተላለፍ</div>
    <p>
        የንብረቱ ሕጋዊ ባለቤትነት ማስተላለፍ በሚመለከተው የኢትዮጵያ ሕግ እና በሚመለከተው የመንግሥት ባለሥልጣን የሚፈለገው ምዝገባና ሂደት ከተፈጸመ በኋላ ይከናወናል።
    </p>

    <div class="section-title">13. የውል መሰረዝ</div>
    <p>
        በዚህ ውል የተደነገገው ክፍያ፣ ሰነድ ወይም ሌላ ግዴታ በተስማማው ጊዜ ካልተፈጸመ የተጎዳው ወገን በሕግ የተፈቀዱ መብቶችን ሊጠቀም ይችላል።
        ማንኛውም የውል መሰረዝ ወይም ማቋረጥ በሚመለከተው ሕግ መሠረት ይፈጸማል።
    </p>

    <div class="section-title">14. አለመግባባት</div>
    <p>
        በዚህ ውል ላይ አለመግባባት ከተፈጠረ ተዋዋይ ወገኖች በመጀመሪያ በውይይትና በስምምነት ለመፍታት ይሞክራሉ።
        በስምምነት መፍታት ካልተቻለ ጉዳዩ በሚመለከተው የኢትዮጵያ ሕግ መሠረት ለሚመለከተው ፍርድ ቤት ወይም ሌላ በሕግ የተፈቀደ አካል ሊቀርብ ይችላል።
    </p>

    <div class="section-title">15. ተጨማሪ ስምምነት</div>
    @if ($o['special_terms'] ?? $order->message)
        <div class="fill-line" style="margin-top: 4px; padding: 4px 8px; border-left: 2px solid #587165;"><span class="blank" style="display: block; text-align: left; min-width: 0; font-weight: normal;">{!! nl2br(e($o['special_terms'] ?? $order->message)) !!}</span></div>
    @else
        <p class="fill-line"><span class="blank">&nbsp;</span></p>
    @endif

    <div class="section-title">16. የወገኖች ማረጋገጫ</div>
    <p>እኛ ከዚህ በታች የተፈረምን ሻጭና ገዢ የዚህን ውል ይዘት አንብበንና ተረድተን፣ በነፃ ፈቃዳችን ተስማምተን ፈርመናል።</p>

    <div class="section-title">17 &amp; 18. የሻጭና የገዢ ፊርማ</div>
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">
                    <strong>17. የሻጭ ፊርማ</strong><br>
                    {{ $o['seller_name'] ?? $seller['name'] }} — {{ now()->format('d/m/Y') }}
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>18. የገዢ ፊርማ</strong><br>
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
                    <div class="ds-title">{{ $o['seller_name'] ?? $seller['name'] }} — ሻጭ</div>
                    <div><span class="ds-verified">ተፈርሟል</span> · {{ $signature['method'] }}</div>
                    <div>የፊርማ ሰው፦ {{ $o['seller_name'] ?? $seller['name'] }}{{ ($o['seller_email'] ?? $seller['email']) ? ' ('.($o['seller_email'] ?? $seller['email']).')' : '' }}</div>
                    <div>የተፈረመበት ጊዜ፦ {{ $signature['signed_at']->format('d/m/Y · H:i') }}</div>
                    <div>የማረጋገጫ ኮድ፦ <span class="ds-code">{{ $signature['verification'] }}</span></div>
                    <div>የሰነድ ማጣቀሻ፦ GTP-AGR-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
            <td>
                <div class="digital-sig">
                    <div class="ds-title">ገዢ — {{ $o['buyer_name'] ?? $order->name }}</div>
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
