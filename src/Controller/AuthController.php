<?php

namespace Zerlix\KvmDash\Api\Controller;

class AuthController
{
    private $tokenFile = '../../token_file.json';

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

    private function storeToken(string $token, string $username): void
    {
        $tokens = $this->loadTokens();
        $tokens[$token] = ['username' => $username, 'created_at' => time()];
        file_put_contents($this->tokenFile, json_encode($tokens));
    }

    private function loadTokens(): array
    {
        if (!file_exists($this->tokenFile)) {
            return [];
        }

        $tokens = json_decode(file_get_contents($this->tokenFile), true);
        return $tokens ?? [];
    }

    public function verifyToken(string $token): bool
    {
        $tokens = $this->loadTokens();
        return isset($tokens[$token]);
    }
}