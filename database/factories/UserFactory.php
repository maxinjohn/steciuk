<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\UserName;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'name' => trim($firstName.' '.$lastName),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::Member->value,
            'account_status' => AccountStatus::Approved->value,
            'is_active' => true,
            'approved_at' => now(),
            'pronouns' => 'they/them',
            'gender' => 'female',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_status' => AccountStatus::Pending->value,
            'approved_at' => null,
            'email_verified_at' => null,
        ]);
    }

    public function configure(): static
    {
        return $this->afterMaking(function (User $user): void {
            $composed = UserName::fromParts($user->first_name, $user->last_name);

            if (filled($user->name) && $composed !== trim($user->name)) {
                $parts = UserName::split($user->name);
                $user->first_name = $parts['first_name'];
                $user->last_name = $parts['last_name'];
            } elseif (filled($user->first_name) || filled($user->last_name)) {
                $user->name = UserName::fromParts($user->first_name, $user->last_name);
            }
        });
    }
}
