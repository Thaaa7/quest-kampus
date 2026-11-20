<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\User;
use App\Support\Tiers;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CertificateService
{
    public function claim(User $user, string $key): Certificate
    {
        $info = Tiers::certificate($key);
        if (! $info) {
            throw new InvalidArgumentException('Jenis sertifikat tidak dikenal.');
        }
        if ($user->xp < $info['xp']) {
            throw new InvalidArgumentException("{$info['name']} terbuka di ".number_format($info['xp'], 0, ',', '.').' XP.');
        }

        return DB::transaction(function () use ($user, $key, $info) {
            $existing = $user->certificates()->where('tier', $key)->first();
            if ($existing) {
                return $existing;
            }

            $certificate = Certificate::create([
                'user_id' => $user->id,
                'tier' => $key,
                'serial' => 'TMP-'.uniqid(),
                'xp_at_issue' => $user->xp,
                'valid_reports' => $user->validReportsCount(),
                'issued_at' => now(),
            ]);
            $certificate->serial = sprintf('KQ-%s-%s-%06d', $info['code'], now()->format('Y'), $certificate->id);
            $certificate->save();

            return $certificate;
        });
    }
}
