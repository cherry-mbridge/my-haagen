<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\MemberResource;
use App\Services\MemberAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use App\Models\Member;

class MemberAuthController extends Controller
{
    public function __construct(private MemberAuthService $service) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->service->register($request->validated());

        $cookie = $this->refreshTokenCookie($result['refresh_token']);

        return (new MemberResource($result['member']))
            ->additional([
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ])
            ->response()
            ->withCookie($cookie);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->service->login($request->validated());

        if (!$result) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $cookie = $this->refreshTokenCookie($result['refresh_token']);

        return (new MemberResource($result['member']))
            ->additional([
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ])
            ->response()
            ->withCookie($cookie);
    }

    public function refresh(Request $request): JsonResponse
    {
        $refresh_token = $request->cookie('refresh_token');

        if (!$refresh_token) {
            return response()->json(['message' => 'Refresh token required'], 422);
        }

        $result = $this->service->refresh($refresh_token);

        if (!$result) {
            $clear_cookie = $this->clearRefreshTokenCookie();
            return response()->json(['message' => 'Invalid or expired refresh token'], 401)
                ->withCookie($clear_cookie);
        }

        $cookie = $this->refreshTokenCookie($result['refresh_token']);

        return (new MemberResource($result['member']))
            ->additional([
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
            ])
            ->response()
            ->withCookie($cookie);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->service->logout($request->user('member'));

        $clear_cookie = $this->clearRefreshTokenCookie();

        return response()->json(['message' => 'Logged out successfully'])
            ->withCookie($clear_cookie);
    }

    public function me(Request $request): JsonResponse
    {
        return (new MemberResource($request->user('member')))->response();
    }

    public function find(Member $member): JsonResponse
    {
        return (new MemberResource($member))->response();
    }

    private function refreshTokenCookie(string $token): Cookie
    {
        return cookie(
            'refresh_token',          // Cookie name sent in the browser.
            $token,                   // The raw refresh token value.
            config('auth.refresh_token_ttl'), // Lifetime in minutes (matches DB expiry).
            '/',                      // Path: available for the entire site.
            null,                     // Domain: null means current domain only.
            app()->isProduction(),    // Secure: send only over HTTPS in production.
            true,                     // HttpOnly: inaccessible to JavaScript (XSS protection).
            false,                    // Raw: false allows Laravel to encrypt the cookie value.
            'Lax'                     // SameSite: cookie withheld on cross-site POST requests (CSRF mitigation).
        );
    }

    private function clearRefreshTokenCookie(): Cookie
    {
        return cookie(
            'refresh_token',          // Cookie name to clear.
            '',                       // Empty value to invalidate the cookie.
            -1,                       // Expiry in the past so the browser deletes it immediately.
            '/',                      // Path: must match the original cookie path.
            null,                     // Domain: must match the original cookie domain.
            app()->isProduction(),    // Secure: must match the original cookie secure flag.
            true,                     // HttpOnly: must match the original cookie HttpOnly flag.
            false,                    // Raw: must match the original cookie raw flag.
            'Lax'                     // SameSite: must match the original cookie SameSite value.
        );
    }
}
