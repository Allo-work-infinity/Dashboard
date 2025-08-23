<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\JobOffer;
use App\Models\JobApplication;
use App\Models\UserSubscription;
use App\Models\PaymentTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();

        // --- Revenue / sales ---
        $totalSales = (float) PaymentTransaction::completed()->sum('amount');

        $salesToday = (float) PaymentTransaction::completed()
            ->whereDate('processed_at', $now->toDateString())
            ->sum('amount');

        // Growth vs previous periods
        $salesLast30 = (float) PaymentTransaction::completed()
            ->whereBetween('processed_at', [$now->copy()->subDays(30)->startOfDay(), $now->endOfDay()])
            ->sum('amount');

        $salesPrev30 = (float) PaymentTransaction::completed()
            ->whereBetween('processed_at', [$now->copy()->subDays(60)->startOfDay(), $now->copy()->subDays(31)->endOfDay()])
            ->sum('amount');

        $growthTotalSales = $salesPrev30 > 0 ? (($salesLast30 - $salesPrev30) / $salesPrev30) * 100 : null;

        $salesYesterday = (float) PaymentTransaction::completed()
            ->whereDate('processed_at', $now->copy()->subDay()->toDateString())
            ->sum('amount');

        $growthSalesToday = $salesYesterday > 0 ? (($salesToday - $salesYesterday) / $salesYesterday) * 100 : null;

        // --- Core counters ---
        $metrics = [
            'total_sales'          => $totalSales,
            'sales_today'          => $salesToday,
            'open_job_offers'      => JobOffer::open()->count(),
            'active_subscriptions' => UserSubscription::current()->count(),
            'users'                => User::count(),
            'companies'            => Company::count(),
            'applications'         => JobApplication::count(),
        ];

        // --- “Spendings Stats” (daily revenue last 7 days) ---
        $start = $now->copy()->subDays(6)->startOfDay();
        $daily = PaymentTransaction::completed()
            ->selectRaw('DATE(processed_at) as d, SUM(amount) as total')
            ->where('processed_at', '>=', $start)
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('total', 'd');

        // Fill missing days with 0
        $spendingsSeries = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $spendingsSeries[] = [
                'date'  => $date,
                'value' => (float) ($daily[$date] ?? 0),
            ];
        }

        // --- Open invoices (pending payments) ---
        $openInvoices = PaymentTransaction::pending()
            ->with(['user:id,first_name,last_name,email'])
            ->latest()
            ->take(10)
            ->get(['id','user_id','konnect_payment_id','amount','currency','status','created_at']);

        // --- “User Percentage” (example breakdown) ---
        $verifiedCount = User::query()
            ->where(function ($q) {
                $q->whereNotNull('email_verified_at')
                  ->orWhere('is_email_verified', true);
            })->count();

        $adminCount = User::where('is_admin', true)->count();
        $unverifiedCount = max(0, $metrics['users'] - $verifiedCount);

        $userBreakdown = [
            'verified'   => $verifiedCount,
            'unverified' => $unverifiedCount,
            'admins'     => $adminCount,
        ];

        // --- Recent users (to fill the small list on the right) ---
        $recentUsers = User::latest('created_at')->take(5)
            ->get(['id','first_name','last_name','email']);

        return view('dashboard', [
            'metrics'           => $metrics,
            'growth'            => [
                'total_sales' => $growthTotalSales,
                'sales_today' => $growthSalesToday,
            ],
            'spendingsSeries'   => $spendingsSeries,
            'openInvoices'      => $openInvoices,
            'userBreakdown'     => $userBreakdown,
            'recentUsers'       => $recentUsers,
        ]);
    }
}
