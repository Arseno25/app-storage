<?php

namespace App\Livewire;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

class UserInfoComponent extends PersonalInfo
{

    protected string $view = "livewire.user-info-component";
    public array $only = ['name', 'email', 'phone_number'];
    public $user;
    public $userClass;


    public function mount(): void
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only($this->only));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->label('Name')
                ->required(),
                TextInput::make('email')
                ->label('Email')
                ->required(),
                TextInput::make('phone_number')
                ->label('Phone Number')
                ->required(),
                ])->statePath('data');
        }

        public function submit(): void
    {
        $data = collect($this->form->getState())->only($this->only)->all();
        $this->user->update($data);
        Notification::make()
            ->success()
            ->title(__('filament-breezy::default.profile.personal_info.notify'))
            ->send();
    }


}
