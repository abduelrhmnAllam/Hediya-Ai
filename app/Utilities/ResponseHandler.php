<?php

namespace App\Utilities;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use stdClass;

class ResponseHandler
{
    /**
     * Send a standardized success response.
     *
     * @param mixed       $data         The response data (array, object, etc.)
     * @param string      $message      A short success message (default "Success")
     * @param int         $httpStatus   The HTTP status code (default 200)
     * @param int         $customCode   An internal success code (default 8200)
     * @param bool        $encode       Whether to apply HTML entity encoding to data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success(
        $data = null,
        string $message = 'Success',
        int $httpStatus = 200,
        int $customCode = 8200,
        bool $encode = false
    ): JsonResponse {
        // Ensure $data is not null
        $data = $data ?? new stdClass();

        // Optionally encode the data
        if ($encode) {
            $data = self::applyHTMLEntities($data);
        }

        return response()->json([
            'status'  => $httpStatus,
            'code'    => $customCode,
            'message' => $message,
            'data'    => $data,
        ], $httpStatus);
    }

    /**
     * Send a standardized error response with optional debug logging.
     *
     * @param string      $errorMessage  The error message (e.g. "Validation failed")
     * @param int         $httpStatus    The HTTP status code (default 500)
     * @param int         $customCode    An internal error code (default 9770)
     * @param mixed       $data          Additional data (errors, debug info, etc.)
     * @param bool        $encode        Whether to apply HTML entity encoding to data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(
        string $errorMessage = 'An error occurred',
        int $httpStatus = 500,
        int $customCode = 9770,
               $data = null,
        bool $encode = false
    ): JsonResponse {
        $data       = $data ?? new stdClass();
        $debug      = config('app.debug');
        $currentUri = Route::current() ? Route::current()->uri() : 'unknown';
        // If app.debug = true, use the exact error message and log it;
        // otherwise, show a generic message from your lang files or fallback.
        $responseMessage = $debug
            ? $errorMessage
            : __('common.errors.unexpected'); // e.g. "Something went wrong. Please try again."

        // In debug mode, log to a specific channel
        if ($debug) {
            self::logError('error_logs', "Error in [{$currentUri}]: {$errorMessage}");
        }

        // Optionally encode data
        if ($encode) {
            $data = self::applyHTMLEntities($data);
        }


        return response()->json([
            'status'  => $httpStatus,
            'code'    => $customCode,
            'message' => $responseMessage,
            'data'    => $data,
        ], $httpStatus);
    }

    /**
     * Recursively apply HTML entity encoding to strings and arrays.
     *
     * @param mixed $params Data to encode
     * @return mixed
     */
    public static function applyHTMLEntities($params)
    {
        if (is_array($params)) {
            return array_map([self::class, __METHOD__], $params);
        }
        if (is_string($params)) {
            return htmlspecialchars($params, ENT_QUOTES, 'UTF-8');
        }
        if ($params instanceof \Illuminate\Database\Eloquent\Collection) {
            return $params->map([self::class, __METHOD__]);
        }
        return $params;
    }

    /**
     * Custom logging utility for managing log channels.
     *
     * @param string $channel The log channel name (defined in config/logging.php).
     * @param string $message The log message.
     */
    private static function logError(string $channel, string $message): void
    {
        Log::channel($channel)->error($message);
    }
}
