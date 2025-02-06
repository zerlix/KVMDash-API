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
        $tokens = $this->cleanupTokens($tokens);
        
        return isset($tokens[$token]);
    }



    private function storeToken(string $token, string $username): void
    {
        $tokens = $this->loadTokens();

        // Neuen Token hinzufügen
        $tokens[$token] = [
            'username' => $username,
            'created_at' => time()
        ];

        // Mit Pretty Print speichern für bessere Lesbarkeit
        file_put_contents(
            $this->tokenFile,
            json_encode($tokens, JSON_PRETTY_PRINT)
        );
    }

    private function loadTokens(): array
    {
        if (!file_exists($this->tokenFile)) {
            return [];
        }

        $content = file_get_contents($this->tokenFile);
        if ($content === false) {
            return [];
        }

        /** @var array<string, array<string, mixed>>|null $tokens */
        $tokens = json_decode($content, true);
        if ($tokens === null) {
            // Bei ungültigem JSON-Format: Datei zurücksetzen
            file_put_contents($this->tokenFile, '{}');
            return [];
        }

        return $tokens;
    }



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
