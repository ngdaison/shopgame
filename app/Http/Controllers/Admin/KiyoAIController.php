<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KiyoAIService;
use Illuminate\Http\Request;

class KiyoAIController extends Controller
{
    protected $kiyoAIService;

    public function __construct(KiyoAIService $kiyoAIService)
    {
        $this->kiyoAIService = $kiyoAIService;
    }

    public function index()
    {
        $chatgptConfig = \App\Models\ApiConfig::where('name', 'chatgpt')->first();
        $config = $chatgptConfig ? $chatgptConfig->value : [];
        
        return view('admin.kiyoai.index', compact('config'));
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
            'model'   => 'nullable|string',
            'memory'  => 'nullable|boolean',
            'ai_type' => 'nullable|string',
        ]);

        try {
            $message = $request->message;
            $history = $request->history ?? [];
            $model = $request->model;
            $memory = $request->memory ?? true;
            $ai_type = $request->ai_type ?? 'default';

            // Limit memory to last 5 conversations (as requested)
            $history = array_slice($history, -5);

            $response = $this->kiyoAIService->getResponse($message, $history, $model, $memory, $ai_type);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi Server: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()
            ]);
        }
    }
}
