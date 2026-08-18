<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "\n========================================\n";
echo "  Create Login Code for Cashier\n";
echo "========================================\n\n";

// Get cashier role
$cashierRole = Role::where('name', 'cashier')->first();

if (!$cashierRole) {
    echo "ERROR: Cashier role not found!\n";
    echo "Please run: php artisan db:seed --class=RoleSeeder\n\n";
    exit(1);
}

// Get all cashier users
$cashiers = User::where('role_id', $cashierRole->id)->get();

if ($cashiers->isEmpty()) {
    echo "No cashier accounts found.\n";
    echo "You need to create a cashier user first.\n\n";
    exit(1);
}

// Display cashiers
echo "Available Cashier Accounts:\n";
echo "----------------------------------------\n";
foreach ($cashiers as $index => $cashier) {
    $code = $cashier->login_code ?? 'NONE';
    $status = $cashier->is_active ? 'Active' : 'Inactive';
    echo ($index + 1) . ". ID: {$cashier->id} | Name: {$cashier->name} | Email: {$cashier->email}\n";
    echo "   Current Code: {$code} | Status: {$status}\n\n";
}

// Get command line arguments
$cashierId = $argv[1] ?? null;
$customCode = $argv[2] ?? null;

if ($cashierId === null) {
    // Interactive mode
    echo "Enter the ID of the cashier to create/update login code (or 'all' for all cashiers): ";
    $input = trim(fgets(STDIN));
    
    if (strtolower($input) === 'all') {
        $cashierId = 'all';
    } else {
        $cashierId = (int)$input;
    }
} else {
    if (strtolower($cashierId) === 'all') {
        // Keep as 'all'
    } else {
        $cashierId = (int)$cashierId;
    }
}

if ($cashierId === 'all') {
    // Generate codes for all cashiers
    echo "\nGenerating login codes for all cashiers...\n";
    foreach ($cashiers as $cashier) {
        $code = generateUniqueCode();
        $cashier->login_code = $code;
        $cashier->save();
        echo "✓ {$cashier->name} ({$cashier->email}): Code = {$code}\n";
    }
    echo "\n✅ All cashier login codes created!\n\n";
} else {
    $cashier = $cashiers->firstWhere('id', $cashierId);
    
    if (!$cashier) {
        echo "\nERROR: Cashier with ID {$cashierId} not found!\n\n";
        exit(1);
    }
    
    // Use custom code if provided, otherwise ask or generate
    if ($customCode === null) {
        echo "\nEnter a 4-digit code (or press Enter to generate automatically): ";
        $customCode = trim(fgets(STDIN));
    }
    
    if (empty($customCode)) {
        // Generate unique code
        $code = generateUniqueCode();
    } else {
        // Validate custom code
        if (!preg_match('/^[0-9]{4}$/', $customCode)) {
            echo "\nERROR: Code must be exactly 4 digits (0-9)!\n\n";
            exit(1);
        }
        
        // Check if code already exists
        $existing = User::where('login_code', $customCode)
            ->where('id', '!=', $cashier->id)
            ->first();
        
        if ($existing) {
            echo "\nERROR: Code {$customCode} is already assigned to: {$existing->name} ({$existing->email})\n\n";
            exit(1);
        }
        
        $code = $customCode;
    }
    
    // Update cashier
    $cashier->login_code = $code;
    $cashier->save();
    
    echo "\n✅ Login code created successfully!\n";
    echo "----------------------------------------\n";
    echo "Cashier: {$cashier->name}\n";
    echo "Email: {$cashier->email}\n";
    echo "Login Code: {$code}\n";
    echo "\nThey can now login using:\n";
    echo "  - Quick Login: Enter code '{$code}' (no password needed)\n";
    echo "  - Regular Login: Email/Name + Password\n\n";
}

function generateUniqueCode() {
    $maxAttempts = 100;
    $attempt = 0;
    
    while ($attempt < $maxAttempts) {
        $code = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        $exists = User::where('login_code', $code)->exists();
        if (!$exists) {
            return $code;
        }
        
        $attempt++;
    }
    
    // If we can't find a unique code, try sequential
    for ($i = 1000; $i <= 9999; $i++) {
        $code = str_pad($i, 4, '0', STR_PAD_LEFT);
        $exists = User::where('login_code', $code)->exists();
        if (!$exists) {
            return $code;
        }
    }
    
    throw new Exception("Could not generate unique login code!");
}
