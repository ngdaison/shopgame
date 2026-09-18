<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\History;

class LogController extends Controller
{
    public function index($type)
    {
        if ($type === 'histories') {
            $histories = History::orderBy('id', 'desc')->limit(3000)->get();

            return view('admin.logs.histories', compact('histories'));
        }
    }
}
