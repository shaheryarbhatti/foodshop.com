<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Models\SiteVisit;
use App\Services\CurrencyService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    public function index(Request $request)
    {
        $user                = Auth::user();
        if ($user && $user->hasAnyRole(['Staff', 'Driver'])) {
            return redirect()->route('frontend.staff.dashboard');
        }

        $user->last_login_at = now();
        $user->save();

        $recentActivity = User::whereNotNull('last_login_at')->withoutRole('Super Admin')
            ->with('roles')
            ->orderBy('last_login_at', 'desc')
            ->take(5)
            ->get();

        [$rangeKey, $rangeLabel, $startDate, $endDate] = $this->resolveDashboardRange($request);
        $rangeDays = max(1, $startDate->diffInDays($endDate) + 1);

        $ordersBaseQuery = Order::query()->whereBetween('created_at', [$startDate, $endDate]);
        $grossRevenue = (float) (clone $ordersBaseQuery)->sum('grand_total');
        $netSales = (float) (clone $ordersBaseQuery)->sum(DB::raw('subtotal + vat_amount'));
        $shippingCosts = (float) (clone $ordersBaseQuery)->sum('shipping_costs');
        $ordersReceived = (int) (clone $ordersBaseQuery)->count();
        $newCustomers = (int) Customer::query()->whereBetween('created_at', [$startDate, $endDate])->count();
        $activeBranches = (int) (clone $ordersBaseQuery)->distinct('organization_id')->count('organization_id');
        $averageOrderValue = $ordersReceived > 0 ? $grossRevenue / $ordersReceived : 0.0;
        $avgGrossDailyRevenue = $grossRevenue / $rangeDays;
        $avgNetDailySales = $netSales / $rangeDays;
        $averageOrderValueFormatted = $this->moneyValue($averageOrderValue);

        $soldItems = (int) OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum('order_items.quantity');

        $paidOrders = (int) (clone $ordersBaseQuery)
            ->where('payment_status', 'paid')
            ->count();

        $chartGranularity = $rangeDays > 62 ? 'month' : 'day';
        $timeline = $this->timelineBuckets($startDate, $endDate, $chartGranularity);
        $timelineLabels = $timeline['labels'];
        $timelineKeys = $timeline['keys'];
        $timelineFormat = $timeline['format'];

        $revenueRows = Order::query()
            ->selectRaw($this->dateSelectExpression('created_at', $chartGranularity) . ' as bucket')
            ->selectRaw('SUM(grand_total) as total_revenue')
            ->selectRaw('COUNT(*) as total_orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $revenueSeries = [];
        $ordersSeries = [];
        foreach ($timelineKeys as $bucket) {
            $revenueSeries[] = round((float) ($revenueRows[$bucket]->total_revenue ?? 0), 2);
            $ordersSeries[] = (int) ($revenueRows[$bucket]->total_orders ?? 0);
        }

        $customerRows = Customer::query()
            ->selectRaw($this->dateSelectExpression('created_at', $chartGranularity) . ' as bucket')
            ->selectRaw('COUNT(*) as total_customers')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $customerSeries = [];
        foreach ($timelineKeys as $bucket) {
            $customerSeries[] = (int) ($customerRows[$bucket]->total_customers ?? 0);
        }

        $deliveryTypeRows = Order::query()
            ->selectRaw($this->dateSelectExpression('created_at', $chartGranularity) . ' as bucket')
            ->selectRaw("SUM(CASE WHEN order_type = 'delivery' THEN 1 ELSE 0 END) as delivery_total")
            ->selectRaw("SUM(CASE WHEN order_type = 'pick_up' THEN 1 ELSE 0 END) as pickup_total")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get()
            ->keyBy('bucket');

        $deliverySeries = [];
        $pickupSeries = [];
        foreach ($timelineKeys as $bucket) {
            $deliverySeries[] = (int) ($deliveryTypeRows[$bucket]->delivery_total ?? 0);
            $pickupSeries[] = (int) ($deliveryTypeRows[$bucket]->pickup_total ?? 0);
        }

        $statusRows = Order::query()
            ->select('order_status', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $statusChartLabels = [];
        $statusChartSeries = [];
        foreach (Order::statusOptions() as $status => $title) {
            $statusChartLabels[] = $title;
            $statusChartSeries[] = (int) ($statusRows[$status] ?? 0);
        }

        $paymentRows = Order::query()
            ->select('payment_method', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();
        $paymentLabels = $paymentRows->map(fn ($row) => $this->paymentMethodTitle($row->payment_method))->values()->all();
        $paymentSeries = $paymentRows->pluck('total')->map(fn ($value) => (int) $value)->values()->all();

        $branchRows = Order::query()
            ->leftJoin('organizations', 'organizations.id', '=', 'orders.organization_id')
            ->selectRaw('COALESCE(organizations.name, "Unknown Branch") as branch_name')
            ->selectRaw('COUNT(orders.id) as total_orders')
            ->selectRaw('SUM(orders.grand_total) as total_revenue')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('branch_name')
            ->orderByDesc('total_revenue')
            ->limit(8)
            ->get();
        $branchLabels = $branchRows->pluck('branch_name')->values()->all();
        $branchRevenueSeries = $branchRows->pluck('total_revenue')->map(fn ($value) => round((float) $value, 2))->values()->all();
        $branchOrderSeries = $branchRows->pluck('total_orders')->map(fn ($value) => (int) $value)->values()->all();

        $productRows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->selectRaw('COALESCE(products.title, order_items.title, "Unknown Product") as product_name')
            ->selectRaw('SUM(order_items.quantity) as total_quantity')
            ->selectRaw('SUM(order_items.line_total + COALESCE(order_items.addition_amount, 0) + COALESCE(order_items.addition_tax, 0)) as total_sales')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->limit(8)
            ->get();
        $productLabels = $productRows->pluck('product_name')->values()->all();
        $productQtySeries = $productRows->pluck('total_quantity')->map(fn ($value) => (int) $value)->values()->all();
        $productSalesSeries = $productRows->pluck('total_sales')->map(fn ($value) => round((float) $value, 2))->values()->all();

        $topCustomers = Order::query()
            ->selectRaw("TRIM(CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, ''))) as customer_name")
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(grand_total) as total_spend')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('customer_name')
            ->orderByDesc('total_spend')
            ->limit(6)
            ->get();

        $summaryCards = [
            ['title' => 'Gross revenue during this period', 'value' => $this->moneyValue($grossRevenue), 'icon' => 'fa-wallet', 'theme' => 'is-violet'],
            ['title' => 'Average gross daily revenue', 'value' => $this->moneyValue($avgGrossDailyRevenue), 'icon' => 'fa-sack-dollar', 'theme' => 'is-amber'],
            ['title' => 'Net sales during this period', 'value' => $this->moneyValue($netSales), 'icon' => 'fa-chart-line', 'theme' => 'is-sky'],
            ['title' => 'Average net daily sales', 'value' => $this->moneyValue($avgNetDailySales), 'icon' => 'fa-chart-simple', 'theme' => 'is-mint'],
            ['title' => 'Orders received', 'value' => number_format($ordersReceived), 'icon' => 'fa-bag-shopping', 'theme' => 'is-cyan'],
            ['title' => 'Sold items', 'value' => number_format($soldItems), 'icon' => 'fa-box-open', 'theme' => 'is-pink'],
            ['title' => 'Shipping costs', 'value' => $this->moneyValue($shippingCosts), 'icon' => 'fa-truck-fast', 'theme' => 'is-orange'],
            ['title' => 'New customers', 'value' => number_format($newCustomers), 'icon' => 'fa-users', 'theme' => 'is-lime'],
        ];

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
            'rangeKey',
            'rangeLabel',
            'startDate',
            'endDate',
            'rangeDays',
            'summaryCards',
            'grossRevenue',
            'netSales',
            'shippingCosts',
            'ordersReceived',
            'soldItems',
            'newCustomers',
            'activeBranches',
            'averageOrderValue',
            'averageOrderValueFormatted',
            'paidOrders',
            'timelineLabels',
            'revenueSeries',
            'ordersSeries',
            'customerSeries',
            'deliverySeries',
            'pickupSeries',
            'statusChartLabels',
            'statusChartSeries',
            'paymentLabels',
            'paymentSeries',
            'branchLabels',
            'branchRevenueSeries',
            'branchOrderSeries',
            'productLabels',
            'productQtySeries',
            'productSalesSeries',
            'topCustomers',
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

    private function resolveDashboardRange(Request $request): array
    {
        $rangeKey = (string) $request->get('range', 'year');
        $today = Carbon::today();

        switch ($rangeKey) {
            case 'last_month':
                $startDate = $today->copy()->subMonthNoOverflow()->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();
                $label = 'Last month';
                break;
            case 'current_month':
                $startDate = $today->copy()->startOfMonth();
                $endDate = $today->copy()->endOfDay();
                $label = 'Current month';
                break;
            case 'last_7_days':
                $startDate = $today->copy()->subDays(6)->startOfDay();
                $endDate = $today->copy()->endOfDay();
                $label = 'Last 7 days';
                break;
            case 'custom':
                $fromDate = $request->get('from_date');
                $untilDate = $request->get('until_date');
                $startDate = $fromDate ? Carbon::parse($fromDate)->startOfDay() : $today->copy()->startOfMonth();
                $endDate = $untilDate ? Carbon::parse($untilDate)->endOfDay() : $today->copy()->endOfDay();
                if ($startDate->gt($endDate)) {
                    [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
                }
                $label = 'Custom range';
                break;
            case 'year':
            default:
                $rangeKey = 'year';
                $startDate = $today->copy()->startOfYear();
                $endDate = $today->copy()->endOfDay();
                $label = 'Year';
                break;
        }

        return [$rangeKey, $label, $startDate, $endDate];
    }

    private function timelineBuckets(Carbon $startDate, Carbon $endDate, string $granularity): array
    {
        $labels = [];
        $keys = [];

        if ($granularity === 'month') {
            $cursor = $startDate->copy()->startOfMonth();
            while ($cursor->lte($endDate)) {
                $labels[] = $cursor->format('M Y');
                $keys[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }
            return ['labels' => $labels, 'keys' => $keys, 'format' => '%Y-%m'];
        }

        foreach (CarbonPeriod::create($startDate->copy()->startOfDay(), $endDate->copy()->startOfDay()) as $day) {
            $labels[] = $day->format('d M');
            $keys[] = $day->format('Y-m-d');
        }

        return ['labels' => $labels, 'keys' => $keys, 'format' => '%Y-%m-%d'];
    }

    private function dateSelectExpression(string $column, string $granularity): string
    {
        return $granularity === 'month'
            ? "DATE_FORMAT({$column}, '%Y-%m')"
            : "DATE_FORMAT({$column}, '%Y-%m-%d')";
    }

    private function paymentMethodTitle(?string $code): string
    {
        if (! $code) {
            return 'Unknown';
        }

        return str($code)->replace('_', ' ')->title()->toString();
    }

    private function moneyValue(float|int $amount): string
    {
        return CurrencyService::formatAmount((float) $amount);
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

