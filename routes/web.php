<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\HotelInventoryController;
use App\Http\Controllers\Admin\KitchenController;
use App\Http\Controllers\Admin\OwnerPurchaseController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PrinterController;
use App\Http\Controllers\Admin\PurchaseRequisitionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\StaffAssignmentController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\HotelPOSController;
use App\Http\Controllers\Admin\RoomBookingController;
use App\Http\Controllers\Admin\RoomCategoryController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BankSettingsController;
use App\Http\Controllers\Admin\CloudFooterController;
use App\Http\Controllers\Admin\GoLiveController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard route (redirects to admin dashboard)
Route::middleware('auth')->get('/dashboard', function () {
    if (auth()->user()->isKitchen()) {
        return redirect()->route('app.kitchen');
    }
    if (auth()->user()->isCashier()) {
        return redirect()->route('app.pos');
    }
    if (auth()->user()->isReceptionist()) {
        return redirect()->route('admin.hotel-pos.index');
    }
    return redirect()->route('admin.dashboard');
})->name('dashboard');

// PWA Manifest
Route::get('/manifest.json', function () {
    return response()->file(public_path('manifest.json'), [
        'Content-Type' => 'application/json',
    ]);
});

// Service Worker
Route::get('/sw.js', function () {
    return response()->file(public_path('sw.js'), [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
    ]);
});

