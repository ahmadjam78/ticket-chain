<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\User\Notifications\AdminNotification;
use App\Domains\User\Resources\V1\AuthMessageResource;
use App\Domains\User\Resources\V1\AuthUserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\User\Services\AuthService;
use App\Domains\User\DTOs\RegisterUserDTO;
use App\Domains\User\DTOs\LoginUserDTO;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use DomainException;

/**
 * Class AuthController
 *
 * Handles API authentication endpoints including user registration, login, logout,
 * and retrieving the currently authenticated user.
 *
 * @package App\Http\Controllers\Api\V1
 */
class AuthController extends Controller
{
    /**
     * AuthController constructor.
     *
     * @param AuthService $authService Service handling authentication business logic.
     */
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * Register a new user.
     *
     * Creates a user account, sends a welcome notification,
     * and returns the user resource with 201 status code.
     *
     * @param RegisterRequest $request The validated registration request.
     * @return JsonResponse The newly created user resource with HTTP 201.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new RegisterUserDTO(
            name: $request->name,
            email: $request->email,
            password: $request->password
        );

        $user = $this->authService->register($dto);

        $user->notify(new AdminNotification(
            title: 'Welcome to our platform!',
            message: "Hello {$user->name}, your account has been successfully created.",
            type: 'success'
        ));

        return (new AuthUserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Authenticate a user and start a session.
     *
     * Attempts to log in the user with provided credentials.
     * On success, regenerates the session ID and returns the user resource.
     *
     * @param LoginRequest $request The validated login request.
     * @return AuthUserResource The authenticated user resource.
     * @throws DomainException When credentials are invalid (handled and returned as JSON response).
     */
    public function login(LoginRequest $request)
    {
        $dto = new LoginUserDTO(
            email: $request->email,
            password: $request->password
        );

        try {
            $user = $this->authService->login($dto);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }

        $request->session()->regenerate();

        return new AuthUserResource($user);
    }

    /**
     * Log out the currently authenticated user.
     *
     * Invalidates the session, regenerates the CSRF token,
     * and returns a logout confirmation message.
     *
     * @param Request $request The HTTP request instance.
     * @return AuthMessageResource Resource containing logout confirmation message.
     */
    public function logout(Request $request): AuthMessageResource
    {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return new AuthMessageResource([
            'message' => 'Logged out'
        ]);
    }

    /**
     * Get the currently authenticated user.
     *
     * Loads the user's roles and returns the user resource.
     *
     * @param Request $request The HTTP request instance (authenticated).
     * @return AuthUserResource The current user resource with roles.
     */
    public function me(Request $request): AuthUserResource
    {
        $user = $request->user()->load('roles');

        return new AuthUserResource($user);
    }
}
