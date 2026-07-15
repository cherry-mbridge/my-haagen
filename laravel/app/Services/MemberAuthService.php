<?php

namespace App\Services;

use App\Models\Member;
use App\Models\RefreshToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MemberAuthService
{
    public function register(array $data): array
    {
        $member = Member::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return $this->generateTokens($member);
    }

    public function login(array $credentials): ?array
    {
        $token = Auth::guard('member')->attempt($credentials);

        if (!$token) {
            return null;
        }

        $member = Auth::guard('member')->user();
        return $this->generateTokens($member, $token);
    }

    public function refresh(string $refresh_token): ?array
    {
        $hashed_token = $this->hashToken($refresh_token);

        $record = RefreshToken::where('token_hash', $hashed_token)
            ->where('is_revoked', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return null;
        }

        $member = Member::find($record->member_id);

        if (!$member) {
            return null;
        }

        // Revoke the used refresh token to prevent replay attacks
        $record->update(['is_revoked' => true]);

        return $this->generateTokens($member);
    }

    public function logout(Member $member): void
    {
        Auth::guard('member')->invalidate(true);

        RefreshToken::where('member_id', $member->id)
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);
    }

    public function profile(Member $member): Member
    {
        return $member;
    }

    private function generateTokens(Member $member, ?string $access_token = null): array
    {
        if (! $access_token) {
            $access_token = Auth::guard('member')->login($member);
        }

        $raw_refresh_token = bin2hex(random_bytes(64));

        RefreshToken::create([
            'member_id' => $member->id,
            'token_hash' => $this->hashToken($raw_refresh_token),
            'expires_at' => Carbon::now()->addMinutes(config('auth.refresh_token_ttl')),
        ]);

        return [
            'access_token' => $access_token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_token' => $raw_refresh_token,
            'member' => $member,
        ];
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
