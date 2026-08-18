<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 20mm; }
        html, body {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #111;
        }
        /* Dompdf paints positioned <img> reliably; CSS background-image on body often does not */
        .letter-wrap {
            position: relative;
            min-height: 100%;
        }
        .letterhead-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: auto;
            z-index: 0;
        }
        .letter-body {
            position: relative;
            z-index: 1;
            padding-top: 58mm;
        }
        .meta { margin-bottom: 14px; }
        .meta strong { display: inline-block; min-width: 72px; }
        h1.subject {
            font-size: 12pt;
            margin: 14px 0 12px;
            text-align: center;
            font-weight: bold;
        }
        p { margin: 0 0 8px; }
        ul { margin: 8px 0 12px 18px; padding: 0; }
        li { margin-bottom: 4px; }
        .closing { margin-top: 18px; }
        .signature { margin-top: 22px; }
    </style>
</head>
<body>
    <div class="letter-wrap">
        @if(!empty($letterheadDataUri))
            <img class="letterhead-bg" src="{{ $letterheadDataUri }}" alt="">
        @endif

        <div class="letter-body">
            <div class="meta">
                <p><strong>DATE:</strong> {{ $letterDate }}</p>
                <p><strong>TO:</strong> Netcraft / Radix Abuse &amp; Security Team</p>
            </div>

            <h1 class="subject">SUBJECT: Request for Domain Unsuspension – www.optizeehotel.store</h1>

            <p>Dear Sir/Madam,</p>

            <p>I am writing on behalf of Optizee Hotel, the rightful owner and operator of the domain <strong>www.optizeehotel.store</strong>, which is used to support our Point of Sale (POS) service operations.</p>

            <p>We acknowledge that our domain was flagged due to a phishing-related issue identified at the URL path <strong>/login</strong>. We take this matter very seriously and have conducted a full investigation and cleanup of the website.</p>

            <p>The following corrective actions have been completed:</p>
            <ul>
                <li>Immediate removal of the phishing page and all related malicious content</li>
                <li>Comprehensive scan of the hosting environment to identify and delete any unauthorized or suspicious files</li>
                <li>Reset and strengthening of all access credentials, including hosting (cPanel), FTP, and database access</li>
                <li>Review and securing of the application environment, ensuring sensitive files (such as configuration files) are not publicly accessible</li>
                <li>Verification that the affected URL now returns a safe response and no longer contains any harmful content</li>
                <li>Implementation of additional security measures, including HTTPS enforcement and server-level protections</li>
            </ul>

            <p>We confirm that the website is now fully secured and is being used strictly for legitimate business purposes related to POS services.</p>

            <p>We respectfully request that you review our case and proceed with lifting the suspension on our domain.</p>

            <p>We sincerely appreciate your time and assistance.</p>

            <div class="closing">
                <p>Yours faithfully,</p>
            </div>

            <div class="signature">
                <p><strong>{{ $signatoryName }}</strong></p>
                <p><strong>{{ $signatoryTitle }}</strong></p>
                <p><strong>Optizee Hotel</strong></p>
                <p><strong>Email:</strong> {{ $signatoryEmail }}</p>
                @if(!empty($signatoryPhone))
                    <p><strong>Phone:</strong> {{ $signatoryPhone }}</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
