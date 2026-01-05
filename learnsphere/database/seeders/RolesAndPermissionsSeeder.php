<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::firstOrCreate(['name' => 'manage courses']);

        // create roles and assign existing permissions
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);
        $instructorRole->givePermissionTo('manage courses');

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
    }
}
