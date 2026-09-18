<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,webp,svg|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            
            // Generate a unique filename
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            
            // Store in 'public/uploads/tickets'
            $path = $file->storeAs('uploads/tickets', $filename, 'public');
            
            // Asset URL
            $url = asset('storage/' . $path);

            return response()->json([
                'status' => true,
                'url' => $url
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No file uploaded'
        ], 400);
    }
}
