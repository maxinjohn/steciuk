<?php

namespace Tests\Support;

use Livewire\Features\SupportTesting\Testable;

trait RegistersTestMembers
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function withRequiredRegistrationFields(Testable $component, array $overrides = []): Testable
    {
        return $component
            ->set('first_name', $overrides['first_name'] ?? 'Parish')
            ->set('last_name', $overrides['last_name'] ?? 'Member')
            ->set('pronouns', $overrides['pronouns'] ?? 'he/him')
            ->set('gender', $overrides['gender'] ?? 'male')
            ->set('email', $overrides['email'] ?? 'member@example.com')
            ->set('password', $overrides['password'] ?? 'SecurePass!123')
            ->set('password_confirmation', $overrides['password_confirmation'] ?? 'SecurePass!123')
            ->set('phone', $overrides['phone'] ?? '07700900123')
            ->set('date_of_birth', $overrides['date_of_birth'] ?? '1990-05-15')
            ->set('postcode', $overrides['postcode'] ?? 'M1 1AE');
    }
}
