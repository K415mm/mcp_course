<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:import {file : Path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk create normal users from a CSV file (Name, Email)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("The file {$filePath} does not exist.");
            return 1;
        }

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file); // Assume first row is Name, Email headers

        $createdCount = 0;
        
        $this->info("Importing Users...");
        $this->line("========================================");

        while (($data = fgetcsv($file)) !== false) {
            // Basic CSV format assumption: Name | Email
            if (count($data) < 2) continue;

            $name = trim($data[0]);
            $email = trim($data[1]);

            if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn("Skipping invalid row: {$name} | {$email}");
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $this->warn("Skipping already existing user: {$email}");
                continue;
            }

            $password = Str::password(12, true, true, true, false);

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => false, // Bulk imported users are strictly normal users
                'role' => User::ROLE_STUDENT,
                'email_verified_at' => null,
            ]);

            event(new Registered($user));

            $this->line("Created: {$name} ({$email}) -> PWD: {$password}");
            $createdCount++;
        }

        fclose($file);

        $this->line("========================================");
        $this->info("Successfully imported {$createdCount} users.");
        $this->warn("Please save these passwords, they are hashed and cannot be retrieved.");

        return 0;
    }
}
