<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DrinksSeeder extends Seeder
{
    public function run(): void
    {
        // Create main Drinks category
        $drinksCategory = Category::updateOrCreate(
            ['slug' => 'drinks'],
            [
                'name' => 'Drinks',
                'description' => 'Alcoholic and non-alcoholic beverages',
                'is_active' => true,
            ]
        );

        // Create subcategories
        $wines = Category::updateOrCreate(
            ['slug' => 'wines'],
            [
                'name' => 'Wines',
                'parent_id' => $drinksCategory->id,
                'description' => 'Red, white, and sparkling wines',
                'is_active' => true,
            ]
        );

        $beer = Category::updateOrCreate(
            ['slug' => 'beer'],
            [
                'name' => 'Beer',
                'parent_id' => $drinksCategory->id,
                'description' => 'Local and imported beers',
                'is_active' => true,
            ]
        );

        $spirits = Category::updateOrCreate(
            ['slug' => 'spirits'],
            [
                'name' => 'Spirits',
                'parent_id' => $drinksCategory->id,
                'description' => 'Whiskey, gin, vodka, and other spirits',
                'is_active' => true,
            ]
        );

        $softDrinks = Category::updateOrCreate(
            ['slug' => 'soft-drinks'],
            [
                'name' => 'Soft Drinks',
                'parent_id' => $drinksCategory->id,
                'description' => 'Non-alcoholic beverages',
                'is_active' => true,
            ]
        );

        // Wines Products
        $wineProducts = [
            ['name' => 'Four Cousins Red Wine', 'selling_price' => 3500, 'cost_price' => 2500, 'stock_quantity' => 50],
            ['name' => 'Four Cousins White Wine', 'selling_price' => 3500, 'cost_price' => 2500, 'stock_quantity' => 45],
            ['name' => 'Four Cousins Rosé Wine', 'selling_price' => 3500, 'cost_price' => 2500, 'stock_quantity' => 30],
            ['name' => 'Robertson Winery Red', 'selling_price' => 4200, 'cost_price' => 3000, 'stock_quantity' => 40],
            ['name' => 'Robertson Winery White', 'selling_price' => 4200, 'cost_price' => 3000, 'stock_quantity' => 35],
            ['name' => 'Nederburg Red Wine', 'selling_price' => 5500, 'cost_price' => 4000, 'stock_quantity' => 25],
            ['name' => 'Nederburg White Wine', 'selling_price' => 5500, 'cost_price' => 4000, 'stock_quantity' => 20],
            ['name' => 'Barefoot Moscato', 'selling_price' => 4800, 'cost_price' => 3500, 'stock_quantity' => 30],
            ['name' => 'Yellow Tail Shiraz', 'selling_price' => 4500, 'cost_price' => 3200, 'stock_quantity' => 28],
            ['name' => 'Jacob\'s Creek Chardonnay', 'selling_price' => 5200, 'cost_price' => 3800, 'stock_quantity' => 22],
        ];

        foreach ($wineProducts as $index => $product) {
            $sku = 'WINE-' . strtoupper(substr(str_replace([' ', "'"], '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $wines->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Premium wine',
                    'is_active' => true,
                ])
            );
        }

        // Beer Products (Nigerian and International)
        $beerProducts = [
            ['name' => 'Star Lager Beer', 'selling_price' => 300, 'cost_price' => 200, 'stock_quantity' => 200],
            ['name' => 'Gulder Lager Beer', 'selling_price' => 300, 'cost_price' => 200, 'stock_quantity' => 180],
            ['name' => 'Heineken Beer', 'selling_price' => 500, 'cost_price' => 350, 'stock_quantity' => 150],
            ['name' => 'Trophy Lager Beer', 'selling_price' => 280, 'cost_price' => 180, 'stock_quantity' => 170],
            ['name' => '33 Export Lager', 'selling_price' => 300, 'cost_price' => 200, 'stock_quantity' => 160],
            ['name' => 'Castle Lite', 'selling_price' => 350, 'cost_price' => 250, 'stock_quantity' => 140],
            ['name' => 'Budweiser', 'selling_price' => 450, 'cost_price' => 320, 'stock_quantity' => 120],
            ['name' => 'Stella Artois', 'selling_price' => 500, 'cost_price' => 350, 'stock_quantity' => 100],
            ['name' => 'Corona Extra', 'selling_price' => 550, 'cost_price' => 400, 'stock_quantity' => 90],
            ['name' => 'Desperados', 'selling_price' => 600, 'cost_price' => 450, 'stock_quantity' => 80],
        ];

        foreach ($beerProducts as $index => $product) {
            $sku = 'BEER-' . strtoupper(substr(str_replace(' ', '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $beer->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Premium beer',
                    'is_active' => true,
                ])
            );
        }

        // Spirits Products
        $spiritProducts = [
            ['name' => 'Hennessy VS', 'selling_price' => 25000, 'cost_price' => 18000, 'stock_quantity' => 15],
            ['name' => 'Hennessy VSOP', 'selling_price' => 35000, 'cost_price' => 25000, 'stock_quantity' => 10],
            ['name' => 'Johnnie Walker Red Label', 'selling_price' => 8000, 'cost_price' => 6000, 'stock_quantity' => 25],
            ['name' => 'Johnnie Walker Black Label', 'selling_price' => 15000, 'cost_price' => 11000, 'stock_quantity' => 18],
            ['name' => 'Jameson Irish Whiskey', 'selling_price' => 12000, 'cost_price' => 9000, 'stock_quantity' => 20],
            ['name' => 'Jack Daniel\'s', 'selling_price' => 18000, 'cost_price' => 13000, 'stock_quantity' => 15],
            ['name' => 'Bombay Sapphire Gin', 'selling_price' => 14000, 'cost_price' => 10000, 'stock_quantity' => 12],
            ['name' => 'Tanqueray Gin', 'selling_price' => 13000, 'cost_price' => 9500, 'stock_quantity' => 14],
            ['name' => 'Smirnoff Vodka', 'selling_price' => 7000, 'cost_price' => 5000, 'stock_quantity' => 30],
            ['name' => 'Absolut Vodka', 'selling_price' => 9000, 'cost_price' => 6500, 'stock_quantity' => 25],
            ['name' => 'Baileys Irish Cream', 'selling_price' => 11000, 'cost_price' => 8000, 'stock_quantity' => 20],
            ['name' => 'Ciroc Vodka', 'selling_price' => 20000, 'cost_price' => 15000, 'stock_quantity' => 10],
        ];

        foreach ($spiritProducts as $index => $product) {
            $sku = 'SPIRIT-' . strtoupper(substr(str_replace([' ', "'"], '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $spirits->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Premium spirit',
                    'is_active' => true,
                ])
            );
        }

        // Soft Drinks Products
        $softDrinkProducts = [
            ['name' => 'Coca Cola (500ml)', 'selling_price' => 200, 'cost_price' => 120, 'stock_quantity' => 300],
            ['name' => 'Pepsi (500ml)', 'selling_price' => 200, 'cost_price' => 120, 'stock_quantity' => 280],
            ['name' => 'Fanta Orange (500ml)', 'selling_price' => 200, 'cost_price' => 120, 'stock_quantity' => 250],
            ['name' => 'Sprite (500ml)', 'selling_price' => 200, 'cost_price' => 120, 'stock_quantity' => 240],
            ['name' => '7UP (500ml)', 'selling_price' => 200, 'cost_price' => 120, 'stock_quantity' => 220],
            ['name' => 'Maltina', 'selling_price' => 250, 'cost_price' => 150, 'stock_quantity' => 200],
            ['name' => 'Amstel Malta', 'selling_price' => 250, 'cost_price' => 150, 'stock_quantity' => 190],
            ['name' => 'Vitamalt', 'selling_price' => 250, 'cost_price' => 150, 'stock_quantity' => 180],
            ['name' => 'Red Bull', 'selling_price' => 500, 'cost_price' => 350, 'stock_quantity' => 100],
            ['name' => 'Monster Energy', 'selling_price' => 600, 'cost_price' => 400, 'stock_quantity' => 80],
            ['name' => 'Lucozade', 'selling_price' => 300, 'cost_price' => 200, 'stock_quantity' => 150],
            ['name' => 'Ribena', 'selling_price' => 400, 'cost_price' => 280, 'stock_quantity' => 120],
        ];

        foreach ($softDrinkProducts as $index => $product) {
            $sku = 'SOFT-' . strtoupper(substr(str_replace([' ', '(', ')'], '', $product['name']), 0, 6)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            Product::updateOrCreate(
                [
                    'name' => $product['name'],
                    'category_id' => $softDrinks->id,
                ],
                array_merge($product, [
                    'sku' => $sku,
                    'description' => 'Refreshing soft drink',
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('Drinks and wine categories with products seeded successfully!');
        $this->command->info('Created: ' . $wines->products()->count() . ' wines, ' . 
                           $beer->products()->count() . ' beers, ' . 
                           $spirits->products()->count() . ' spirits, ' . 
                           $softDrinks->products()->count() . ' soft drinks');
    }
}

