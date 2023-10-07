<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    const BASE_URL = '/api/v1/auth';

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['signin', 'signup']]);
    }

    #[OA\Post(
        path: self::BASE_URL . "/signup",
        summary: "Sign up.",
        description: "User sign up.",
        tags: ["auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/SignupData")
        )
    )]
    #[OA\Schema(schema: "SignupData", properties: [
        new OA\Property(property: "name", type: "string", example: "John"),
        new OA\Property(property: "email", type: "string", example: "john-example@mail.mail"),
        new OA\Property(property: "password", type: "string", example: "123456")
    ])]
    #[OA\Response(
        response: 200,
        description: "Successful operation.",
        content: new OA\JsonContent(ref: "#/components/schemas/Success")
    )]
    #[OA\Response(
        response: 422,
        description: "Validation error.",
        content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
    )]
    public function signup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required'],
        ]);

        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return response()->json([
            'status' => '200',
            'message' => 'Successful operation.'
        ]);
    }

    #[OA\Post(
        path: self::BASE_URL . "/signin",
        summary: "Sign in.",
        description: "User sign in.",
        tags: ["auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/SigninData")
        )
    )]
    #[OA\Schema(schema: "SigninData", properties: [
        new OA\Property(property: "email", type: "string", example: "john-example@mail.mail"),
        new OA\Property(property: "password", type: "string", example: "123456")
    ])]
    #[OA\Response(
        response: 200,
        description: "Successful signin.",
        content: new OA\JsonContent(ref: "#/components/schemas/JwtTokenResponse")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    #[OA\Response(
        response: 422,
        description: "Validation error.",
        content: new OA\JsonContent(ref: "#/components/schemas/ValidationError")
    )]
    public function signin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => '401',
                'message' => 'Unauthorized.'
            ], 401);
        }

        return $this->tokenResponse($token);
    }

    #[OA\Get(
        path: self::BASE_URL . "/refresh",
        summary: "Refresh.",
        description: "Refresh a token.",
        tags: ["auth"],
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: "Successful refresh.",
        content: new OA\JsonContent(ref: "#/components/schemas/JwtTokenResponse")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    public function refresh(): JsonResponse
    {
        return $this->tokenResponse(auth()->refresh());
    }

    #[OA\Get(
        path: self::BASE_URL . "/signout",
        summary: "Sign out.",
        description: "Sign the user out (Invalidate the token).",
        tags: ["auth"],
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: "Successful signout.",
        content: new OA\JsonContent(ref: "#/components/schemas/Success")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    public function signout(): JsonResponse
    {
        auth()->logout();

        return response()->json([
            'status' => '200',
            'message' => 'Successfully signed out.'
        ]);
    }

    #[OA\Get(
        path: self::BASE_URL . "/current-user",
        summary: "Current user.",
        description: "It returns current user.",
        tags: ["auth"],
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: "Successful signout.",
        content: new OA\JsonContent(ref: "#/components/schemas/Success")
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized.",
        content: new OA\JsonContent(ref: "#/components/schemas/Unauthorized")
    )]
    public function currentUser(): UserResource
    {
        return UserResource::make(auth()->user());
    }

    /**
     * Prepare JWT token response
     * 
     * @param string $token
     * @return JsonResponse
     */
    #[OA\Schema(schema: "JwtTokenResponse", properties: [
        new OA\Property(property: "token", type: "string", example: "string"),
        new OA\Property(property: "expires_in", type:"integer", example: 3600)
    ])]
    private function tokenResponse(string $token): JsonResponse
    {
        return response()->json([
            'token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
