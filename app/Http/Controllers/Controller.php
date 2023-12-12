<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function sendResponse($result, $message = '')
    {
        $response = [
            'success' => true,
            'message' => $message,
            'response' => $result,
        ];

        try {
            Log::info($message);
        } catch (\Throwable $th) {
            Log::info(json_encode($th));
        }

        // Convert the response to a JSON string
        $jsonResponse = json_encode($response);

        // Calculate the content length
        $contentLength = strlen($jsonResponse);

        // Return the response with the Content-Length header
        return response($jsonResponse, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Length', $contentLength);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($message, $errorMessage = null, $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];


        if ($errorMessage) {
            $response['response'] = $errorMessage;
        }


        try {
            Log::info(json_encode($response));
        } catch (\Throwable $th) {
            Log::info(json_encode($th));
        }

        return response($response, $code)->header('Content-Type', 'application/json');
    }
}
