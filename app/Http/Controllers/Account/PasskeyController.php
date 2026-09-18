<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelWebauthn\Actions\PrepareCreationData;
use LaravelWebauthn\Actions\ValidateKeyCreation;
use LaravelWebauthn\Actions\DeleteKey;
use LaravelWebauthn\Models\WebauthnKey;
use Illuminate\Support\Facades\Log;

class PasskeyController extends Controller
{
    /**
     * Get registration options (challenge) for the browser.
     */
    public function options(Request $request)
    {
        try {
            $publicKey = app(PrepareCreationData::class)($request->user());
            return response()->json($publicKey);
        }
        catch (\Exception $e) {
            Log::error('Passkey Options Error: ' . $e->getMessage());
            return response()->json(['error' => 'Không thể tạo yêu cầu xác thực thiết bị.'], 500);
        }
    }

    /**
     * Register a new Passkey.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id' => 'required',
            'rawId' => 'required',
            'response' => 'required|array',
            'type' => 'required|string',
        ]);

        try {
            Log::info('Passkey Registration Attempt', [
                'user_id' => $request->user()->id,
                'data' => $request->only(['id', 'rawId', 'type']),
                'host' => $request->getHost(),
                'origin' => $request->header('origin'),
            ]);

            $webauthnKey = app(ValidateKeyCreation::class)(
                $request->user(),
                $request->only(['id', 'rawId', 'response', 'type']),
                $request->input('name')
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm thiết bị xác thực thành công.',
                'key' => [
                    'id' => $webauthnKey->id,
                    'name' => $webauthnKey->name,
                    'created_at' => $webauthnKey->created_at->format('Y-m-d H:i:s'),
                ]
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Passkey Validation Exception: ' . json_encode($e->errors()));
            return response()->json(['error' => 'Xác thực thiết bị thất bại: ' . collect($e->errors())->flatten()->first()], 422);
        }
        catch (\Exception $e) {
            Log::error('Passkey Registration Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Lỗi hệ thống khi xác thực thiết bị: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a Passkey.
     */
    public function destroy(Request $request, $id)
    {
        try {
            app(DeleteKey::class)($request->user(), (int)$id);
            return response()->json(['success' => true, 'message' => 'Đã xóa thiết bị xác thực.']);
        }
        catch (\Exception $e) {
            Log::error('Passkey Delete Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['message' => 'Lỗi xóa: ' . $e->getMessage(), 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * List user passkeys.
     */
    public function list(Request $request)
    {
        $keys = $request->user()->webauthnKeys()->orderBy('created_at', 'desc')->get();
        return response()->json($keys);
    }
}
