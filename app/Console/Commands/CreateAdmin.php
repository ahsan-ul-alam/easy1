<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('admin:create {--name= : The admin\'s display name} {--email= : The login email address} {--password= : The password (you will be asked if omitted)}')]
#[Description('Create an admin account, or reset the password of an existing one')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Email address');
        $existing = User::query()->where('email', $email)->first();
        $name = $this->option('name') ?: ($existing?->name ?? $this->ask('Name', 'Admin'));
        $password = $this->option('password') ?: $this->secret('Password (min. 8 characters)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', Password::min(8)],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = $existing ?? new User;
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_admin' => true,
        ])->save();

        $this->info($existing
            ? "Admin {$email} updated. You can now log in at ".route('admin.login')
            : "Admin {$email} created. You can now log in at ".route('admin.login'));

        return self::SUCCESS;
    }
}
