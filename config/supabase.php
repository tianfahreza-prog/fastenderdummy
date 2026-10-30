<?php
/**
 * Fastender Pre-Order Platform
 * Supabase Configuration - Storage API Only
 * Loads from .env file
 */

require_once __DIR__ . '/env.php';

class SupabaseStorage {
    private $url;
    private $serviceKey;
    
    public function __construct() {
        $this->url = env('SUPABASE_URL', 'https://your-project-ref.supabase.co');
        $this->serviceKey = env('SUPABASE_SERVICE_ROLE_KEY', 'your-service-role-key');
    }
    
    public function upload(string $bucket, string $path, string $filePath, string $contentType = 'image/jpeg'): array {
        $url = rtrim($this->url, '/') . "/storage/v1/object/$bucket/$path";
        
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            throw new Exception("Failed to read file: $filePath");
        }
        
        $headers = [
            'Authorization: Bearer ' . $this->serviceKey,
            'Content-Type: ' . $contentType,
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $fileContent,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Storage upload failed: $error");
        }
        
        if ($httpCode >= 400) {
            throw new Exception("Storage upload error ($httpCode): $response");
        }
        
        return json_decode($response, true) ?? [];
    }
    
    public function uploadFromString(string $bucket, string $path, string $content, string $contentType = 'image/jpeg'): array {
        $url = rtrim($this->url, '/') . "/storage/v1/object/$bucket/$path";
        
        $headers = [
            'Authorization: Bearer ' . $this->serviceKey,
            'Content-Type: ' . $contentType,
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $content,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Storage upload failed: $error");
        }
        
        if ($httpCode >= 400) {
            throw new Exception("Storage upload error ($httpCode): $response");
        }
        
        return json_decode($response, true) ?? [];
    }
    
    public function getPublicUrl(string $bucket, string $path): string {
        return rtrim($this->url, '/') . "/storage/v1/object/public/$bucket/$path";
    }
    
    public function delete(string $bucket, array $paths): array {
        $url = rtrim($this->url, '/') . "/storage/v1/object/$bucket";
        
        $headers = [
            'Authorization: Bearer ' . $this->serviceKey,
            'Content-Type: application/json',
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode(['prefixes' => $paths]),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Storage delete failed: $error");
        }
        
        return json_decode($response, true) ?? [];
    }
}

function getSupabaseStorage(): SupabaseStorage {
    static $storage = null;
    if ($storage === null) {
        $storage = new SupabaseStorage();
    }
    return $storage;
}

// Bucket names from .env with fallbacks
function getBucketWebData(): string {
    return env('BUCKET_WEB_DATA', 'web_data');
}

function getBucketBuktiPembayaran(): string {
    return env('BUCKET_BUKTI_PEMBAYARAN', 'bukti_pembayaran');
}

function getBucketImageProducts(): string {
    return env('BUCKET_IMAGE_PRODUCTS', 'image_products');
}