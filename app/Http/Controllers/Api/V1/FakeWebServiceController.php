<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Class FakeWebServiceController
 *
 * Simulates a web service endpoint for testing and development purposes.
 * Randomly returns one of several HTTP status codes (200, 400, 429, 500)
 * with a corresponding JSON response body.
 *
 * This controller is useful for testing retry logic, error handling,
 * and resilience patterns without making actual external API calls.
 *
 * @package App\Http\Controllers\Api\V1
 */
class FakeWebServiceController
{
    /**
     * Simulate a call to an external web service.
     *
     * Randomly selects an HTTP status code from a predefined set (200, 400, 429, 500)
     * and returns an array containing the success flag, JSON response body, and status code.
     *
     * @param array $data The request data (unused in simulation, but kept for signature compatibility).
     * @return array{
     *     success: bool,
     *     response: string,
     *     status_code: int
     * } An associative array with the following keys:
     *     - success: True if status code is 200, false otherwise.
     *     - response: JSON-encoded response body containing status, message, and code.
     *     - status_code: The simulated HTTP status code.
     */
    public static function call(array $data): array
    {
        $statusCodes = [200, 400, 429, 500];
        $status = $statusCodes[array_rand($statusCodes)];

        $messages = [
            200 => 'Success',
            400 => 'Bad Request',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
        ];

        $responseBody = [
            'status' => $status === 200 ? 'success' : 'error',
            'message' => $messages[$status],
            'code' => $status
        ];

        return [
            'success' => $status === 200,
            'response' => json_encode($responseBody),
            'status_code' => $status
        ];
    }
}
