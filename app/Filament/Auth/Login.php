<?php

namespace App\Filament\Auth;

use App\Rules\TurnstileCaptcha;
use App\Services\TurnstileCaptchaService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class Login extends \Filament\Auth\Pages\Login
{
    public string $captchaToken = '';

    public function mount(): void
    {
        parent::mount();

        if (request()->boolean('expired')) {
            session()->regenerateToken();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(array_filter([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
                $this->getTurnstileFormComponent(),
            ]));
    }

    protected function getTurnstileFormComponent(): ?Component
    {
        if (! app(TurnstileCaptchaService::class)->isEnabled()) {
            return null;
        }

        $service = app(TurnstileCaptchaService::class);

        return ViewField::make('turnstile')
            ->hiddenLabel()
            ->view('filament.admin.turnstile-field')
            ->viewData([
                'turnstileEnabled' => true,
                'turnstileSiteKey' => $service->siteKey(),
            ])
            ->columnSpanFull();
    }

    public function authenticate(): ?LoginResponse
    {
        if (filled($this->data['email'] ?? null)) {
            $this->data['email'] = self::normalizeEmail($this->data['email']);
        }

        if (app(TurnstileCaptchaService::class)->isEnabled()) {
            try {
                $this->validate([
                    'captchaToken' => ['required', new TurnstileCaptcha],
                ]);
            } catch (ValidationException $exception) {
                $this->resetTurnstileCaptcha();

                throw $exception;
            }
        }

        try {
            return parent::authenticate();
        } catch (ValidationException $exception) {
            $this->resetTurnstileCaptcha();

            throw $exception;
        }
    }

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

    protected function resetTurnstileCaptcha(): void
    {
        if (! app(TurnstileCaptchaService::class)->isEnabled()) {
            return;
        }

        $this->captchaToken = '';
        $this->dispatch('turnstile-reset', elementId: 'turnstile-admin-login');
    }
}
