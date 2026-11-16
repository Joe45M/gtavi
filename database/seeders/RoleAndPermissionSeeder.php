<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'access studio',
            'create posts',
            'edit posts',
            'publish posts',
            'delete posts',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdminRole->syncPermissions($permissions);

        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->syncPermissions($permissions);

        $editorRole = Role::findOrCreate('editor', 'web');
        $editorRole->syncPermissions([
            'access studio',
            'create posts',
            'edit posts',
            'publish posts',
        ]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@gtavi.pro'],
            [
                'name' => 'Vice Editorial Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $editor = User::query()->updateOrCreate(
            ['email' => 'editor@gtavi.pro'],
            [
                'name' => 'Vice Telemetry Editor',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $editor->syncRoles(['editor']);
    }
}
