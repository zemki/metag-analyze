<?php

// Code within app\Helpers\Helper.php

namespace App\Helpers;

use Exception;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class Helper
{
    /**
     * Generate random string with a given set of chars
     *
     * @param  string  $keyspace
     * @return string
     *
     * @throws Exception
     */
    public static function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!$%()=-*.,')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; $i++) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    /**
     * Securely extract and validate file extension from base64 data URI
     *
     * SECURITY: This method validates the actual file content using finfo
     * to prevent MIME type spoofing attacks. Only whitelisted file types
     * are allowed (audio and image formats for research data collection).
     *
     * @param  string  $uri  base64 file data URI (e.g., data:audio/mpeg;base64,...)
     * @return string  Validated file extension (e.g., 'mp3', 'jpg')
     *
     * @throws \InvalidArgumentException if file type is not allowed or invalid
     */
    public static function extension($uri)
    {
        // Whitelist of allowed MIME types to file extensions
        // Only audio and image formats used for research data collection
        $allowedMimeTypes = [
            // Audio formats
            'audio/mpeg' => 'mp3',
            'audio/mp3' => 'mp3',
            'audio/x-mpeg' => 'mp3',
            'audio/mp4' => 'm4a',
            'audio/x-m4a' => 'm4a',
            'audio/aac' => 'aac',
            'audio/wav' => 'wav',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/ogg' => 'ogg',
            'audio/webm' => 'webm',
            'audio/flac' => 'flac',
            // Image formats
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];

        try {
            // Split data URI into header and data parts
            $parts = explode(',', $uri, 2);
            if (count($parts) !== 2) {
                throw new \InvalidArgumentException('Invalid data URI format');
            }

            // Decode the base64 data
            $data = base64_decode($parts[1], true);
            if ($data === false) {
                throw new \InvalidArgumentException('Invalid base64 encoding');
            }

            // Use finfo to detect the ACTUAL MIME type from file content
            // This prevents MIME type spoofing attacks
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMimeType = $finfo->buffer($data);

            // Normalize MIME type (remove charset if present)
            $detectedMimeType = strtolower(explode(';', $detectedMimeType)[0]);

            // Check if the detected MIME type is in our whitelist
            if (!isset($allowedMimeTypes[$detectedMimeType])) {
                throw new \InvalidArgumentException(
                    "File type not allowed. Detected MIME type: {$detectedMimeType}. ".
                    'Only audio and image files are permitted for research data collection.'
                );
            }

            // Return the safe, validated extension
            return $allowedMimeTypes[$detectedMimeType];

        } catch (\Exception $e) {
            // Log the security incident
            \Log::warning('File upload security validation failed', [
                'error' => $e->getMessage(),
                'uri_header' => substr($uri, 0, 50), // Log only header, not full data
            ]);

            // Throw exception to prevent insecure file storage
            throw new \InvalidArgumentException(
                'File validation failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * @return bool|string
     */
    public static function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);

        if ($ini === 0 || $ini === false) {
            return '';
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        if (strpos($string, $end, $ini) === false) {
            $len = strlen($string) - 1;
        }

        return substr($string, $ini, $len);
    }

    /**
     * Search for an element in array recursively
     *
     * @return bool
     */
    public static function in_array_recursive($needle, $haystack)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($haystack));
        foreach ($iterator as $element) {
            if ($element === $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  int  $depth
     * @param  array  $arrayKeys
     * @return array
     */
    public static function array_keys_recursive($myArray, $arrayKeys = [], $MAXDEPTH = INF, $depth = 0)
    {
        if ($depth < $MAXDEPTH) {
            $depth++;
            $keys = array_keys($myArray);
            foreach ($keys as $key) {
                if (is_array($myArray[$key])) {
                    $arrayKeys[$key] = Helper::array_keys_recursive($myArray[$key], $MAXDEPTH, $depth);
                }
            }
        }

        return $arrayKeys;
    }

    /**
     * @param  $array
     * @return array
     */
    public static function multiexplode($delimiters, $string)
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);

        return $launch;
    }
}
