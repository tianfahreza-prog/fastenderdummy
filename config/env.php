<?php
/**
 * Fastender Pre-Order Platform
 * Environment Loader
 * Loads .env file into $_ENV and putenv()
 */

function loadEnv(string $path = __DIR__ . '/../.env'): void {
    if (!file_exists($path)) {
        // Try parent directory
        $path = dirname(__DIR__) . '/.env';
    }
    
    if (!file_exists($path)) {
        return; // No .env file, rely on system environment
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments
        if (str_starts_with($line, '#')) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove surrounding quotes
            $value = trim($value, '"\'');
            
            // Expand variables like ${VAR_NAME}
            $value = preg_replace_callback('/\$\{([A-Z0-9_]+)\}/', function($matches) {
                return $_ENV[$matches[1]] ?? getenv($matches[1]) ?? '';
            }, $value);
            
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load immediately
loadEnv();

// Helper to get env with fallback
function env(string $key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?? $default;
}

// Helper for boolean env
function envBool(string $key, bool $default = false): bool {
    $val = env($key);
    if ($val === null) return $default;
    return filter_var($val, FILTER_VALIDATE_BOOLEAN);
}

// Helper for int env
function envInt(string $key, int $default = 0): int {
    $val = env($key);
    if ($val === null) return $default;
    return (int)$val;
}