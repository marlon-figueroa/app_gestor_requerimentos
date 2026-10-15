<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UsuariosSeeders::class,
        ]);

        if (app()->isLocal() && User::role('Usuario')->doesntExist()) {
            User::factory(15)
                ->sequence(fn () => ['created_at' => now()->subDays(random_int(0, 90))])
                ->create()
                ->each(fn (User $user) => $user->assignRole('Usuario'));
        }
    }
}