// Admin Routes (protected)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Products
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/receive-payment', [CustomerController::class, 'receivePayment'])->name('customers.receive-payment');
    Route::get('/customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    Route::get('/suppliers/{supplier}/orders', [SupplierController::class, 'orders'])->name('suppliers.orders');
    Route::post('/suppliers/{supplier}/record-supply', [SupplierController::class, 'recordSupply'])->name('suppliers.record-supply');
    Route::post('/suppliers/{supplier}/make-payment', [SupplierController::class, 'makePayment'])->name('suppliers.make-payment');
    Route::get('/suppliers/{supplier}/statement', [SupplierController::class, 'statement'])->name('suppliers.statement');

    // POS
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/products', [POSController::class, 'getProducts'])->name('pos.products');
    Route::get('/pos/rooms', [POSController::class, 'getRooms'])->name('pos.rooms');
    Route::get('/pos/rooms/availability', [POSController::class, 'checkRoomAvailability'])->name('pos.rooms.availability');
    Route::post('/pos', [POSController::class, 'store'])->name('pos.store');

    // Hotel POS (Standalone)
    Route::get('/hotel-pos', [HotelPOSController::class, 'index'])->name('hotel-pos.index');
    Route::get('/hotel-pos/rooms', [HotelPOSController::class, 'getRooms'])->name('hotel-pos.rooms');
    Route::post('/pos/save-pending', [POSController::class, 'savePending'])->name('pos.save-pending');
    Route::get('/pos/get-pending', [POSController::class, 'getPending'])->name('pos.get-pending');
    Route::post('/pos/clear-pending', [POSController::class, 'clearPending'])->name('pos.clear-pending');
    Route::post('/pos/pending/{sale}/delete', [POSController::class, 'deletePending'])->name('pos.delete-pending');
    Route::delete('/pos/pending/{sale}', [POSController::class, 'deletePending']);
    Route::get('/pos/history', [POSController::class, 'history'])->name('pos.history');

    // Supervisor Dashboard - Real-time pending orders (must be before /pos/{sale})
    Route::get('/pos/supervisor', [POSController::class, 'supervisorDashboard'])->name('pos.supervisor');
    Route::get('/pos/supervisor/orders', [POSController::class, 'getAllPendingOrders'])->name('pos.supervisor.orders');

    Route::get('/pos/{sale}', [POSController::class, 'show'])->name('pos.show');
    Route::post('/pos/{sale}/void', [POSController::class, 'void'])->name('pos.void');

    // Shifts
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
    Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
    Route::get('/shifts/current', [ShiftController::class, 'current'])->name('shifts.current');
    Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
    Route::post('/shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');

    // Users (Staff Management)
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/generate-code', [UserController::class, 'generateCode'])->name('users.generate-code');

    // Hotel Inventory
    Route::get('/hotel-inventory', [HotelInventoryController::class, 'index'])->name('hotel-inventory.index');
    Route::get('/hotel-inventory/report', [HotelInventoryController::class, 'report'])->name('hotel-inventory.report');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers');
    Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/hotel-bookings', [ReportController::class, 'hotelBookings'])->name('reports.hotel-bookings');

    // Report Exports
    Route::get('/reports/daily/export', [ReportController::class, 'exportDaily'])->name('reports.export.daily');
    Route::get('/reports/sales/export', [ReportController::class, 'exportSales'])->name('reports.export.sales');
    Route::get('/reports/products/export', [ReportController::class, 'exportProducts'])->name('reports.export.products');
    Route::get('/reports/customers/export', [ReportController::class, 'exportCustomers'])->name('reports.export.customers');
    Route::get('/reports/suppliers/export', [ReportController::class, 'exportSuppliers'])->name('reports.export.suppliers');
    Route::get('/reports/profit/export', [ReportController::class, 'exportProfit'])->name('reports.export.profit');
    Route::get('/reports/hotel-bookings/export', [ReportController::class, 'exportHotelBookings'])->name('reports.export.hotel-bookings');

    // Danger zone (admin only)
    Route::post('/reports/sales/delete-all', [ReportController::class, 'deleteAllSales'])
        ->name('reports.sales.delete-all');

    // Kitchen
    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::get('/kitchen/live', [KitchenController::class, 'liveOrders'])->name('kitchen.live');
    Route::get('/kitchen/report', [KitchenController::class, 'report'])->name('kitchen.report');
    Route::get('/kitchen/print-all', [KitchenController::class, 'printAll'])->name('kitchen.print-all');
    Route::post('/kitchen/{sale}/preparing', [KitchenController::class, 'preparing'])->name('kitchen.preparing');
    Route::post('/kitchen/{sale}/ready', [KitchenController::class, 'ready'])->name('kitchen.ready');
    Route::post('/kitchen/{sale}/served', [KitchenController::class, 'served'])->name('kitchen.served');
    Route::get('/kitchen/{sale}/print', [KitchenController::class, 'print'])->name('kitchen.print');

    // Sections
    Route::resource('sections', SectionController::class);

    // Staff Assignments
    Route::get('/assignments', [StaffAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [StaffAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [StaffAssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [StaffAssignmentController::class, 'show'])->name('assignments.show');
    Route::get('/assignments/{assignment}/edit', [StaffAssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/assignments/{assignment}', [StaffAssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [StaffAssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::patch('/assignments/{assignment}/end', [StaffAssignmentController::class, 'end'])->name('assignments.end');

    // Tables
    Route::post('/tables/add-guest', [TableController::class, 'addGuest'])->name('tables.add-guest-body');
    Route::resource('tables', TableController::class);
    Route::post('/tables/{table}/occupy', [TableController::class, 'occupy'])->name('tables.occupy');
    Route::post('/tables/{table}/add-guest', [TableController::class, 'addGuest'])->name('tables.add-guest');
    Route::put('/tables/guests/{guest}', [TableController::class, 'updateGuest'])->name('tables.update-guest');
    Route::delete('/tables/guests/{guest}', [TableController::class, 'removeGuest'])->name('tables.remove-guest');
    Route::post('/tables/guests/{guest}/delete', [TableController::class, 'removeGuest'])->name('tables.delete-guest');
    Route::post('/tables/{table}/release', [TableController::class, 'release'])->name('tables.release');
    Route::get('/tables/{table}/split-bill', [TableController::class, 'splitBill'])->name('tables.split-bill');
    Route::get('/tables/{table}/guests', [TableController::class, 'getGuests'])->name('tables.guests');

    // Room Categories
    Route::resource('room-categories', RoomCategoryController::class);

    // Rooms
    Route::resource('rooms', RoomController::class);
    Route::get('/rooms/category/{category}', [RoomController::class, 'getByCategory'])->name('rooms.by-category');
    Route::get('/rooms/{room}/availability', [RoomController::class, 'checkAvailability'])->name('rooms.availability');

    // Room Bookings
    Route::resource('room-bookings', RoomBookingController::class)->parameters([
        'room-bookings' => 'roomBooking'
    ]);
    Route::post('/room-bookings/{roomBooking}/check-in', [RoomBookingController::class, 'checkIn'])->name('room-bookings.check-in');
    Route::get('/room-bookings/{roomBooking}/checkout', [RoomBookingController::class, 'showCheckOut'])->name('room-bookings.checkout');
    Route::post('/room-bookings/{roomBooking}/check-out', [RoomBookingController::class, 'checkOut'])->name('room-bookings.check-out');
    Route::post('/room-bookings/{roomBooking}/cancel', [RoomBookingController::class, 'cancel'])->name('room-bookings.cancel');
    Route::get('/room-bookings/{roomBooking}/receipt', [RoomBookingController::class, 'receipt'])->name('room-bookings.receipt');

    // Purchase Requisitions
    Route::resource('purchase-requisitions', PurchaseRequisitionController::class)->parameters([
        'purchase-requisitions' => 'purchaseRequisition'
    ]);
    Route::post('/purchase-requisitions/{purchaseRequisition}/approve', [PurchaseRequisitionController::class, 'approve'])->name('purchase-requisitions.approve');
    Route::post('/purchase-requisitions/{purchaseRequisition}/reject', [PurchaseRequisitionController::class, 'reject'])->name('purchase-requisitions.reject');
    Route::post('/purchase-requisitions/{purchaseRequisition}/complete', [PurchaseRequisitionController::class, 'complete'])->name('purchase-requisitions.complete');

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Owner Purchases
    Route::resource('owner-purchases', OwnerPurchaseController::class)->parameters([
        'owner-purchases' => 'ownerPurchase'
    ]);

    // Printers
    Route::resource('printers', PrinterController::class);
    Route::get('/printers/troubleshoot', [PrinterController::class, 'troubleshoot'])->name('printers.troubleshoot');
    Route::post('/printers/{printerSetting}/test', [PrinterController::class, 'test'])->name('printers.test');
    Route::get('/printers/server-info', [PrinterController::class, 'serverInfo'])->name('printers.server-info');

    // Bank Settings
    Route::get('/bank-settings', [BankSettingsController::class, 'edit'])->name('bank-settings.edit');
    Route::put('/bank-settings', [BankSettingsController::class, 'update'])->name('bank-settings.update');

    // Go Live (admin only — controller enforces)
    Route::get('/go-live', [GoLiveController::class, 'index'])->name('go-live.index');
    Route::post('/go-live/clear-orders', [GoLiveController::class, 'clearOrders'])->name('go-live.clear-orders');
    Route::post('/go-live/clear-customers', [GoLiveController::class, 'clearCustomers'])->name('go-live.clear-customers');
    Route::post('/go-live/clear-all', [GoLiveController::class, 'clearAll'])->name('go-live.clear-all');

    // Cloud Footer
    Route::get('/cloud-footer', [CloudFooterController::class, 'edit'])->name('cloud-footer.edit');
    Route::put('/cloud-footer', [CloudFooterController::class, 'update'])->name('cloud-footer.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/credentials', [ProfileController::class, 'updateCredentials'])->name('profile.credentials');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Mobile Staff App (Cashier + Kitchen)
|--------------------------------------------------------------------------
*/
Route::prefix('app')->name('app.')->group(function () {
    Route::get('/manifest.json', function () {
        $user = auth()->user();
        $startUrl = '/app';
        $shortcuts = [];

        if ($user && $user->isKitchen()) {
            $startUrl = '/app/kitchen';
            $shortcuts[] = [
                'name' => 'Kitchen',
                'short_name' => 'Kitchen',
                'description' => 'Kitchen orders',
                'url' => '/app/kitchen',
                'icons' => [['src' => '/logo.jpg', 'sizes' => '96x96']],
            ];
        } elseif ($user && ($user->isCashier() || $user->canAccessPOS())) {
            $startUrl = '/app/pos';
            $shortcuts = [
                [
                    'name' => 'POS',
                    'short_name' => 'POS',
                    'description' => 'Cashier POS',
                    'url' => '/app/pos',
                    'icons' => [['src' => '/logo.jpg', 'sizes' => '96x96']],
                ],
                [
                    'name' => 'Kitchen',
                    'short_name' => 'Kitchen',
                    'description' => 'Ready orders',
                    'url' => '/app/kitchen',
                    'icons' => [['src' => '/logo.jpg', 'sizes' => '96x96']],
                ],
            ];
        }

        return response()->json([
            'name' => 'Optizee Staff',
            'short_name' => 'Optizee Staff',
            'description' => 'Cashier and Kitchen mobile app',
            'start_url' => $startUrl,
            'scope' => '/app',
            'display' => 'standalone',
            'background_color' => '#0f172a',
            'theme_color' => '#0f172a',
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => '/logo.jpg',
                    'sizes' => '192x192',
                    'type' => 'image/jpeg',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/logo.jpg',
                    'sizes' => '512x512',
                    'type' => 'image/jpeg',
                    'purpose' => 'any maskable',
                ],
            ],
            'shortcuts' => $shortcuts,
        ])->header('Content-Type', 'application/manifest+json');
    })->name('manifest');

    Route::get('/login', [\App\Http\Controllers\Mobile\MobileAppController::class, 'loginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Mobile\MobileAppController::class, 'login']);
    Route::post('/login/quick', [\App\Http\Controllers\Mobile\MobileAppController::class, 'quickLogin'])->name('login.quick');

    Route::middleware(['auth', 'mobile.staff'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Mobile\MobileAppController::class, 'home'])->name('home');
        Route::get('/pos', [\App\Http\Controllers\Mobile\MobileAppController::class, 'pos'])->name('pos');
        Route::get('/kitchen', [\App\Http\Controllers\Mobile\MobileAppController::class, 'kitchen'])->name('kitchen');
        Route::post('/logout', [\App\Http\Controllers\Mobile\MobileAppController::class, 'logout'])->name('logout');
    });
});
