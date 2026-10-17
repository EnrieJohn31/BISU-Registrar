<?php

namespace App\Console\Commands;

use App\Actions\Accounts\ProvisionStaffUser;
use App\Enums\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateFirstAdmin extends Command
{
    /**
     * @var string
     */
    protected $signature = 'bisu:create-admin
        {--name= : Full name of the administrator}
        {--email= : Email address used to sign in}
        {--force : Create another administrator even if an active one exists}';

    /**
     * @var string
     */
    protected $description = 'Create the first system administrator with a temporary password (run by MIS on the server)';

    public function handle(ProvisionStaffUser $provision): int
    {
        $activeAdministratorExists = UserRole::query()
            ->where('role', Role::SystemAdmin->value)
            ->whereNull('revoked_at')
            ->whereHas('user', fn ($query) => $query->whereNull('deactivated_at'))
            ->exists();

        if ($activeAdministratorExists && ! $this->option('force')) {
            $this->error('An active system administrator already exists. Create further accounts in the admin screens, or pass --force.');

            return self::FAILURE;
        }

        $input = [
            'name' => $this->option('name') ?: $this->ask('Full name'),
            'email' => $this->option('email') ?: $this->ask('Email address'),
        ];

        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $result = $provision->handle(
            null,
            (string) $input['name'],
            (string) $input['email'],
            null,
            [Role::SystemAdmin],
            'Initial system administrator created on the server with bisu:create-admin',
        );

        $this->info("System administrator created: {$result['user']->email}");
        $this->line("Temporary password (shown once): {$result['temporaryPassword']}");
        $this->line('Expires: '.$result['expiresAt']->timezone('Asia/Manila')->toDayDateTimeString().' (Asia/Manila)');
        $this->warn('Hand it over in person. A new password and two-factor setup are required at first sign-in.');

        return self::SUCCESS;
    }
}
