<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class APIController extends Controller
{
    protected $locale;

    public function __construct(Request $request)
    {
        $this->locale = config('app.locale');
        if ($request->has('locale')) {
            $this->locale = $request->locale;
        }
    }

    /**
     * Return success response
     * @param   array|object   $data       Array to return
     * @param   string  $message    Message to show
     * @return  json    Response json
     */
    protected function successResponse($data, $message = "Ok")
    {
        $response = ['status' => 200, 'error' => 0, 'message'=>$message, 'data' => $data];
        if (is_countable($data)) {
            $response['count'] = count($data);
            //$response['count'] = $data->count();
        }
        return response()->json($response);
    }

    /**
     * Return not found response
     * @param   array   $data       Array to return
     * @param   string  $message    Message to show
     * @return  json    Response json
     */
    protected function notFoundResponse($data, $message = "Not found")
    {
        $response = ['status' => 400, 'error' => 1, 'message'=>$message, 'data' => $data];
        return response()->json($response);
    }

    /**
     * Return custom error response
     * @param   array   $data       Array to return
     * @param   string  $message    Message to show
     * @return  json    Response json
     */
    protected function errorResponse($data, $message = "Error occured")
    {
        $response = ['status' => 500, 'error' => 1, 'message'=>$message, 'data' => $data];
        return response()->json($response);
    }
}
