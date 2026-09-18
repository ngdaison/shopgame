<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\Ingames;
use Illuminate\Http\Request;

class ListIngameController extends Controller
{
  public function index(Request $request)
  {
    $ingames = Ingames::orderBy('id', 'desc')->get();

    return response()->json([
      'data'    => $ingames,
      'status'  => 200,
      'message' => 'Get list ingame success',
    ]);
  }
}
