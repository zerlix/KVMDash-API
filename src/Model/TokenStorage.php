<?php

declare(strict_types=1);

namespace Zerlix\KvmDash\Api\Model;


class TokenStorage
{
    private string $tokenFile;

    public function __construct()
    {
        $this->tokenFile = __DIR__ . '/../../tmp/token_file.json';
    }


    /**
     * create a token for the user
     * 
     * @param string $username
     * @param string $password
     * @return array<string, mixed>
     */
    public function createToken(string $username, string $password): array
    {
        $envUser = $_ENV['API_USER'] ?? null;
        $envPassword = $_ENV['API_PASSWORD'] ?? null;

        if ($username !== $envUser) {
            return ['status' => 'error', 'message' => "Benutzername $username nicht gefunden"];
        }

        if (!is_string($envPassword)) {
            return ['status' => 'error', 'message' => 'API_PASSWORD nicht gesetzt'];
        }

        if (!password_verify($password, $envPassword)) {
            return ['status' => 'error', 'message' => 'Falsches Passwort'];
        }

        // Token generieren
        $token = base64_encode(random_bytes(32));
        $this->storeToken($token, $username);

        return ['status' => 'success', 'token' => $token];
    }


    /**
     * Check if token is valid and delete it 
     * if it is older than 24 hours
     * 
     * @param string $token
     * @return bool
     */
    public function verifyToken(string $token): bool
    {
        $tokens = $this->loadTokens();
        
        // Tokens bereinigen und gleichzeitig speichern wenn nötig
        if (getenv('DEBUG') !=='true' ) { 
            $tokens = $this->cleanupTokens($tokens);
        }
        
        return isset($tokens[$token]);
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
     * delete token from file
     * 
     * @param string $token Token from Authorization header
     * @return array<string, string> Status response
     */
    public function logout(string $token): array
    {
        $tokens = $this->loadTokens();

        if (!isset($tokens[$token])) {
            return ['status' => 'error', 'message' => 'Token nicht gefunden'];
        }

        // Token löschen
        unset($tokens[$token]);

        // Tokens speichern
        file_put_contents(
            $this->tokenFile,
            json_encode($tokens, JSON_PRETTY_PRINT)
        );

        return ['status' => 'success'];
    }
}
