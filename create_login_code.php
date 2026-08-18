<?php

/**
 * Quick script to create/update login code for a user
 * Usage: php create_login_code.php [user_id] [code]
 * Example: php create_login_code.php 3 1234
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

$userId = $argv[1] ?? null;
$customCode = $argv[2] ?? null;

if (!$userId) {
    echo "\nUsage: php create_login_code.php [user_id] [code]\n";
    echo "Example: php create_login_code.php 3 1234\n";
    echo "         php create_login_code.php 3 (auto-generate)\n\n";
    
    // Show all users with roles
    echo "Available Users:\n";
    echo "----------------------------------------\n";
    $users = User::with('role')->get();
    foreach ($users as $user) {
        $role = $user->role ? $user->role->name : ($user->is_admin ? 'Admin' : 'No Role');
        $code = $user->login_code ?? 'NONE';
        echo "ID: {$user->id} | {$user->name} | {$user->email} | Role: {$role} | Code: {$code}\n";
    }
    echo "\n";
    exit(1);
}

$user = User::find($userId);

if (!$user) {
    echo "\nERROR: User with ID {$userId} not found!\n\n";
    exit(1);
}

// Check if user has a role (required for login)
if (!$user->is_admin && !$user->role) {
    echo "\nWARNING: User '{$user->name}' has no role assigned.\n";
    echo "They may not be able to login. Assign a role first.\n\n";
}

// Generate or use custom code
if ($customCode) {
    // Validate custom code
    if (!preg_match('/^[0-9]{4}$/', $customCode)) {
        echo "\nERROR: Code must be exactly 4 digits (0-9)!\n\n";
        exit(1);
    }
    
    // Check if code already exists
    $existing = User::where('login_code', $customCode)
        ->where('id', '!=', $user->id)
        ->first();
    
    if ($existing) {
        echo "\nERROR: Code {$customCode} is already assigned to: {$existing->name} ({$existing->email})\n\n";
        exit(1);
    }
    
    $code = $customCode;
} else {
    // Generate unique code
    $code = generateUniqueCode();
}

// Update user
$user->login_code = $code;
$user->save();

echo "\n✅ Login code created successfully!\n";
echo "----------------------------------------\n";
echo "User: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Role: " . ($user->role ? $user->role->name : ($user->is_admin ? 'Admin' : 'None')) . "\n";
echo "Login Code: {$code}\n";
echo "\nThey can now login using:\n";
echo "  - Quick Login: Enter code '{$code}' (no password needed)\n";
echo "  - Regular Login: Email/Name + Password\n\n";

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





