<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TableGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoLiveController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403, 'Only administrators can access go-live tools.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        return view('admin.go-live.index', [
            'counts' => $this->getCounts(),
        ]);
    }

    public function clearOrders(Request $request)
    {
        return $this->performClear($request, ['orders']);
    }

    public function clearCustomers(Request $request)
    {
        return $this->performClear($request, ['customers']);
    }

    public function clearAll(Request $request)
    {
        return $this->performClear($request, ['orders', 'customers']);
    }

    private function getCounts(): array
    {
        return [
            'sales' => Sale::count(),
            'sale_items' => SaleItem::count(),
            'table_guests' => TableGuest::count(),
            'customers' => Customer::count(),
            'customer_payments' => CustomerPayment::count(),
        ];
    }

    private function performClear(Request $request, array $targets)
    {
        try {
            $deleted = DB::transaction(function () use ($targets) {
                $result = [
                    'sales' => 0,
                    'sale_items' => 0,
                    'table_guests' => 0,
                    'customers' => 0,
                    'customer_payments' => 0,
                ];

                if (in_array('orders', $targets, true)) {
                    $result['sale_items'] = SaleItem::count();
                    SaleItem::query()->delete();

                    $result['sales'] = Sale::count();
                    Sale::query()->delete();

                    $result['table_guests'] = TableGuest::count();
                    TableGuest::query()->delete();
                }

                if (in_array('customers', $targets, true)) {
                    if (Sale::count() > 0) {
                        throw new \RuntimeException('Clear all orders before clearing customers.');
                    }

                    $result['customer_payments'] = CustomerPayment::count();
                    CustomerPayment::query()->delete();

                    $result['customers'] = Customer::count();
                    Customer::query()->delete();
                }

                return $result;
            });

            $action = match (true) {
                count($targets) === 2 => 'go_live_clear_all',
                in_array('orders', $targets, true) => 'go_live_clear_orders',
                default => 'go_live_clear_customers',
            };

            AuditLog::log($action, 'Go-live data cleared: ' . json_encode($deleted));

            $message = $this->buildMessage($deleted, $targets);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted' => $deleted,
                    'counts' => $this->getCounts(),
                ]);
            }

            return redirect()->route('admin.go-live.index')->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('admin.go-live.index')->with('error', $e->getMessage());
        }
    }

    private function buildMessage(array $deleted, array $targets): string
    {
        $parts = [];

        if (in_array('orders', $targets, true)) {
            $parts[] = "{$deleted['sales']} sale(s)";
            $parts[] = "{$deleted['sale_items']} sale item(s)";
            $parts[] = "{$deleted['table_guests']} table guest(s)";
        }

        if (in_array('customers', $targets, true)) {
            $parts[] = "{$deleted['customers']} customer(s)";
            $parts[] = "{$deleted['customer_payments']} customer payment(s)";
        }

        return 'Successfully cleared ' . implode(', ', $parts) . '. The system is ready for live use.';
    }
}
