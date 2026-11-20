<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'identifier' => (string) fake()->unique()->numerify('22#####'),
            'role' => User::MAHASISWA,
            'faculty' => 'Fakultas Teknik',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function technician(): static
    {
        return $this->state(fn () => ['role' => User::TEKNISI, 'faculty' => null]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => User::ADMIN, 'faculty' => null]);
    }
}
