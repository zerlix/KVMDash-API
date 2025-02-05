<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Controller;

class AuthController
{
    private string  $tokenFile;


    public function __construct()
    {
        $this->tokenFile = __DIR__ . '/../../tmp/token_file.json';
    }


    /**
     * Handle the login request
     * 
     * @param string $username
     * @param string $password
     * @return array<string, mixed>
     */
    public function login(string $username, string $password): array
    {
        $envUser = $_ENV['API_USER'] ?? null;
        $envPassword = $_ENV['API_PASSWORD'] ?? null;

        if ($username !== $envUser) {
            return ['status' => 'error', 'message' => "Benutzername $username nicht gefunden"];
        }

        if (!password_verify($password, $envPassword)) {
            return ['status' => 'error', 'message' => 'Falsches Passwort'];
        }

        // Token generieren
        $token = base64_encode(random_bytes(32));
        $this->storeToken($token, $username);

        return ['status' => 'success', 'token' => $token];
    }


    // store token in file
    private function storeToken(string $token, string $username): void
    {
        $tokens = $this->loadTokens();
        $tokens[$token] = ['username' => $username, 'created_at' => time()];
        file_put_contents($this->tokenFile, json_encode($tokens));
    }


    /**
     * Load tokens from file
     * 
     * @return array<string, array<string, mixed>>
     */
    private function loadTokens(): array
    {
        if (!file_exists($this->tokenFile)) {
            return [];
        }

        $content = file_get_contents($this->tokenFile);
        if ($content === false) {
            return [];
        }

        $tokens = json_decode($content, true);
        return $tokens ?? [];
    }
    
    /**
     * Check if token is valid
     * 
     * @param string $token
     * @return bool
     */
    public function verifyToken(string $token): bool
    {
        $tokens = $this->loadTokens();
        return isset($tokens[$token]);
    }
}
