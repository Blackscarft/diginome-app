<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Auth\Events\Attempting;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
        parent::booted(); // Jika ingin memanggil parent booted

        Event::listen(Attempting::class, function (Attempting $event) {
            // Cek apakah user yang sedang mencoba login memiliki is_active = false
            $user = static::where('email', $event->credentials['email'])->first();

            if ($user && ! $user->is_active) {
                // Batalkan proses login dan lemparkan error validasi
                throw ValidationException::withMessages([
                    'data.email' => __('Akun Anda telah dinonaktifkan oleh administrator.'),
                ]);
            }
        });
    }
}
