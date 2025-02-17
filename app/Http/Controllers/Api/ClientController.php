<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        return response()->json([
            'data' => Client::all(),
        ]);
    }
}
