<?php

namespace App\Helpers;

class Sanitizer
{
    /**
     * Sanitize string input - remove potentially dangerous characters
     */
    public static function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        // Convert special characters to HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }

    /**
     * Sanitize HTML content - allow only safe tags
     */
    public static function sanitizeHtml(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Strip all HTML tags except allowed ones
        $allowedTags = '<b><i><u><strong><em><br><p>';
        
        return strip_tags($value, $allowedTags);
    }

    /**
     * Sanitize phone number - keep only digits and optional plus
     */
    public static function sanitizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        // Keep only digits and optional leading plus
        return preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Sanitize email - lowercase and trim
     */
    public static function sanitizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        return strtolower(trim($email));
    }

    /**
     * Sanitize username - alphanumeric and underscore only
     */
    public static function sanitizeUsername(?string $username): ?string
    {
        if ($username === null) {
            return null;
        }

        // Keep only alphanumeric and underscore
        return preg_replace('/[^a-zA-Z0-9_]/', '', $username);
    }

    /**
     * Sanitize filename - remove dangerous characters
     */
    public static function sanitizeFilename(?string $filename): ?string
    {
        if ($filename === null) {
            return null;
        }

        // Remove path separators and dangerous characters
        $filename = preg_replace('/[\/\\\\:*?"<>|]/', '', $filename);
        
        // Limit length
        if (strlen($filename) > 255) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 250) . '.' . $ext;
        }

        return $filename;
    }

    /**
     * Sanitize URL - ensure it's a valid URL
     */
    public static function sanitizeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);
        
        // Add scheme if missing
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return $url;
    }

    /**
     * Sanitize array of strings
     */
    public static function sanitizeArray(array $array): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return self::sanitizeString($value);
            }
            if (is_array($value)) {
                return self::sanitizeArray($value);
            }
            return $value;
        }, $array);
    }

    /**
     * Remove SQL injection patterns
     */
    public static function preventSqlInjection(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Remove common SQL injection patterns
        $patterns = [
            '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE)\b/i',
            '/--/',
            '/\/\*.*?\*\//s',
            '/;/',
        ];

        return preg_replace($patterns, '', $value);
    }

    /**
     * Clean XSS patterns
     */
    public static function preventXss(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Remove JavaScript event handlers
        $value = preg_replace('/on\w+\s*=/i', '', $value);
        
        // Remove javascript: URLs
        $value = preg_replace('/javascript:/i', '', $value);
        
        // Remove data: URLs (can be used for XSS)
        $value = preg_replace('/data:/i', '', $value);
        
        return $value;
    }
}
