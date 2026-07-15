<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\HasPublicId;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'id'])]
class Member extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory, Notifiable, HasPublicId;

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }
}
