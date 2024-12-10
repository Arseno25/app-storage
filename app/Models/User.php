<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (app()->runningInConsole()) {
                return;
            }

            if (in_array($user->name, ['Admin', 'Super Admin'])) {
                if (User::where('name', $user->name)->whereHas('roles', function ($query) {
                    $query->where('name', 'super_admin');
                })->exists()) {
                    return;
                }
            }

            $user->assignRole('users');
        });
    }

    /**
     * @throws \Exception
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if($panel->getId() === 'storageApp'){
            return $this->hasRole(Utils::getSuperAdminName()) || $this->hasRole('users');
        }else{
            return false;
        }
    }

    public function file():HasMany
    {
        return $this->hasMany(File::class);
    }
}
