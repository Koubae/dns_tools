<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AppController extends Controller
{
    public function __construct() { }

    /**
     * Return metadata with the App API version and more
     * @return JsonResponse
     */
    public function version(): JsonResponse
    {
        $reponsePayload = [
            'App' => config('app.name'),
            'Api-version' => config('app.api_version'),
            'Author' => config('app.author'),
            'License' => config('app.license'),
            'Summary' => config('app.summary'),
            'Description' => config('app.description'),
            'Docs' => config('app.docs'),
        ];
        return response()->json($reponsePayload);
    }

}
