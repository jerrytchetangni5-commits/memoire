<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Configure a valid ADMIN_EMAIL before seeding the administrator.');
        }

        if (! is_string($password) || trim($password) === '') {
            throw new RuntimeException('Set ADMIN_PASSWORD in .env before seeding the administrator.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'nom' => 'Administrateur',
                'prenom' => 'ENEAM',
                'password' => $password,
                'role' => 'admin',
                'actif' => true,
            ]
        );
    }
}
