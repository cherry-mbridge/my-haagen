<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['member_id', 'token_hash', 'expires_at', 'is_revoked'])]
class RefreshToken extends Model
{
    protected $casts = [
        'expires_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
