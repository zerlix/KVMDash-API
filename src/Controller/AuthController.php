<?php

namespace Zerlix\KvmDash\Api\Controller;

class AuthController
{
    public function login(string $username, string $password): array
    {
        $envUser = $_ENV['API_USER'] ?? null;
        $envPassword = $_ENV['API_PASSWORD'] ?? null;
        var_dump($envUser);
    
        if ($username !== $envUser) {
            return ['status' => 'error', 'message' => "Benutzername $username nicht gefunden"];
        }

        if (!password_verify($password, $envPassword)) {
            return ['status' => 'error', 'message' => 'Falsches Passwort'];
        }

        // Token generieren
        $token = base64_encode(random_bytes(32));
        return ['status' => 'success', 'token' => $token];
    }

    public function verifyToken(string $token): bool
    {
        // Hier sollten Sie den Token überprüfen, z.B. aus einer Datenbank oder einem Cache
        // Beispiel:
        // $result = $db->query('SELECT * FROM tokens WHERE token = ?', [$token]);
        // return !empty($result);
        return true;
    }
}