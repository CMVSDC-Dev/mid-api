<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'pass123', //$2y$10$mCRdG8sKp42J5aLTWGkJuun22sakissmskBRKkPjMLvQDMC9bpU3q
            'jwt_secret' => Str::random(32),
            'is_active' => true,
        ]);

        $role = \Spatie\Permission\Models\Role::create(['name' => 'Super Admin']);
        $role2 = \Spatie\Permission\Models\Role::create(['name' => 'Admin']);
        $permission1 = \Spatie\Permission\Models\Permission::create(['name' => 'add user']);
        $permission2 = \Spatie\Permission\Models\Permission::create(['name' => 'edit user']);
        $permission3 = \Spatie\Permission\Models\Permission::create(['name' => 'delete user']);

        $role->syncPermissions([$permission1, $permission2, $permission3]);
        $role2->syncPermissions([$permission1, $permission2, $permission3]);
        User::find(1)->assignRole('Super Admin');

        $this->call(MonitoringConfigSeeder::class);
    }
}
