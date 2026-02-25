<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OfficeController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'http://localhost:1337/api');
    }

    public function index(Request $request)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->get("{$this->apiUrl}/offices", $request->all());

        return $response->json();
    }

    public function show(Request $request, $id)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->get("{$this->apiUrl}/offices/{$id}");
        
            return $response->json();
    }

    public function store(Request $request)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/offices", $request->all());

        return $response->json();
    }

    public function update(Request $request, $id)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->put ("{$this->apiUrl}/offices/{$id}", $request->all());

        return $response->json();
    }

    public function destroy(Request $request, $id)
    {
        $token = $request->bearerToken();

        $response = Http::withToken($token)
            ->delete("{$this->apiUrl}/offices/{$id}");

        return $response->json();
    }
    
}
