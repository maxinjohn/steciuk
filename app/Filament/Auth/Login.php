<?php

namespace App\Filament\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class Login extends \Filament\Auth\Pages\Login
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'email' => self::normalizeEmail($data['email'] ?? ''),
            'password' => $data['password'] ?? '',
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        if (filled($this->data['email'] ?? null)) {
            $this->data['email'] = self::normalizeEmail($this->data['email']);
        }

        return parent::authenticate();
    }

    protected function throwFailureValidationException(): never
    {
        if (request()->attributes->get('admin_login_panel_denied')) {
            throw ValidationException::withMessages([
                'data.email' => 'Your account does not have permission to access the parish admin panel. Contact the site administrator.',
            ]);
        }

        parent::throwFailureValidationException();
    }

    public static function normalizeEmail(mixed $email): string
    {
        return strtolower(trim((string) $email));
    }
}
