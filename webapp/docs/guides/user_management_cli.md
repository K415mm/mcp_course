# Manual User Management via PHP CLI (Artisan Tinker)

Laravel provides a powerful interactive REPL called **Tinker** that allows you to interact with the application's database and models directly from the command line.

This is extremely useful when the web interface isn't accessible, when email invitations fail, or when you need to perform bulk updates quickly.

## 1. Accessing Tinker

To enter the Tinker environment, run the following command from the root of the Laravel project (`s:/tunai/webapp` or inside your docker container):

```bash
# If running directly on the server/locally
php artisan tinker

# If using Docker (Docker Compose)
docker compose exec app php artisan tinker
```

Once inside, you will see a prompt like `>`. You can now write standard PHP code using Laravel's Eloquent ORM.

## 2. Finding Users

**Find a user by email:**
```php
$user = User::where('email', 'student@example.com')->first();
```

**Find a user by ID:**
```php
$user = User::find(1);
```

**List all users with a specific role:**
```php
$admins = User::where('role', 'admin')->get();
$admins->pluck('email'); // Just to see their emails
```

## 3. Creating a User Manually

If the invitation system is broken, you can manually create a user:

```php
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'password' => Hash::make('SecretPassword123!'),
    'role' => User::ROLE_STUDENT, // or 'admin', 'mentor', 'guest'
    'is_admin' => false,
    'email_verified_at' => now(), // Skips the email verification requirement
]);
```

## 4. Updating a User's Role or Password

If a user forgot their password or needs admin rights:

```php
$user = User::where('email', 'john.doe@example.com')->first();

// Change role to Admin
$user->role = User::ROLE_ADMIN;
$user->is_admin = true;
$user->save();

// Reset password manually
$user->password = Hash::make('NewPassword123!');
$user->save();

// Force verify their email (if they are stuck)
$user->email_verified_at = now();
$user->save();
```

## 5. Deleting or Banning a User

**Delete a user completely:**
```php
$user = User::where('email', 'bad.user@example.com')->first();
$user->delete();
```

**Ban a user (prevents login):**
```php
$user = User::where('email', 'rule.breaker@example.com')->first();
$user->banned_at = now();
$user->save();
```

**Unban a user:**
```php
$user->banned_at = null;
$user->save();
```

## 6. Exiting Tinker

To leave the Tinker console, simply type:
```php
exit
```
or press `Ctrl + C`.

---

## 7. Automated Script (Alternative to Tinker)

If you need to inject test users or import an email list from a file quickly without opening Tinker, we have created an Artisan command:

**Generate 5 random test students:**
```bash
php artisan user:inject --count=5
```

**Import from a CSV/TXT file:**
```bash
php artisan user:inject --file=emails.txt --password=MySecretPassword
```

*(The file can contain just emails, one per line, or CSV format `Name,Email`)*
