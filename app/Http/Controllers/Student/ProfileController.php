<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\CertificateService;
use App\Support\Tiers;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return view('student.profile', [
            'user' => $user,
            'tier' => $user->tier(),
            'next' => $user->nextTier(),
            'tiers' => Tiers::all(),
            'certificates' => $user->certificates()->get()->keyBy('tier'),
            'certificateTypes' => collect(array_keys(config('kampusquest.certificates')))->map(fn ($k) => Tiers::certificate($k)),
            'validReports' => $user->validReportsCount(),
            'partyCount' => $user->confirmations()->count(),
            'pendingXp' => $user->pendingXp(),
            'ledger' => $user->xpTransactions()->latest()->latest('id')->take(20)->get(),
        ]);
    }

    public function claim(Request $request, string $tier, CertificateService $service)
    {
        try {
            $certificate = $service->claim($request->user(), $tier);
        } catch (InvalidArgumentException $e) {
            return redirect()->route('profile')->with('error', $e->getMessage());
        }

        return redirect()->route('profile')->with('status', "Sertifikat diterbitkan dengan nomor seri {$certificate->serial}.");
    }
}
