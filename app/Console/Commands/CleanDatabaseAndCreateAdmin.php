<?php

namespace App\Console\Commands;

use App\Models\SystemSettings;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CleanDatabaseAndCreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean-and-admin {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean database and create a fresh super admin user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Sky Blue Consulting - Database Cleanup & Admin Creation');
        $this->newLine();

        // Confirm action unless --force flag is used
        if (! $this->option('force')) {
            $confirmed = confirm(
                label: '⚠️  This will DELETE ALL DATA and create fresh tables. Continue?',
                default: false
            );

            if (! $confirmed) {
                $this->components->warn('Operation cancelled.');

                return self::FAILURE;
            }
        }

        // Step 1: Wipe database
        $this->components->task('Wiping database', function () {
            Artisan::call('db:wipe', ['--force' => true], $this->output);

            return true;
        });

        // Step 2: Run migrations
        $this->components->task('Running migrations', function () {
            Artisan::call('migrate', ['--force' => true], $this->output);

            return true;
        });

        // Step 3: Create default system settings
        $this->components->task('Creating system settings', function () {
            SystemSettings::create([
                'company_name' => 'Sky Blue Consulting',
                'company_email' => 'info@skyblue.com',
            ]);

            return true;
        });

        // Step 4: Seed basic data
        $this->components->task('Seeding basic data', function () {
            Artisan::call('db:seed', [
                '--class' => 'UniversitySeeder',
                '--force' => true,
            ], $this->output);

            return true;
        });

        $this->newLine();

        // Step 5: Create super admin
        $this->components->info('Creating Super Admin User');
        $this->newLine();

        $name = text(
            label: 'Admin Name',
            placeholder: 'Super Admin',
            default: 'Super Admin',
            required: true
        );

        $email = text(
            label: 'Admin Email',
            placeholder: 'admin@sky.com',
            default: 'admin@sky.com',
            required: true,
            validate: fn (string $value) => match (true) {
                ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'Please enter a valid email address.',
                User::where('email', $value)->exists() => 'This email is already in use.',
                default => null
            }
        );

        $passwordInput = password(
            label: 'Admin Password',
            placeholder: 'Enter a secure password',
            required: true,
            validate: fn (string $value) => match (true) {
                strlen($value) < 8 => 'Password must be at least 8 characters.',
                default => null
            }
        );

        // Create admin user
        $this->components->task('Creating super admin', function () use ($name, $email, $passwordInput) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($passwordInput),
                'role' => 'super_admin',
                'phone_number' => '+252000000000',
            ]);

            return true;
        });

        // Step 6: Create sample agent (optional)
        $this->newLine();
        $createAgent = confirm(
            label: 'Create a sample agent account?',
            default: true
        );

        if ($createAgent) {
            $this->components->task('Creating sample agent', function () {
                User::create([
                    'name' => 'Agent Owner',
                    'email' => 'agent@sky.com',
                    'password' => Hash::make('password'),
                    'role' => 'agent_owner',
                    'phone_number' => '+252111111111',
                ]);

                return true;
            });
        }

        // Step 7: Clear caches
        $this->components->task('Clearing caches', function () {
            Artisan::call('optimize:clear', [], $this->output);

            return true;
        });

        $this->newLine(2);
        $this->components->info('✅ Database cleaned and admin created successfully!');
        $this->newLine();

        // Display credentials
        $this->components->twoColumnDetail('Admin Email', $email);
        $this->components->twoColumnDetail('Admin Panel', url('/admin'));
        if ($createAgent) {
            $this->newLine();
            $this->components->twoColumnDetail('Agent Email', 'agent@sky.com');
            $this->components->twoColumnDetail('Agent Password', 'password');
            $this->components->twoColumnDetail('Agent Panel', url('/agent'));
        }

        $this->newLine();
        $this->components->info('🎉 Ready to use!');

        return self::SUCCESS;
    }
}
