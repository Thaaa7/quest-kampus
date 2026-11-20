<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Tiers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $required = Tiers::leaderboardXp();

        if ($user->xp < $required) {
            return view('student.leaderboard-locked', ['user' => $user, 'required' => $required]);
        }

        $period = $request->query('periode') === 'semua' ? 'semua' : 'bulan';

        $students = User::query()
            ->where('role', User::MAHASISWA)
            ->where('faculty', $user->faculty)
            ->when($period === 'bulan', function ($q) {
                $q->addSelect(['users.*', 'score' => DB::table('xp_transactions')
                    ->selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('xp_transactions.user_id', 'users.id')
                    ->where('created_at', '>=', now()->startOfMonth())]);
            }, fn ($q) => $q->select('users.*', 'users.xp as score'))
            ->get()
            ->sort(fn ($a, $b) => [(int) $b->score, $a->id] <=> [(int) $a->score, $b->id])
            ->values();

        $rank = $students->search(fn ($u) => $u->id === $user->id);

        return view('student.leaderboard', [
            'user' => $user,
            'period' => $period,
            'top' => $students->take(10),
            'rank' => $rank === false ? null : $rank + 1,
            'myScore' => $rank === false ? 0 : (int) $students[$rank]->score,
        ]);
    }
}
