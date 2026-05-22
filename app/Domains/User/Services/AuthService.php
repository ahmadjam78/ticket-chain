<?php

namespace App\Domains\User\Services;

use App\Domains\User\Actions\RegisterUserAction;
use App\Domains\User\Actions\LoginUserAction;
use App\Domains\User\DTOs\RegisterUserDTO;
use App\Domains\User\DTOs\LoginUserDTO;
use App\Domains\User\Events\UserRegistered;
use App\Domains\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use DomainException;

/**
 * Class AuthService
 *
 * Service class handling user authentication operations including registration and login.
 * Orchestrates actions, password hashing, role assignment, event dispatching, and session management.
 *
 * @package App\Domains\User\Services
 */
class AuthService
{
    /**
     * AuthService constructor.
     *
     * @param RegisterUserAction $registerAction Action for creating a new user.
     * @param LoginUserAction $loginAction Action for authenticating user credentials.
     */
    public function __construct(
        private RegisterUserAction $registerAction,
        private LoginUserAction $loginAction,
    ) {}

    /**
     * Register a new user.
     *
     * Hashes the password, executes the register action, assigns the 'customer' role,
     * dispatches a UserRegistered event, and returns the created user.
     *
     * @param RegisterUserDTO $dto Data transfer object containing name, email, and plain password.
     * @return User The newly created user model instance.
     */
    public function register(RegisterUserDTO $dto): User
    {
        // Hash the plain password before passing to action
        $dto->password = Hash::make($dto->password);

        $user = $this->registerAction->execute($dto);

        $user->assignRole('customer');

        UserRegistered::dispatch($user);

        return $user;
    }

    /**
     * Authenticate a user and log them in.
     *
     * Validates credentials using the login action. If successful, logs the user into the session.
     * Throws a DomainException if credentials are invalid.
     *
     * @param LoginUserDTO $dto Data transfer object containing email and password.
     * @return User The authenticated user model instance.
     * @throws DomainException When credentials are invalid.
     */
    public function login(LoginUserDTO $dto): User
    {
        $user = $this->loginAction->execute($dto);

        if (!$user) {
            throw new DomainException('Invalid credentials');
        }

        Auth::login($user);

        return $user;
    }
}
