<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Deliberately NOT called from DatabaseSeeder's automatic chain — run this
     * explicitly (php artisan db:seed --class=AdminUserSeeder) so a later
     * re-seed of content never silently resets a since-changed password.
     *
     * Reads ADMIN_SEED_EMAIL/ADMIN_SEED_PASSWORD for the first account, and
     * optionally ADMIN_SEED_EMAIL_2/ADMIN_SEED_PASSWORD_2 for a second one —
     * both are updateOrCreate, so re-running is always safe.
     */
    public function run(): void
    {
        $this->seedOne(env('ADMIN_SEED_EMAIL'), env('ADMIN_SEED_PASSWORD'), required: true);
        $this->seedOne(env('ADMIN_SEED_EMAIL_2'), env('ADMIN_SEED_PASSWORD_2'), required: false);
    }

    private function seedOne(?string $email, ?string $password, bool $required): void
    {
        if (! $email || ! $password) {
            if ($required) {
                $this->command->error('Set ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD in .env before running this seeder.');
            }

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Admin Anagata', 'password' => Hash::make($password)]
        );

        $this->command->info("Admin user ready: $email");
    }
}
