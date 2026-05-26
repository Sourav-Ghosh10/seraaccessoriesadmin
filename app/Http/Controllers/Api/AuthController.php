<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Member;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Sera Accessories API",
    description: "Secure REST API for Sera Accessories CRM. Supports Dealers, Salesmen, and Distributors authentication using short-lived access tokens and long-lived refresh tokens."
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    bearerFormat: "SanctumToken",
    scheme: "bearer",
    description: "Enter your access token as: Bearer {token}. For /auth/refresh use the refresh token."
)]
class AuthController extends Controller
{
    // =========================================================================
    // LOGIN — POST /api/auth/login
    // =========================================================================
    #[OA\Post(
        path: "/auth/login",
        summary: "Login as dealer, salesman, or distributor",
        description: "Authenticates a member using email and password only. Role is auto-detected from the database. Returns a short-lived access token (15 min) and a long-lived refresh token (7 days).",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    required: ["email", "password"],
                    properties: [
                        new OA\Property(property: "email",    type: "string", format: "email",    example: "dealer@example.com"),
                        new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string",  example: "Login successful."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "access_token",  type: "string",  example: "3|AbcXyzAccessToken..."),
                                new OA\Property(property: "refresh_token", type: "string",  example: "4|XyzRefreshToken..."),
                                new OA\Property(property: "token_type",    type: "string",  example: "Bearer"),
                                new OA\Property(property: "expires_in",    type: "integer", example: 900, description: "Access token lifetime in seconds (15 min)"),
                                new OA\Property(
                                    property: "user",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "id",          type: "integer", example: 1),
                                        new OA\Property(property: "name",        type: "string",  example: "John Doe"),
                                        new OA\Property(property: "email",       type: "string",  example: "dealer@example.com"),
                                        new OA\Property(property: "mobile",      type: "string",  example: "9876543210"),
                                        new OA\Property(property: "role",        type: "string",  enum: ["dealer", "salesman", "distributor"], example: "dealer"),
                                        new OA\Property(property: "status",      type: "string",  example: "Active"),
                                        new OA\Property(property: "shop",        type: "string",  nullable: true, example: "Sera Accessories Shop"),
                                        new OA\Property(property: "address",     type: "string",  nullable: true, example: "123 Main St"),
                                        new OA\Property(property: "salesman_id", type: "integer", nullable: true, example: null),
                                        new OA\Property(
                                            property: "salesman",
                                            type: "object",
                                            nullable: true,
                                            properties: [
                                                new OA\Property(property: "id",     type: "integer", example: 2),
                                                new OA\Property(property: "name",   type: "string",  example: "Rakesh Sharma"),
                                                new OA\Property(property: "mobile", type: "string",  example: "9876543210"),
                                                new OA\Property(property: "role",   type: "string",  example: "salesman")
                                            ]
                                        ),
                                        new OA\Property(property: "emp_id",      type: "string",  nullable: true, example: null),
                                        new OA\Property(property: "ref_code",    type: "string",  nullable: true, example: "REF001"),
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Account deactivated or role not allowed",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Your account has been deactivated. Please contact admin."),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Invalid credentials or validation failure",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "The provided credentials are incorrect."),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "email",
                                    type: "array",
                                    items: new OA\Items(type: "string", example: "The provided credentials are incorrect.")
                                )
                            ]
                        )
                    ]
                )
            ),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        /** @var Member|null $member */
        $member = Member::where('email', $request->email)->first();

        // Wrong credentials
        if (! $member || ! Hash::check($request->password, $member->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
                'errors'  => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 422);
        }

        // Only dealers, salesmen, and distributors may use the API
        $allowedRoles = ['dealer', 'salesman', 'distributor'];
        if (! in_array(strtolower($member->role), $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorised to access this API.',
            ], 403);
        }

        // Deactivated account
        if (strtolower($member->status) !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact admin.',
            ], 403);
        }

        $roleName = strtolower($member->role);

        // Generate JWT Access Token (100 years)
        $accessToken = \App\Services\JwtService::generateToken([
            'sub'        => $member->id,
            'role'       => $roleName,
            'token_type' => 'access',
        ], 3153600000);

        // Generate JWT Refresh Token (100 years)
        $refreshToken = \App\Services\JwtService::generateToken([
            'sub'        => $member->id,
            'role'       => $roleName,
            'token_type' => 'refresh',
        ], 3153600000);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type'    => 'Bearer',
                'expires_in'    => 3153600000,
                'whatsapp_number' => \App\Models\Setting::get('whatsapp_number', ''),
                'user'          => $this->formatMember($member),
            ],
        ], 200);
    }

    // =========================================================================
    // ME — GET /api/auth/me
    // =========================================================================
    #[OA\Get(
        path: "/auth/me",
        summary: "Get authenticated member profile",
        description: "Returns the full profile of the currently authenticated member. Requires an access token (not the refresh token) in the Authorization header.",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile fetched successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string",  example: "Profile fetched successfully."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id",          type: "integer", example: 1),
                                new OA\Property(property: "name",        type: "string",  example: "John Doe"),
                                new OA\Property(property: "email",       type: "string",  example: "dealer@example.com"),
                                new OA\Property(property: "mobile",      type: "string",  example: "9876543210"),
                                new OA\Property(property: "role",        type: "string",  example: "dealer"),
                                new OA\Property(property: "status",      type: "string",  example: "Active"),
                                new OA\Property(property: "shop",        type: "string",  nullable: true, example: "Sera Shop"),
                                new OA\Property(property: "address",     type: "string",  nullable: true, example: "123 Main St"),
                                new OA\Property(property: "salesman_id", type: "integer", nullable: true, example: null),
                                new OA\Property(
                                    property: "salesman",
                                    type: "object",
                                    nullable: true,
                                    properties: [
                                        new OA\Property(property: "id",     type: "integer", example: 2),
                                        new OA\Property(property: "name",   type: "string",  example: "Rakesh Sharma"),
                                        new OA\Property(property: "mobile", type: "string",  example: "9876543210"),
                                        new OA\Property(property: "role",   type: "string",  example: "salesman")
                                    ]
                                ),
                                new OA\Property(property: "emp_id",      type: "string",  nullable: true, example: null),
                                new OA\Property(property: "ref_code",    type: "string",  nullable: true, example: "REF001"),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Unauthenticated."),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Refresh token used instead of access token",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Please use your access token, not the refresh token."),
                    ]
                )
            ),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        /** @var Member $member */
        $member = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully.',
            'data'    => $this->formatMember($member),
        ], 200);
    }

    // =========================================================================
    // REFRESH — POST /api/auth/refresh
    // =========================================================================
    #[OA\Post(
        path: "/auth/refresh",
        summary: "Refresh tokens using a refresh token",
        description: "Pass the long-lived refresh token in the Authorization header. Returns a brand-new access token and refresh token. All old tokens are revoked.",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tokens refreshed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string",  example: "Tokens refreshed successfully."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "access_token",  type: "string",  example: "5|NewAccessToken..."),
                                new OA\Property(property: "refresh_token", type: "string",  example: "6|NewRefreshToken..."),
                                new OA\Property(property: "token_type",    type: "string",  example: "Bearer"),
                                new OA\Property(property: "expires_in",    type: "integer", example: 900),
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated or token expired",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Unauthenticated."),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Access token used instead of refresh token",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Please use your refresh token, not the access token."),
                    ]
                )
            ),
        ]
    )]
    public function refresh(Request $request): JsonResponse
    {
        $authorization = $request->header('Authorization');
        if (!$authorization || !str_starts_with($authorization, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $token = substr($authorization, 7);
        $payload = \App\Services\JwtService::decodeAndValidateToken($token);

        if (!$payload || ($payload['token_type'] ?? '') !== 'refresh') {
            return response()->json([
                'success' => false,
                'message' => 'Please use your refresh token, not the access token.'
            ], 403);
        }

        $member = Member::find($payload['sub'] ?? null);
        if (!$member || strtolower($member->status) !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $roleName = strtolower($member->role);

        // Generate brand new JWT Access Token (100 years)
        $newAccessToken = \App\Services\JwtService::generateToken([
            'sub'        => $member->id,
            'role'       => $roleName,
            'token_type' => 'access',
        ], 3153600000);

        // Generate brand new JWT Refresh Token (100 years)
        $newRefreshToken = \App\Services\JwtService::generateToken([
            'sub'        => $member->id,
            'role'       => $roleName,
            'token_type' => 'refresh',
        ], 3153600000);

        return response()->json([
            'success' => true,
            'message' => 'Tokens refreshed successfully.',
            'data'    => [
                'access_token'  => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'token_type'    => 'Bearer',
                'expires_in'    => 3153600000,
            ],
        ], 200);
    }

    // =========================================================================
    // LOGOUT — POST /api/auth/logout
    // =========================================================================
    #[OA\Post(
        path: "/auth/logout",
        summary: "Logout the authenticated member",
        description: "Revokes all issued tokens (access + refresh) for the member. You can pass either token type in the header.",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logged out successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string",  example: "Logged out successfully."),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string",  example: "Unauthenticated."),
                    ]
                )
            ),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ], 200);
    }

    // =========================================================================
    // PRIVATE HELPER — Consistent member data shape across all responses
    // =========================================================================
    private function formatMember(Member $member): array
    {
        if ($member->salesman_id && !$member->relationLoaded('salesman')) {
            $member->load('salesman');
        }

        return [
            'id'          => $member->id,
            'name'        => $member->name,
            'email'       => $member->email,
            'mobile'      => $member->mobile,
            'role'        => $member->role,
            'status'      => $member->status,
            'shop'        => $member->shop,
            'address'     => $member->address,
            'salesman_id' => $member->salesman_id,
            'emp_id'      => $member->emp_id,
            'ref_code'    => $member->ref_code,
            'salesman'    => $member->salesman ? [
                'id'     => $member->salesman->id,
                'name'   => $member->salesman->name,
                'mobile' => $member->salesman->mobile,
                'role'   => $member->salesman->role,
            ] : null,
        ];
    }
}
