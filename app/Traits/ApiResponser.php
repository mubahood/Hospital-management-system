<?php

namespace App\Traits;

trait ApiResponser
{
    protected function success($data = [], $message = "")
    {
        //set header to must return json
        header('Content-Type: application/json');
        http_response_code(200);

        return response()->json([
            'code' => 1,
            'message' => $message,
            'data' => $data
        ]);
    }

    protected function error($message = "")
    {
        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => ""
        ]);
    }
}
 