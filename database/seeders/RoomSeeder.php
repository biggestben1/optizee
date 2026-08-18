<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Standard Rooms
        $standardCategory = RoomCategory::updateOrCreate(
            ['slug' => 'standard-rooms'],
            [
                'name' => 'Standard Rooms',
                'description' => 'Comfortable standard rooms',
                'price_per_room' => 35000,
                'total_rooms' => 7,
                'is_suite' => false,
                'is_active' => true,
            ]
        );

        // Create 7 Standard Rooms
        for ($i = 1; $i <= 7; $i++) {
            Room::updateOrCreate(
                ['room_number' => 'STD-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                [
                    'room_category_id' => $standardCategory->id,
                    'room_name' => 'Standard Room ' . $i,
                    'price_per_night' => 35000,
                    'status' => 'available',
                    'is_suite_sub_room' => false,
                    'is_active' => true,
                ]
            );
        }

        // Semi-Executive Rooms
        $semiExecCategory = RoomCategory::updateOrCreate(
            ['slug' => 'semi-executive-rooms'],
            [
                'name' => 'Semi-Executive Rooms',
                'description' => 'Semi-executive rooms with enhanced amenities',
                'price_per_room' => 40000,
                'total_rooms' => 4,
                'is_suite' => false,
                'is_active' => true,
            ]
        );

        // Create 4 Semi-Executive Rooms
        for ($i = 1; $i <= 4; $i++) {
            Room::updateOrCreate(
                ['room_number' => 'SEM-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                [
                    'room_category_id' => $semiExecCategory->id,
                    'room_name' => 'Semi-Executive Room ' . $i,
                    'price_per_night' => 40000,
                    'status' => 'available',
                    'is_suite_sub_room' => false,
                    'is_active' => true,
                ]
            );
        }

        // Executive Rooms
        $execCategory = RoomCategory::updateOrCreate(
            ['slug' => 'executive-rooms'],
            [
                'name' => 'Executive Rooms',
                'description' => 'Premium executive rooms',
                'price_per_room' => 50000,
                'total_rooms' => 5,
                'is_suite' => false,
                'is_active' => true,
            ]
        );

        // Create 5 Executive Rooms
        for ($i = 1; $i <= 5; $i++) {
            Room::updateOrCreate(
                ['room_number' => 'EXE-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                [
                    'room_category_id' => $execCategory->id,
                    'room_name' => 'Executive Room ' . $i,
                    'price_per_night' => 50000,
                    'status' => 'available',
                    'is_suite_sub_room' => false,
                    'is_active' => true,
                ]
            );
        }

        // Suite Category
        $suiteCategory = RoomCategory::updateOrCreate(
            ['slug' => 'suite'],
            [
                'name' => 'Suite',
                'description' => 'Luxury suite with multiple rooms',
                'price_per_room' => null, // Not applicable for suite
                'full_suite_price' => 250000,
                'total_rooms' => 1,
                'is_suite' => true,
                'is_active' => true,
            ]
        );

        // Create Suite (parent room)
        $suiteRoom = Room::updateOrCreate(
            ['room_number' => 'SUITE-1'],
            [
                'room_category_id' => $suiteCategory->id,
                'room_name' => 'Suite',
                'price_per_night' => 250000,
                'status' => 'available',
                'is_suite_sub_room' => false,
                'is_active' => true,
            ]
        );

        // Create Suite Sub-Rooms
        $suiteSubRooms = [
            ['number' => 'SUITE-1-MB1', 'name' => 'Master Bed 1', 'price' => 50000],
            ['number' => 'SUITE-1-MB2', 'name' => 'Master Bed 2', 'price' => 50000],
            ['number' => 'SUITE-1-DLX', 'name' => 'Deluxe', 'price' => 45000],
            ['number' => 'SUITE-1-SUP', 'name' => 'Superior', 'price' => 45000],
            ['number' => 'SUITE-1-CLS', 'name' => 'Classic', 'price' => 45000],
        ];

        foreach ($suiteSubRooms as $subRoom) {
            Room::updateOrCreate(
                ['room_number' => $subRoom['number']],
                [
                    'room_category_id' => $suiteCategory->id,
                    'room_name' => $subRoom['name'],
                    'price_per_night' => $subRoom['price'],
                    'status' => 'available',
                    'is_suite_sub_room' => true,
                    'parent_suite_id' => $suiteRoom->id,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Room categories and rooms seeded successfully!');
        $this->command->info('Standard Rooms: 7');
        $this->command->info('Semi-Executive Rooms: 4');
        $this->command->info('Executive Rooms: 5');
        $this->command->info('Suite: 1 (with 5 sub-rooms)');
    }
}
