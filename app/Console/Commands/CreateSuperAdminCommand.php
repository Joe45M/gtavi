<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class CreateSuperAdminCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'gta:make-super-admin
                            {--name=Global Super Admin : Display name for the super admin account}
                            {--email= : Email address for the super admin account}
                            {--password= : Password for the super admin account (auto-generated if omitted in non-interactive mode)}';

    /**
     * @var list<string>
     */
    protected $aliases = ['make:super-admin', 'admin:create'];

    /**
     * @var string
     */
    protected $description = 'Create or promote a Global Super Admin user with unrestricted roles and permissions.';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?? ''));
        if ($name === '') {
            $name = 'Global Super Admin';
        }

        $emailOption = trim((string) ($this->option('email') ?? ''));
        if ($emailOption === '' && $this->input->isInteractive()) {
            $emailOption = trim((string) $this->ask('Super Admin email address', 'superadmin@gtavi.pro'));
        }

        if ($emailOption === '' || filter_var($emailOption, FILTER_VALIDATE_EMAIL) === false) {
            $this->error('A valid --email address is required (e.g. --email=admin@gtavi.pro).');

            return self::INVALID;
        }

        $email = mb_strtolower($emailOption);
        $passwordOption = (string) ($this->option('password') ?? '');
        $wasGenerated = false;

        if ($passwordOption === '' && $this->input->isInteractive()) {
            $passwordOption = (string) ($this->secret('Password (leave blank to auto-generate a secure 20-char password)') ?? '');
        }

        if (trim($passwordOption) === '') {
            $passwordOption = Str::password(20);
            $wasGenerated = true;
        }

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

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($passwordOption),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['super-admin', 'admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info("Global Super Admin [{$user->email}] is ready with unrestricted access.");

        $headers = ['ID', 'Name', 'Email', 'Roles'];
        $row = [
            (string) $user->id,
            $user->name,
            $user->email,
            $user->getRoleNames()->implode(', '),
        ];

        if ($wasGenerated) {
            $headers[] = 'Generated Password';
            $row[] = $passwordOption;
        }

        $this->table($headers, [$row]);

        return self::SUCCESS;
    }
}
