<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        // Create main Food category
        $foodCategory = Category::updateOrCreate(
            ['slug' => 'food'],
            [
                'name' => 'Food',
                'description' => 'Delicious meals and dishes',
                'is_active' => true,
            ]
        );

        // Create subcategories
        $appetizers = Category::updateOrCreate(
            ['slug' => 'appetizers'],
            [
                'name' => 'Appetizers',
                'parent_id' => $foodCategory->id,
                'description' => 'Starters and small plates',
                'is_active' => true,
            ]
        );

        $mainCourses = Category::updateOrCreate(
            ['slug' => 'main-courses'],
            [
                'name' => 'Main Courses',
                'parent_id' => $foodCategory->id,
                'description' => 'Main dishes and entrees',
                'is_active' => true,
            ]
        );

        $desserts = Category::updateOrCreate(
            ['slug' => 'desserts'],
            [
                'name' => 'Desserts',
                'parent_id' => $foodCategory->id,
                'description' => 'Sweet treats and desserts',
                'is_active' => true,
            ]
        );

        $soups = Category::updateOrCreate(
            ['slug' => 'soups'],
            [
                'name' => 'Soups',
                'parent_id' => $foodCategory->id,
                'description' => 'Hot and cold soups',
                'is_active' => true,
            ]
        );

        $salads = Category::updateOrCreate(
            ['slug' => 'salads'],
            [
                'name' => 'Salads',
                'parent_id' => $foodCategory->id,
                'description' => 'Fresh salads and sides',
                'is_active' => true,
            ]
        );

        // Appetizers Products
        $appetizerProducts = [
            ['name' => 'Chicken Wings (6 pcs)', 'selling_price' => 2500, 'cost_price' => 1500, 'stock_quantity' => 50, 'preparation_time' => 15],
            ['name' => 'Spring Rolls (4 pcs)', 'selling_price' => 1800, 'cost_price' => 1000, 'stock_quantity' => 60, 'preparation_time' => 10],
            ['name' => 'Chicken Satay (4 pcs)', 'selling_price' => 2200, 'cost_price' => 1300, 'stock_quantity' => 45, 'preparation_time' => 12],
            ['name' => 'Mozzarella Sticks (6 pcs)', 'selling_price' => 2000, 'cost_price' => 1200, 'stock_quantity' => 55, 'preparation_time' => 8],
            ['name' => 'Garlic Bread (4 pcs)', 'selling_price' => 1200, 'cost_price' => 600, 'stock_quantity' => 80, 'preparation_time' => 5],
            ['name' => 'Nachos with Cheese', 'selling_price' => 1500, 'cost_price' => 800, 'stock_quantity' => 70, 'preparation_time' => 7],
            ['name' => 'Bruschetta (4 pcs)', 'selling_price' => 1800, 'cost_price' => 1000, 'stock_quantity' => 50, 'preparation_time' => 10],
            ['name' => 'Shrimp Cocktail', 'selling_price' => 3000, 'cost_price' => 1800, 'stock_quantity' => 30, 'preparation_time' => 12],
        ];

        foreach ($appetizerProducts as $index => $product) {
            $sku = 'APP-' . strtoupper(substr(str_replace([' ', '(', ')'], '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $appetizers->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Delicious appetizer',
                    'unit' => 'serving',
                    'reorder_level' => 20,
                    'is_active' => true,
                ])
            );
        }

        // Main Courses Products
        $mainCourseProducts = [
            ['name' => 'Grilled Chicken Breast', 'selling_price' => 4500, 'cost_price' => 2500, 'stock_quantity' => 40, 'preparation_time' => 20],
            ['name' => 'Beef Steak (200g)', 'selling_price' => 6500, 'cost_price' => 4000, 'stock_quantity' => 30, 'preparation_time' => 25],
            ['name' => 'Grilled Fish Fillet', 'selling_price' => 5000, 'cost_price' => 3000, 'stock_quantity' => 35, 'preparation_time' => 18],
            ['name' => 'Pasta Carbonara', 'selling_price' => 3800, 'cost_price' => 2200, 'stock_quantity' => 50, 'preparation_time' => 15],
            ['name' => 'Spaghetti Bolognese', 'selling_price' => 3500, 'cost_price' => 2000, 'stock_quantity' => 55, 'preparation_time' => 15],
            ['name' => 'Chicken Curry', 'selling_price' => 4000, 'cost_price' => 2300, 'stock_quantity' => 45, 'preparation_time' => 20],
            ['name' => 'Jollof Rice with Chicken', 'selling_price' => 3200, 'cost_price' => 1800, 'stock_quantity' => 60, 'preparation_time' => 18],
            ['name' => 'Fried Rice with Beef', 'selling_price' => 3500, 'cost_price' => 2000, 'stock_quantity' => 55, 'preparation_time' => 18],
            ['name' => 'Pizza Margherita (12")', 'selling_price' => 4500, 'cost_price' => 2500, 'stock_quantity' => 40, 'preparation_time' => 20],
            ['name' => 'Pizza Pepperoni (12")', 'selling_price' => 5000, 'cost_price' => 2800, 'stock_quantity' => 38, 'preparation_time' => 20],
            ['name' => 'Burger with Fries', 'selling_price' => 3000, 'cost_price' => 1700, 'stock_quantity' => 50, 'preparation_time' => 12],
            ['name' => 'Chicken Burger', 'selling_price' => 2800, 'cost_price' => 1500, 'stock_quantity' => 55, 'preparation_time' => 12],
            ['name' => 'BBQ Ribs (Full Rack)', 'selling_price' => 7500, 'cost_price' => 4500, 'stock_quantity' => 25, 'preparation_time' => 30],
            ['name' => 'Lamb Chops (4 pcs)', 'selling_price' => 7000, 'cost_price' => 4200, 'stock_quantity' => 28, 'preparation_time' => 25],
            ['name' => 'Seafood Platter', 'selling_price' => 8500, 'cost_price' => 5000, 'stock_quantity' => 20, 'preparation_time' => 25],
        ];

        foreach ($mainCourseProducts as $index => $product) {
            $sku = 'MAIN-' . strtoupper(substr(str_replace([' ', '(', ')', '"'], '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $mainCourses->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Delicious main course',
                    'unit' => 'serving',
                    'reorder_level' => 15,
                    'is_active' => true,
                ])
            );
        }

        // Soups Products
        $soupProducts = [
            ['name' => 'Chicken Soup', 'selling_price' => 2500, 'cost_price' => 1400, 'stock_quantity' => 60, 'preparation_time' => 15],
            ['name' => 'Tomato Soup', 'selling_price' => 2000, 'cost_price' => 1100, 'stock_quantity' => 70, 'preparation_time' => 12],
            ['name' => 'Pepper Soup', 'selling_price' => 2800, 'cost_price' => 1600, 'stock_quantity' => 50, 'preparation_time' => 18],
            ['name' => 'Egusi Soup', 'selling_price' => 3000, 'cost_price' => 1800, 'stock_quantity' => 45, 'preparation_time' => 20],
            ['name' => 'Vegetable Soup', 'selling_price' => 2200, 'cost_price' => 1200, 'stock_quantity' => 65, 'preparation_time' => 12],
            ['name' => 'Fish Soup', 'selling_price' => 3200, 'cost_price' => 1900, 'stock_quantity' => 40, 'preparation_time' => 18],
        ];

        foreach ($soupProducts as $index => $product) {
            $sku = 'SOUP-' . strtoupper(substr(str_replace(' ', '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $soups->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Hot and delicious soup',
                    'unit' => 'bowl',
                    'reorder_level' => 20,
                    'is_active' => true,
                ])
            );
        }

        // Salads Products
        $saladProducts = [
            ['name' => 'Caesar Salad', 'selling_price' => 2800, 'cost_price' => 1600, 'stock_quantity' => 50, 'preparation_time' => 10],
            ['name' => 'Greek Salad', 'selling_price' => 3000, 'cost_price' => 1800, 'stock_quantity' => 45, 'preparation_time' => 10],
            ['name' => 'Garden Salad', 'selling_price' => 2500, 'cost_price' => 1400, 'stock_quantity' => 60, 'preparation_time' => 8],
            ['name' => 'Chicken Salad', 'selling_price' => 3500, 'cost_price' => 2000, 'stock_quantity' => 40, 'preparation_time' => 12],
            ['name' => 'Coleslaw', 'selling_price' => 1500, 'cost_price' => 800, 'stock_quantity' => 70, 'preparation_time' => 5],
            ['name' => 'Fruit Salad', 'selling_price' => 2000, 'cost_price' => 1100, 'stock_quantity' => 55, 'preparation_time' => 8],
        ];

        foreach ($saladProducts as $index => $product) {
            $sku = 'SALAD-' . strtoupper(substr(str_replace(' ', '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $salads->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Fresh and healthy salad',
                    'unit' => 'serving',
                    'reorder_level' => 20,
                    'is_active' => true,
                ])
            );
        }

        // Desserts Products
        $dessertProducts = [
            ['name' => 'Chocolate Cake (Slice)', 'selling_price' => 2000, 'cost_price' => 1100, 'stock_quantity' => 50, 'preparation_time' => 5],
            ['name' => 'Ice Cream (2 Scoops)', 'selling_price' => 1500, 'cost_price' => 800, 'stock_quantity' => 80, 'preparation_time' => 3],
            ['name' => 'Cheesecake (Slice)', 'selling_price' => 2500, 'cost_price' => 1400, 'stock_quantity' => 45, 'preparation_time' => 5],
            ['name' => 'Apple Pie (Slice)', 'selling_price' => 1800, 'cost_price' => 1000, 'stock_quantity' => 55, 'preparation_time' => 5],
            ['name' => 'Tiramisu', 'selling_price' => 3000, 'cost_price' => 1800, 'stock_quantity' => 35, 'preparation_time' => 5],
            ['name' => 'Chocolate Brownie', 'selling_price' => 1500, 'cost_price' => 800, 'stock_quantity' => 70, 'preparation_time' => 3],
            ['name' => 'Fruit Tart', 'selling_price' => 2200, 'cost_price' => 1200, 'stock_quantity' => 40, 'preparation_time' => 5],
            ['name' => 'Panna Cotta', 'selling_price' => 2500, 'cost_price' => 1400, 'stock_quantity' => 38, 'preparation_time' => 5],
        ];

        foreach ($dessertProducts as $index => $product) {
            $sku = 'DESSERT-' . strtoupper(substr(str_replace([' ', '(', ')'], '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $desserts->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Sweet dessert',
                    'unit' => 'serving',
                    'reorder_level' => 20,
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('Food categories with products seeded successfully!');
        $this->command->info('Created: ' . $appetizers->products()->count() . ' appetizers, ' . 
                           $mainCourses->products()->count() . ' main courses, ' . 
                           $soups->products()->count() . ' soups, ' .
                           $salads->products()->count() . ' salads, ' .
                           $desserts->products()->count() . ' desserts');
    }
}

