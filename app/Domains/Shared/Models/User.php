<?php

namespace App\Domains\Shared\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Domains\HR\Staff\Models\Staff;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function getTeacherAttribute()
    {
        return $this->staff?->teacher;
    }

    /**
     * Get the user's full name from their associated profile.
     * 
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->staff?->full_name
            ?? $this->student?->full_name_ar
            // ?? $this->guardian?->full_name  // Guardian relationship not yet defined in User model
            ?? $this->username;
    }
}
