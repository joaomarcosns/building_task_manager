<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        return response()->json([
            'data' => Building::all(),
        ]);
    }
}
