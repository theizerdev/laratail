<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed permissions, roles, and groups (order matters)
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            GroupSeeder::class,
            PaisSeeder::class,
        ]);

        // Assign super-admin role to the first user
        $user->assignRole('super-admin');
    }
}
