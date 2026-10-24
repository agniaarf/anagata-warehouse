<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The admin user is deliberately NOT seeded here — see AdminUserSeeder,
     * run explicitly so re-seeding content never resets a changed password.
     */
    public function run(): void
    {
        $this->call(CmsContentSeeder::class);
    }
}
