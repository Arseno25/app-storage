<?php

namespace App\Filament\Auth;

use DominionSolutions\FilamentCaptcha\Forms\Components\Captcha;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseAuth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class AdminLogin extends BaseAuth
{

    public function getHeading(): string|Htmlable
    {
        return __('filament-panels::pages/auth/login.heading');
    }
    /**
     * Get the form for the resource.
     */
    public function form(Form $form): Form
    {
        $schema = [
            $this->getUsernameFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];

        // Tambahkan captcha hanya jika disable_captcha = false
        if ($this->shouldShowCaptcha()) {
            $schema[] = $this->getCaptchaFormComponent();
        }

        return $form
            ->schema($schema)
            ->statePath('data');
    }

    protected function shouldShowCaptcha(): bool
    {
        return !config('captcha.disable', false);
    }

    /**
     * Get the username form component.
     */
    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Username/Email')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCaptchaFormComponent(): Component
    {
        return Captcha::make('captcha')
            ->rules(['captcha'])
            ->required()
            ->columnStart(1);
    }

    /**
     * Get the credentials from the form data.
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $type = filter_var($data['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        return [
            $type => $data['username'],
            'password' => $data['password'],
        ];
    }

    /**
     * Validate the captcha.
     */
    protected function validateCaptcha(array $data): void
    {
        validator($data, [
            'captcha' => 'captcha',
        ]);
    }

    /**
     * Authenticate the user.
     * @throws ValidationException
     */
    public function authenticate(): ?LoginResponse
    {
        $data = request()->all();

        try {
            $this->validateCaptcha($data);
            return parent::authenticate();
        } catch (ValidationException $e) {
            if ($e->errors()['data.captcha'] ?? false) {
                throw ValidationException::withMessages([
                    'data.captcha' => __('Captcha does not match the image'),
                ]);
            }

            throw ValidationException::withMessages([
                'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }
    }
}