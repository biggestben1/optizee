<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HotelInventoryRoutesTest extends TestCase
{
    public function test_admin_hotel_inventory_report_route_is_registered(): void
    {
        $this->assertTrue(Route::has('admin.hotel-inventory.report'));
    }
}
