<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ScribeUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'scribe@example.com'],
            [
                'name' => 'Scribe API',
                'password' => bcrypt(Str::random(32)),
            ]
        );

        $token = $user->createToken('Scribe token')->plainTextToken;

        // Gib den Token in der Konsole aus
        echo "\nScribe token:\n" . $token . "\n";

        // Optional: speichere ihn in eine Datei
        file_put_contents(storage_path('app/private/scribe-token.txt'), $token);
    }
} 