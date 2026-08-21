<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\RoomBooking;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user->is_admin || $user->isManager() || $user->isAccountant()) {
                return $next($request);
            }
            
            // Supervisors can see most reports except financial/profit
            if ($user->isSupervisor()) {
                $allowedRoutes = ['admin.reports.index', 'admin.reports.sales', 'admin.reports.products', 'admin.reports.daily', 'admin.reports.hotel-bookings'];
                if (in_array($request->route()->getName(), $allowedRoutes)) {
                    return $next($request);
                }
            }

            // Cashiers can only see index and daily report
            if ($user->isCashier()) {
                $allowedRoutes = ['admin.reports.index', 'admin.reports.daily'];
                if (in_array($request->route()->getName(), $allowedRoutes)) {
                    return $next($request);
                }
            }

            // Receptionists can see hotel booking reports
            if ($user->isReceptionist()) {
                $allowedRoutes = [
                    'admin.reports.index',
                    'admin.reports.hotel-bookings',
                    'admin.reports.export.hotel-bookings',
                ];
                if (in_array($request->route()->getName(), $allowedRoutes, true)) {
                    return $next($request);
                }
            }

            abort(403, 'You do not have permission to access this report.');
        });
    }
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Helper to generate CSV download response
     */
    private function exportCsv(string $filename, array $headers, array $data): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $data) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add headers
            fputcsv($handle, $headers);
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    public function sales(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $sales = Sale::completed()
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with(['user', 'customer', 'items'])
            ->latest()
            ->get();

        $summary = [
            'total_sales' => $sales->sum('total'),
            'total_profit' => $sales->sum(fn($sale) => $sale->getProfit()),
            'total_vat' => $sales->sum('tax'),
            'total_subtotal' => $sales->sum('subtotal'),
            'total_discount' => $sales->sum('discount'),
            'total_transactions' => $sales->count(),
            'cash_sales' => $sales->where('payment_method', 'cash')->sum('total'),
            'transfer_sales' => $sales->where('payment_method', 'transfer')->sum('total'),
            'pos_sales' => $sales->where('payment_method', 'pos')->sum('total'),
            'credit_sales' => $sales->where('is_credit_sale', true)->sum('total'),
            'average_sale' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
        ];

        // Daily breakdown
        $dailySales = $sales->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'))
            ->map(fn($daySales) => [
                'total' => $daySales->sum('total'),
                'profit' => $daySales->sum(fn($sale) => $sale->getProfit()),
                'count' => $daySales->count(),
            ]);

        // Get Purchase Requisitions for the date range
        $purchaseRequisitions = PurchaseRequisition::whereBetween('pr_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with(['requestedBy', 'approvedBy'])
            ->latest('pr_date')
            ->get();

        $prSummary = [
            'total_amount' => $purchaseRequisitions->sum('amount'),
            'total_count' => $purchaseRequisitions->count(),
            'pending' => $purchaseRequisitions->where('status', 'pending')->count(),
            'approved' => $purchaseRequisitions->where('status', 'approved')->count(),
            'completed' => $purchaseRequisitions->where('status', 'completed')->count(),
            'rejected' => $purchaseRequisitions->where('status', 'rejected')->count(),
        ];

        return view('admin.reports.sales', compact('sales', 'summary', 'dailySales', 'startDate', 'endDate', 'purchaseRequisitions', 'prSummary'));
    }

    /**
     * Delete all sales records and pending orders (including test records)
     */
    public function deleteAllSales(Request $request)
    {
        try {
            // Count before deletion
            $deletedSalesCount = Sale::count();
            $deletedItemsCount = SaleItem::count();
            
            // Delete all sale items first (if cascade doesn't work)
            SaleItem::query()->delete();
            
            // Delete all sales (including pending orders)
            Sale::query()->delete();
            
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully deleted {$deletedSalesCount} sales record(s) (including pending orders) and {$deletedItemsCount} sale item(s). All test records have been cleared.",
                    'sales_deleted' => $deletedSalesCount,
                    'items_deleted' => $deletedItemsCount,
                ]);
            }
            
            return redirect()->route('admin.reports.sales')
                ->with('success', "Successfully deleted {$deletedSalesCount} sales record(s) (including pending orders) and {$deletedItemsCount} sale item(s). All test records have been cleared.");
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting sales: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->route('admin.reports.sales')
                ->with('error', 'Error deleting sales: ' . $e->getMessage());
        }
    }

    public function products(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $topProducts = SaleItem::selectRaw('product_id, product_name, SUM(quantity) as total_quantity, SUM(total) as total_revenue')
            ->whereHas('sale', fn($q) => $q->completed()->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->take(20)
            ->get();

        $lowStockProducts = Product::active()->lowStock()->with('category')->get();

        $outOfStockProducts = Product::active()->where('stock_quantity', 0)->with('category')->get();

        return view('admin.reports.products', compact('topProducts', 'lowStockProducts', 'outOfStockProducts', 'startDate', 'endDate'));
    }

    public function customers(Request $request)
    {
        $customersWithDebt = Customer::withOutstandingBalance()
            ->orderByDesc('credit_balance')
            ->get();

        $totalDebt = $customersWithDebt->sum('credit_balance');

        // Top customers by purchase amount
        $topCustomers = Customer::withSum(['sales as total_purchases' => fn($q) => $q->completed()], 'total')
            ->orderByDesc('total_purchases')
            ->take(20)
            ->get();

        return view('admin.reports.customers', compact('customersWithDebt', 'totalDebt', 'topCustomers'));
    }

    public function suppliers(Request $request)
    {
        $suppliersWithDebt = Supplier::withOutstandingBalance()
            ->orderByDesc('balance_owed')
            ->get();

        $totalDebt = $suppliersWithDebt->sum('balance_owed');

        // Suppliers with most supplies
        $topSuppliers = Supplier::withSum('supplies', 'total_amount')
            ->orderByDesc('supplies_sum_total_amount')
            ->take(20)
            ->get();

        return view('admin.reports.suppliers', compact('suppliersWithDebt', 'totalDebt', 'topSuppliers'));
    }

    public function profit(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $sales = Sale::completed()
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with('items')
            ->get();

        $expenses = \App\Models\Expense::whereBetween('expense_date', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        $totalRevenue = $sales->sum('total');
        $productCost = $sales->sum(fn($sale) => $sale->items->sum(fn($item) => $item->cost_price * $item->quantity));
        $directExpenses = $expenses->where('is_direct_cost', true)->sum('amount');
        
        $totalDirectCost = $productCost + $directExpenses;
        $grossProfit = $totalRevenue - $totalDirectCost;
        
        $indirectExpenses = $expenses->where('is_direct_cost', false)->sum('amount');
        $netProfit = $grossProfit - $indirectExpenses;

        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Daily breakdown
        $dailyProfit = $sales->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'))
            ->map(function ($daySales, $date) use ($expenses) {
                $revenue = $daySales->sum('total');
                $prodCost = $daySales->sum(fn($sale) => $sale->items->sum(fn($item) => $item->cost_price * $item->quantity));
                
                $dayExpenses = $expenses->filter(fn($e) => $e->expense_date->format('Y-m-d') === $date);
                $dirExp = $dayExpenses->where('is_direct_cost', true)->sum('amount');
                $indirExp = $dayExpenses->where('is_direct_cost', false)->sum('amount');
                
                $gross = $revenue - ($prodCost + $dirExp);
                $net = $gross - $indirExp;

                return [
                    'revenue' => $revenue,
                    'cost' => $prodCost + $dirExp,
                    'gross_profit' => $gross,
                    'indirect_expenses' => $indirExp,
                    'net_profit' => $net,
                ];
            });

        return view('admin.reports.profit', compact(
            'totalRevenue', 'productCost', 'directExpenses', 'totalDirectCost', 
            'grossProfit', 'indirectExpenses', 'netProfit', 'grossMargin', 'netMargin',
            'dailyProfit', 'startDate', 'endDate'
        ));
    }

    public function daily(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : now();
        $user = auth()->user();
        $isCashier = $user->isCashier();

        $salesQuery = Sale::completed()
            ->whereDate('created_at', $date)
            ->with(['user', 'items', 'customer']);

        // For cashiers, only show their own sales
        if ($isCashier) {
            $salesQuery->where('user_id', $user->id);
        }

        $sales = $salesQuery->get();

        $summary = [
            'total_sales' => $sales->sum('total'),
            'total_profit' => $sales->sum(fn($sale) => $sale->getProfit()),
            'total_vat' => $sales->sum('tax'),
            'total_subtotal' => $sales->sum('subtotal'),
            'total_discount' => $sales->sum('discount'),
            'transactions' => $sales->count(),
            'cash' => $sales->where('payment_method', 'cash')->sum('total'),
            'transfer' => $sales->where('payment_method', 'transfer')->sum('total'),
            'pos' => $sales->where('payment_method', 'pos')->sum('total'),
            'credit' => $sales->where('is_credit_sale', true)->sum('total'),
        ];

        // Fetch Previous Day Summary for comparison
        $prevDate = (clone $date)->subDay();
        $prevSalesQuery = Sale::completed()->whereDate('created_at', $prevDate);
        if ($isCashier) {
            $prevSalesQuery->where('user_id', $user->id);
        }
        $prevSales = $prevSalesQuery->get();
        
        $prevSummary = [
            'total_sales' => $prevSales->sum('total'),
            'total_profit' => $prevSales->sum(fn($sale) => $sale->getProfit()),
            'transactions' => $prevSales->count(),
        ];

        // Sales by hour
        $hourlyData = $sales->groupBy(fn($sale) => $sale->created_at->format('H'))
            ->map(fn($hourSales) => $hourSales->sum('total'));

        // Sales by staff (only relevant if not cashier, or show only cashier's data)
        $staffSales = $sales->groupBy('user_id')
            ->map(fn($userSales) => [
                'user' => $userSales->first()->user->name,
                'total' => $userSales->sum('total'),
                'count' => $userSales->count(),
            ]);

        // Get pending orders for the date
        $pendingOrdersQuery = Sale::pending()
            ->whereDate('created_at', $date)
            ->with(['user', 'items', 'customer', 'table', 'tableGuest']);

        // For cashiers, only show their own pending orders
        if ($isCashier) {
            $pendingOrdersQuery->where('user_id', $user->id);
        }

        $pendingOrders = $pendingOrdersQuery->latest()->get();

        // Get Purchase Requisitions for the date
        $purchaseRequisitions = PurchaseRequisition::whereDate('pr_date', $date)
            ->with(['requestedBy', 'approvedBy'])
            ->latest('pr_date')
            ->get();

        $prSummary = [
            'total_amount' => $purchaseRequisitions->sum('amount'),
            'total_count' => $purchaseRequisitions->count(),
            'pending' => $purchaseRequisitions->where('status', 'pending')->count(),
            'approved' => $purchaseRequisitions->where('status', 'approved')->count(),
            'completed' => $purchaseRequisitions->where('status', 'completed')->count(),
            'rejected' => $purchaseRequisitions->where('status', 'rejected')->count(),
        ];

        return view('admin.reports.daily', compact('date', 'sales', 'summary', 'prevSummary', 'hourlyData', 'staffSales', 'isCashier', 'pendingOrders', 'purchaseRequisitions', 'prSummary'));
    }

    /**
     * Export Daily Report to CSV
     */
    public function exportDaily(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : now();
        $user = auth()->user();
        $isCashier = $user->isCashier();

        $salesQuery = Sale::completed()
            ->whereDate('created_at', $date)
            ->with(['user', 'items', 'customer']);

        // For cashiers, only export their own sales
        if ($isCashier) {
            $salesQuery->where('user_id', $user->id);
        }

        $sales = $salesQuery->get();

        $headers = ['Invoice', 'Time', 'Cashier', 'Customer', 'Items', 'Payment Method', 'Total'];
        
        $data = $sales->map(function ($sale) {
            return [
                $sale->invoice_number,
                $sale->created_at->format('H:i:s'),
                $sale->user->name,
                $sale->customer?->name ?? 'Walk-in',
                $sale->items->sum('quantity') . ' items',
                $sale->is_credit_sale ? 'Credit' : ucfirst($sale->payment_method),
                number_format($sale->total, 2),
            ];
        })->toArray();

        $filename = 'daily-report-' . $date->format('Y-m-d') . '.csv';
        
        return $this->exportCsv($filename, $headers, $data);
    }

    /**
     * Export Sales Report to CSV
     */
    public function exportSales(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $sales = Sale::completed()
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with(['user', 'customer', 'items'])
            ->latest()
            ->get();

        $headers = ['Invoice', 'Date/Time', 'Cashier', 'Customer', 'Payment Method', 'Total', 'Profit'];
        
        $data = $sales->map(function ($sale) {
            return [
                $sale->invoice_number,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->user->name,
                $sale->customer?->name ?? 'Walk-in',
                $sale->is_credit_sale ? 'Credit' : ucfirst($sale->payment_method),
                number_format($sale->total, 2),
                number_format($sale->getProfit(), 2),
            ];
        })->toArray();

        $filename = 'sales-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';
        
        return $this->exportCsv($filename, $headers, $data);
    }

    /**
     * Export Products Report to CSV
     */
    public function exportProducts(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $topProducts = SaleItem::selectRaw('product_id, product_name, SUM(quantity) as total_quantity, SUM(total) as total_revenue')
            ->whereHas('sale', fn($q) => $q->completed()->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->get();

        $headers = ['Rank', 'Product', 'Quantity Sold', 'Revenue'];
        
        $data = $topProducts->map(function ($product, $index) {
            return [
                $index + 1,
                $product->product_name,
                number_format($product->total_quantity),
                number_format($product->total_revenue, 2),
            ];
        })->toArray();

        $filename = 'products-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';
        
        return $this->exportCsv($filename, $headers, $data);
    }

    /**
     * Export Customers Report to CSV
     */
    public function exportCustomers(Request $request)
    {
        $customersWithDebt = Customer::withOutstandingBalance()
            ->orderByDesc('credit_balance')
            ->get();

        $headers = ['Customer Name', 'Phone', 'Credit Balance', 'Credit Limit'];
        
        $data = $customersWithDebt->map(function ($customer) {
            return [
                $customer->name,
                $customer->phone,
                number_format($customer->credit_balance, 2),
                number_format($customer->credit_limit, 2),
            ];
        })->toArray();

        $filename = 'customers-debt-report-' . now()->format('Y-m-d') . '.csv';
        
        return $this->exportCsv($filename, $headers, $data);
    }

    /**
     * Export Suppliers Report to CSV
     */
    public function exportSuppliers(Request $request)
    {
        $suppliersWithDebt = Supplier::withOutstandingBalance()
            ->orderByDesc('balance_owed')
            ->get();

        $headers = ['Supplier Name', 'Phone', 'Balance Owed'];
        
        $data = $suppliersWithDebt->map(function ($supplier) {
            return [
                $supplier->name,
                $supplier->phone,
                number_format($supplier->balance_owed, 2),
            ];
        })->toArray();

        $filename = 'suppliers-debt-report-' . now()->format('Y-m-d') . '.csv';
        
        return $this->exportCsv($filename, $headers, $data);
    }

    /**
     * Export Profit Report to CSV
     */
    public function exportProfit(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $sales = Sale::completed()
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with('items')
            ->get();

        $dailyProfit = $sales->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'))
            ->map(function ($daySales) {
                $revenue = $daySales->sum('total');
                $cost = $daySales->sum(fn($sale) => $sale->items->sum(fn($item) => $item->cost_price * $item->quantity));
                return [
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $revenue - $cost,
                ];
            });

        $headers = ['Date', 'Revenue', 'Cost', 'Profit', 'Margin %'];
        
        $data = $dailyProfit->map(function ($day, $date) {
            $margin = $day['revenue'] > 0 ? ($day['profit'] / $day['revenue']) * 100 : 0;
            return [
                $date,
                number_format($day['revenue'], 2),
                number_format($day['cost'], 2),
                number_format($day['profit'], 2),
                number_format($margin, 1) . '%',
            ];
        })->values()->toArray();

        $filename = 'profit-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';
        
        return $this->exportCsv($filename, $headers, $data);
    }

    /**
     * Hotel Bookings Report
     */
    public function hotelBookings(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $bookings = RoomBooking::notCancelled()
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with(['room.category', 'customer', 'user'])
            ->latest()
            ->get();

        $summary = [
            'total_revenue' => $bookings->sum('total'),
            'total_bookings' => $bookings->count(),
            'confirmed_bookings' => $bookings->where('status', 'confirmed')->count(),
            'checked_in_bookings' => $bookings->where('status', 'checked-in')->count(),
            'checked_out_bookings' => $bookings->where('status', 'checked-out')->count(),
            'cancelled_bookings' => $bookings->where('status', 'cancelled')->count(),
            'cash_bookings' => $bookings->where('payment_method', 'cash')->sum('total'),
            'transfer_bookings' => $bookings->where('payment_method', 'transfer')->sum('total'),
            'pos_bookings' => $bookings->where('payment_method', 'pos')->sum('total'),
            'credit_bookings' => $bookings->where('is_credit_booking', true)->sum('total'),
            'overnight_bookings' => $bookings->where('booking_type', 'overnight')->count(),
            'short_stay_bookings' => $bookings->where('booking_type', 'short-stay')->count(),
            'total_nights' => $bookings->where('booking_type', 'overnight')->sum('number_of_nights'),
            'total_hours' => $bookings->where('booking_type', 'short-stay')->sum('hours_stayed'),
            'average_booking' => $bookings->count() > 0 ? $bookings->sum('total') / $bookings->count() : 0,
        ];

        // Daily breakdown
        $dailyBookings = $bookings->groupBy(fn($booking) => $booking->created_at->format('Y-m-d'))
            ->map(fn($dayBookings) => [
                'total' => $dayBookings->sum('total'),
                'count' => $dayBookings->count(),
                'overnight' => $dayBookings->where('booking_type', 'overnight')->count(),
                'short_stay' => $dayBookings->where('booking_type', 'short-stay')->count(),
            ]);

        // Bookings by room category
        $bookingsByCategory = $bookings->groupBy(function($booking) {
            return $booking->room && $booking->room->category ? $booking->room->category->name : 'Unknown';
        })->map(fn($categoryBookings) => [
            'count' => $categoryBookings->count(),
            'revenue' => $categoryBookings->sum('total'),
        ]);

        return view('admin.reports.hotel-bookings', compact('bookings', 'summary', 'dailyBookings', 'bookingsByCategory', 'startDate', 'endDate'));
    }

    /**
     * Export Hotel Bookings Report to CSV
     */
    public function exportHotelBookings(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $bookings = RoomBooking::notCancelled()
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with(['room.category', 'customer', 'user'])
            ->latest()
            ->get();

        $headers = ['Booking Number', 'Date', 'Room', 'Category', 'Guest', 'Check-in', 'Check-out', 'Type', 'Duration', 'Total', 'Paid', 'Payment Method', 'Status', 'Staff'];
        
        $data = $bookings->map(function ($booking) {
            $duration = $booking->booking_type === 'short-stay' 
                ? $booking->hours_stayed . ' hour(s)' 
                : $booking->number_of_nights . ' night(s)';
            
            return [
                $booking->booking_number,
                $booking->created_at->format('Y-m-d H:i'),
                $booking->room ? $booking->room->room_number : 'N/A',
                $booking->room && $booking->room->category ? $booking->room->category->name : 'N/A',
                $booking->customer ? $booking->customer->name : 'Walk-in',
                $booking->check_in_date ? $booking->check_in_date->format('Y-m-d') : 'N/A',
                $booking->check_out_date ? $booking->check_out_date->format('Y-m-d') : 'N/A',
                ucfirst($booking->booking_type ?? 'overnight'),
                $duration,
                number_format((float)$booking->total, 2),
                number_format((float)$booking->amount_paid, 2),
                ucfirst($booking->payment_method),
                ucfirst($booking->status),
                $booking->user ? $booking->user->name : 'N/A',
            ];
        })->toArray();

        $filename = 'hotel-bookings-report-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.csv';
        
        return $this->exportCsv($filename, $headers, $data);
    }
}


