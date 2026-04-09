<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SiteVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // 1. Update current user's login date/time
        $user                = Auth::user();
        $user->last_login_at = now();
        $user->save();

        // 2. Fetch Recent Activity
        $recentActivity = User::whereNotNull('last_login_at')->withoutRole('Super Admin')
            ->orderBy('last_login_at', 'desc')
            ->take(5)
            ->get();

        $showVisitAnalytics = $user && $user->hasRole('Super Admin');
        $visitLabels = [];
        $visitCounts = [];
        $visitTotal = 0;
        $uniqueVisitors = 0;
        $countryLabels = [];
        $countryCounts = [];
        $visitRangeStart = null;
        $visitRangeEnd = null;
        $visitRangeLabel = null;
        $visitToday = 0;
        if ($showVisitAnalytics) {
            $visitRangeStart = request()->get('visit_start');
            $visitRangeEnd = request()->get('visit_end');
            $startDate = $visitRangeStart ? Carbon::parse($visitRangeStart)->startOfDay() : Carbon::today()->subDays(29)->startOfDay();
            $endDate = $visitRangeEnd ? Carbon::parse($visitRangeEnd)->endOfDay() : Carbon::today()->endOfDay();
            $rangeDays = min($startDate->diffInDays($endDate) + 1, 60);
            $endDate = $startDate->copy()->addDays($rangeDays - 1)->endOfDay();
            $visitRangeLabel = $visitRangeStart || $visitRangeEnd
                ? $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y')
                : 'Last 30 Days';
            $days = collect(range(0, $rangeDays - 1))
                ->map(fn ($offset) => $startDate->copy()->addDays($offset)->format('d M'))
                ->values();

            $dailyRows = SiteVisit::selectRaw('DATE(visited_at) as day, COUNT(*) as total')
                ->whereBetween('visited_at', [$startDate, $endDate])
                ->groupBy('day')
                ->pluck('total', 'day');

            $visitLabels = $days->all();
            $visitCounts = $days->map(function ($label, $index) use ($startDate, $dailyRows) {
                $day = $startDate->copy()->addDays($index)->toDateString();
                return (int) ($dailyRows[$day] ?? 0);
            })->values()->all();

            $visitTotal = array_sum($visitCounts);
            $uniqueVisitors = SiteVisit::whereBetween('visited_at', [$startDate, $endDate])
                ->distinct('ip_address')
                ->count('ip_address');

            $countryRows = SiteVisit::selectRaw('country, COUNT(*) as total')
                ->whereBetween('visited_at', [$startDate, $endDate])
                ->groupBy('country')
                ->orderByDesc('total')
                ->take(7)
                ->get();

            $countryLabels = $countryRows->pluck('country')->map(fn ($c) => $c ?: 'Unknown')->values()->all();
            $countryCounts = $countryRows->pluck('total')->map(fn ($c) => (int) $c)->values()->all();

            $visitToday = SiteVisit::whereDate('visited_at', Carbon::today())->count();
        }

        return view('dashboard/dashboard', compact(
            'recentActivity',
            'showVisitAnalytics',
            'visitLabels',
            'visitCounts',
            'visitTotal',
            'uniqueVisitors',
            'countryLabels',
            'countryCounts',
            'visitToday',
            'visitRangeStart',
            'visitRangeEnd',
            'visitRangeLabel'
        ));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
