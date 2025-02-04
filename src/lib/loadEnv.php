<?php
/**
 * Loads environment variables from a .env file into the PHP environment.
 *
 * @param string $filePath The path to the .env file.
 * @throws Exception If the .env file does not exist or cannot be read.
 */
function loadEnv(string $filePath) : void {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        throw new Exception('.env file not found or not readable');
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Ignore comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Extract key and value
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Optional: Remove quotes around the value
            $value = trim($value, '"\'');

            // Set variable in environment
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
