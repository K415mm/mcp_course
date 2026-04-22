<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserInjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:inject 
                            {--count=5 : Number of random users to generate} 
                            {--file= : Optional path to a CSV or TXT file with emails/names}
                            {--password= : Optional default password for the created users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inject test users with student role (no verification needed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->option('file');
        $count = (int) $this->option('count');
        $defaultPassword = $this->option('password') ?? 'password123'; // Default password

        $this->info("Starting User Injection...");

        if ($file) {
            $this->injectFromFile($file, $defaultPassword);
        } else {
            $this->injectRandomUsers($count, $defaultPassword);
        }

        $this->info("Injection complete.");
        return 0;
    }

    protected function injectRandomUsers(int $count, string $password)
    {
        $this->info("Generating {$count} random student users...");

        for ($i = 0; $i < $count; $i++) {
            $name = "Test Student " . Str::random(4);
            $email = "student" . Str::random(6) . "@example.com";

            $this->createUser($name, $email, $password);
        }
    }

    protected function injectFromFile(string $filePath, string $password)
    {
        if (!file_exists($filePath)) {
            $this->error("The file {$filePath} does not exist.");
            return;
        }

        $this->info("Reading users from {$filePath}...");
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $isCsv = str_ends_with(strtolower($filePath), '.csv');

        foreach ($lines as $index => $line) {
            // Skip CSV header if it looks like one
            if ($isCsv && $index === 0 && stripos($line, 'email') !== false) {
                continue;
            }

            $name = '';
            $email = '';

            if ($isCsv) {
                $data = str_getcsv($line);
                if (count($data) >= 2) {
                    $name = trim($data[0]);
                    $email = trim($data[1]);
                } else {
                    $email = trim($data[0]);
                    $name = explode('@', $email)[0] ?? 'User';
                }
            } else {
                // TXT file - assume one email per line, or "Name, Email"
                if (str_contains($line, ',')) {
                    $parts = explode(',', $line);
                    $name = trim($parts[0]);
                    $email = trim($parts[1]);
                } else {
                    $email = trim($line);
                    $name = explode('@', $email)[0] ?? 'User';
                }
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn("Skipping invalid email: {$email}");
                continue;
            }

            $this->createUser($name, $email, $password);
        }
    }

    protected function createUser(string $name, string $email, string $password)
    {
        if (User::where('email', $email)->exists()) {
            $this->warn("User already exists: {$email}");
            return;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => false,
            'role' => User::ROLE_STUDENT,
            'email_verified_at' => now(), // Skip verification by setting timestamp
        ]);

        $this->line("Created: {$user->name} ({$user->email}) -> PWD: {$password}");
    }
}
