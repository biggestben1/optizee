<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class LetterheadPdfController extends Controller
{
    private const LETTERHEAD_FILENAME = 'Optizee letterhead_pages-to-jpg-0001.jpg';

    /**
     * PDF: domain unsuspension letter on Optizee letterhead.
     * Place the JPG in public/: Optizee letterhead_pages-to-jpg-0001.jpg
     */
    public function domainUnsuspension(Request $request)
    {
        $letterheadDataUri = $this->letterheadDataUri();

        $letterDate = $request->input('date')
            ? Carbon::parse($request->input('date'))->format('F j, Y')
            : Carbon::now()->format('F j, Y');

        $signatoryName = $request->input('name', '[Your Full Name]');
        $signatoryTitle = $request->input('title', '[Your Position – e.g., IT Administrator / Manager]');
        $signatoryEmail = $request->input('email', '[Your Email Address]');
        $signatoryPhone = $request->input('phone', '');

        $html = view('pdf.domain-unsuspension-letter', compact(
            'letterheadDataUri',
            'letterDate',
            'signatoryName',
            'signatoryTitle',
            'signatoryEmail',
            'signatoryPhone'
        ))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setChroot(realpath(public_path()) ?: public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Optizee_Domain_Unsuspension_Letter.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * Dompdf renders embedded images reliably; CSS background-image on body often does not paint.
     */
    private function letterheadDataUri(): ?string
    {
        $candidates = array_unique(array_values(array_filter(array_merge(
            [public_path(self::LETTERHEAD_FILENAME)],
            glob(public_path('*letterhead*.jpg')) ?: [],
            glob(public_path('*letterhead*.jpeg')) ?: [],
            glob(public_path('*Letterhead*.jpg')) ?: [],
            glob(public_path('*optizee*letterhead*.jpg')) ?: [],
        ))));

        foreach ($candidates as $path) {
            if (! is_readable($path)) {
                continue;
            }
            $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            $data = base64_encode(file_get_contents($path));

            return 'data:'.$mime.';base64,'.$data;
        }

        return null;
    }
}
