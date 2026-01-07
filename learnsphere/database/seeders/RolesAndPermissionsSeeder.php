<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        // create or update a superuser and assign admin role
        if (app()->environment('production')) {
            $this->command->warn('Skipping superuser creation in production environment.');
        } else {
            $password = Str::random(12);
            $user = User::updateOrCreate(
                ['email' => 'admin@learnsphere.test'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make($password),
                    'is_approved' => true,
                ]
            );

            $user->assignRole($adminRole);

            $this->command->info("Superuser created/updated: email: admin@learnsphere.test | password: {$password}");
        }
    }
}
