<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\AuthLoginRequest;
use App\Http\Requests\Api\V1\AuthRegisterRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends BaseApiController
{
    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new customer account',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name', 'email', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                        new OA\Property(property: 'phone', type: 'string', example: '+1234567890'),
                        new OA\Property(property: 'address', type: 'string', example: '123 Flavor Street'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Registered successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function register(AuthRegisterRequest $request): JsonResponse
    {
        $existing = Customer::where('email', $request->email)->first();
        if ($existing) {
            return $this->sendError('Validation failed', ['email' => ['The email has already been taken.']], 422);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => 'active',
        ]);

        $token = $customer->createToken('customer_api_token')->plainTextToken;

        return $this->sendResponse([
            'customer' => new CustomerResource($customer),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Customer registered successfully', 201);
    }

    #[OA\Post(
        path: '/auth/login',
        summary: 'Login customer and retrieve API Bearer token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['email', 'password'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful'),
            new OA\Response(response: 401, description: 'Invalid credentials'),
        ]
    )]
    public function login(AuthLoginRequest $request): JsonResponse
    {
        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || ! Hash::check($request->password, $customer->password)) {
            return $this->sendError('Invalid email or password', [], 401);
        }

        if ($customer->status === 'suspended') {
            return $this->sendError('Account is suspended. Contact support.', [], 403);
        }

        $token = $customer->createToken('customer_api_token')->plainTextToken;

        return $this->sendResponse([
            'customer' => new CustomerResource($customer),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Revoke current customer API token',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logged out successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->sendResponse(null, 'Logged out successfully');
    }

    #[OA\Get(
        path: '/auth/me',
        summary: 'Get details of authenticated customer',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Customer profile data'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return $this->sendResponse(new CustomerResource($request->user()), 'Profile retrieved successfully');
    }

    #[OA\Put(
        path: '/auth/profile',
        summary: 'Update authenticated customer profile',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Johnathan Doe'),
                        new OA\Property(property: 'phone', type: 'string', example: '+1987654321'),
                        new OA\Property(property: 'address', type: 'string', example: '456 Gourmet Avenue'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Profile updated successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function updateProfile(Request $request): JsonResponse
    {
        $customer = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $customer->update($validated);

        return $this->sendResponse(new CustomerResource($customer->fresh()), 'Profile updated successfully');
    }

    #[OA\Put(
        path: '/auth/password',
        summary: 'Update authenticated customer password',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['current_password', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                        new OA\Property(property: 'password', type: 'string', format: 'password'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Password updated successfully'),
            new OA\Response(response: 400, description: 'Invalid current password'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = $request->user();

        if (! Hash::check($request->current_password, $customer->password)) {
            return $this->sendError('Current password does not match.', [], 400);
        }

        $customer->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->sendResponse(null, 'Password updated successfully');
    }

    #[OA\Post(
        path: '/auth/refresh',
        summary: 'Refresh API token',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Token refreshed successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function refresh(Request $request): JsonResponse
    {
        $customer = $request->user();
        $request->user()->currentAccessToken()?->delete();
        $newToken = $customer->createToken('customer_api_token')->plainTextToken;

        return $this->sendResponse([
            'token' => $newToken,
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully');
    }
}
