<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Controller;

use Zerlix\KvmDash\Api\Model\TokenStorage;

class AuthController
{
    private TokenStorage $tokenStorage;
    private string  $tokenFile;

    public function __construct()
    {
        $this->tokenStorage = new TokenStorage();
        $this->tokenFile = __DIR__ . '/../../tmp/token_file.json';
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

        /**
     * Store token in file
     *
     * @param string $token
     * @param string $username
     * @return void
     */
    private function storeToken(string $token, string $username): void
    {
        $tokens = $this->loadTokens();

        // Neuen Token hinzufügen
        $tokens[$token] = [
            'username' => $username,
            'created_at' => time()
        ];

        file_put_contents(
            $this->tokenFile,
            json_encode($tokens, JSON_PRETTY_PRINT)
        );
    }


        /**
     * Load tokens from file
     *
     * @return array<string, array{username: string, created_at: int}>
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

        /** @var array<string, array{username: string, created_at: int}>|null $tokens */
        $tokens = json_decode($content, true);
        if ($tokens === null || !$this->validateTokenStructure($tokens)) {
            // Bei ungültigem JSON-Format oder Struktur: Datei zurücksetzen
            file_put_contents($this->tokenFile, '{}');
            return [];
        }

        return $tokens;
    }

        /**
     * Validate token structure
     *
     * @param array<string, mixed> $tokens
     * @return bool
     */
    private function validateTokenStructure(array $tokens): bool
    {
        foreach ($tokens as $token) {
            if (
                !is_array($token)
                || !isset($token['username'])
                || !is_string($token['username'])
                || !isset($token['created_at'])
                || !is_int($token['created_at'])
            ) {
                return false;
            }
        }
        return true;
    }


        /**
     * Cleanup tokens and remove all tokens older than 24 hours
     *
     * @param array<string, array{username: string, created_at: int}> $tokens
     * @return array<string, array{username: string, created_at: int}>
     */
    private function cleanupTokens(array $tokens): array
    {
        $now = time();
        $hasChanges = false;

        foreach ($tokens as $key => $data) {
            if (($now - $data['created_at']) > 86400) {
                unset($tokens[$key]);
                $hasChanges = true;
            }
        }

        // Nur speichern wenn sich was geändert hat
        if ($hasChanges) {
            file_put_contents(
                $this->tokenFile,
                json_encode($tokens, JSON_PRETTY_PRINT)
            );
        }

        return $tokens;
    }

}
