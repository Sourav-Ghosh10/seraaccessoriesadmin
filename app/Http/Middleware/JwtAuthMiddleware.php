<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\JwtService;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');

        if (!$authorization || !str_starts_with($authorization, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $token = substr($authorization, 7);
        $payload = JwtService::decodeAndValidateToken($token);

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated or token expired.'
            ], 401);
        }

        // Verify token type
        if (($payload['token_type'] ?? '') !== 'access') {
            return response()->json([
                'success' => false,
                'message' => 'Please use your access token, not the refresh token.'
            ], 403);
        }

        // Verify member
        $member = Member::find($payload['sub'] ?? null);
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Verify status
        if (strtolower($member->status) !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact admin.'
            ], 403);
        }

        // Set the authenticated user on the guard and the request resolver
        Auth::setUser($member);
        Auth::shouldUse('member');
        $request->setUserResolver(function () use ($member) {
            return $member;
        });

        return $next($request);
    }
}
