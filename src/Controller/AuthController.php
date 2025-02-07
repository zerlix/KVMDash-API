<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Controller;

use Zerlix\KvmDash\Api\Model\TokenStorage;

class AuthController
{
    private TokenStorage $tokenStorage;

    public function __construct()
    {
        $this->tokenStorage = new TokenStorage();
    }

    /**
     * Handle the login request
     * 
     * @return array<string, mixed>
     */
    public function login(): array
    {
        $rawInput = file_get_contents('php://input');
        if ($rawInput === false) {
            return ['status' => 'error', 'message' => 'Keine Eingabedaten'];
        }

        /** @var array{username?: string, password?: string}|null $input */
        $input = json_decode($rawInput, true);
        if (!is_array($input)) {
            return ['status' => 'error', 'message' => 'Ungültiges JSON-Format'];
        }

        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        return $this->tokenStorage->createToken($username, $password);
    }

    // Handle logout 
    public function logout(string $token): void
    {
        $this->tokenStorage->logout($token);
    }

    // handle token verification
    public function verifyToken(string $token): bool
    {
        return $this->tokenStorage->verifyToken($token);
    }
}
