<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\User;
use App\Services\QrCode;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function verify(Request $request)
    {
        if ($serial = trim((string) $request->query('seri'))) {
            return redirect()->route('verify.show', strtoupper($serial));
        }

        return view('verify.show', ['serial' => null, 'certificate' => null]);
    }

    public function verifySerial(string $serial)
    {
        $serial = strtoupper(trim($serial));
        $certificate = Certificate::with('user')->where('serial', $serial)->first();

        return view('verify.show', ['serial' => $serial, 'certificate' => $certificate]);
    }

    public function pdf(Request $request, Certificate $certificate)
    {
        $user = $request->user();
        abort_unless($certificate->user_id === $user->id || $user->hasRole(User::ADMIN), 403);

        $certificate->load('user');
        $html = view('certificate.pdf', [
            'certificate' => $certificate,
            'info' => $certificate->info(),
            'qr' => QrCode::dataUri($certificate->verifyUrl(), 240),
        ])->render();

        $options = new Options;
        $options->setIsRemoteEnabled(false);
        $options->setChroot([resource_path('fonts'), public_path()]);
        $options->setFontDir(storage_path('fonts'));
        $options->setFontCache(storage_path('fonts'));
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$certificate->serial.'.pdf"',
        ]);
    }
}
