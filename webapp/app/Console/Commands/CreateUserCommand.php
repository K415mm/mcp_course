<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {email} {name} {--admin : Create as an administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user securely via CLI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $isAdmin = $this->option('admin');

        if (User::where('email', $email)->exists()) {
            $this->error("A user with the email {$email} already exists.");
            return 1;
        }

        // Generate a secure random password (12 characters)
        $password = Str::password(12, true, true, true, false);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => $isAdmin,
            'email_verified_at' => null, // Let them verify via email
        ]);

        event(new Registered($user));

        $this->info("User created successfully!");
        $this->line("----------------------------------------");
        $this->line("Email:    " . $user->email);
        $this->line("Password: " . $password);
        $this->line("Role:     " . ($isAdmin ? 'Admin' : 'Normal User'));
        $this->line("----------------------------------------");
        $this->warn("Please securely share these credentials with the user.");

        return 0;
    }
}
