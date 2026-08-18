<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StaffAssignment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isCashier = $user->isCashier();

        // For cashiers, only show their own sales
        $salesQuery = Sale::completed()->today();
        if ($isCashier) {
            $salesQuery->where('user_id', $user->id);
        }

        $todaySalesList = (clone $salesQuery)->with('items')->get();
        $todaySales = $todaySalesList->sum('total');
        $todayProfit = $todaySalesList->sum(fn($sale) => $sale->getProfit());
        $todayCost = $todaySalesList->sum(fn($sale) => $sale->getTotalCost());
        $todayTransactions = $todaySalesList->count();

        // Today's Expenses
        $todayExpenses = Expense::whereDate('expense_date', now())->sum('amount');

        // Only show customer/supplier debt and low stock to non-cashiers
        $totalCustomerDebt = $isCashier ? 0 : Customer::sum('credit_balance');
        $totalSupplierDebt = $isCashier ? 0 : Supplier::sum('balance_owed');
        $lowStockProducts = $isCashier ? 0 : Product::active()->lowStock()->count();

        // Recent sales - only cashier's own sales if cashier
        $recentSalesQuery = Sale::with(['user', 'customer'])->completed();
        if ($isCashier) {
            $recentSalesQuery->where('user_id', $user->id);
        }
        $recentSales = $recentSalesQuery->latest()->take(10)->get();

        // Top products - only from cashier's sales if cashier
        $topProductsQuery = Product::withCount(['saleItems as total_sold' => function ($query) use ($isCashier, $user) {
            $query->whereHas('sale', function ($q) use ($isCashier, $user) {
                $q->completed()->today();
                if ($isCashier) {
                    $q->where('user_id', $user->id);
                }
            });
        }]);
        $topProducts = $topProductsQuery->orderByDesc('total_sold')->take(5)->get();

        // Get cashier's assignments/activities
        $myAssignments = null;
        if ($isCashier) {
            $myAssignments = StaffAssignment::with(['section', 'assignedBy'])
                ->where('user_id', $user->id)
                ->current()
                ->latest()
                ->get();
        }

        return view('admin.dashboard', compact(
            'todaySales',
            'todayProfit',
            'todayCost',
            'todayExpenses',
            'totalCustomerDebt',
            'totalSupplierDebt',
            'lowStockProducts',
            'todayTransactions',
            'recentSales',
            'topProducts',
            'isCashier',
            'myAssignments'
        ));
    }
}











