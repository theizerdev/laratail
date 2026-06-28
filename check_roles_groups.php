<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Group;
use Spatie\Permission\Models\Role;

$groups = Group::all();
echo "GROUPS:\n";
foreach ($groups as $g) {
    echo "ID: {$g->id}, Name: {$g->name}, Is Active: " . ($g->is_active ? 'Yes' : 'No') . "\n";
}

$roles = Role::all();
echo "\nROLES:\n";
foreach ($roles as $r) {
    echo "ID: {$r->id}, Name: {$r->name}, Guard: {$r->guard_name}\n";
}
