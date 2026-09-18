<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemLog;

class LogSystemActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Proceed with the request first
        $response = $next($request);

        // Perform logging after the request is handled (terminable middleware behavior simulated here)
        // Note: For heavy load, this should be dispatched to a Job. 
        // But per requirements, we do it directly.
        try {
            $this->logActivity($request, $response);
        }
        catch (\Exception $e) {
            // Do not fail the request if logging fails
            \Illuminate\Support\Facades\Log::error('System Logging Failed: ' . $e->getMessage());
        }

        return $response;
    }

    protected function logActivity(Request $request, Response $response)
    {
        // Check for excluded paths from settings
        $excludedPaths = theme_config('excluded_log_paths');
        if ($excludedPaths) {
            if (trim($excludedPaths) === '/') {
                return; // Exclude ALL
            }

            $patterns = preg_split('/[\r\n,]+/', $excludedPaths, -1, PREG_SPLIT_NO_EMPTY);
            $currentPath = $request->path(); // e.g. "account/heartbeat"

            foreach ($patterns as $pattern) {
                $pattern = trim($pattern);
                if (empty($pattern))
                    continue;

                // Normalize pattern (remove leading slash for comparison with request->path())
                $normalizedPattern = ltrim($pattern, '/');

                // Case 1: Exact match or starts with (e.g. "account/heartbeat" matches "account/heartbeat")
                // Case 2: Pattern is part of the path
                if ($currentPath === $normalizedPattern || str_contains($currentPath, $normalizedPattern)) {
                    return;
                }
            }
        }

        // Don't log the logging requests (optional but good practice)
        if ($request->is('admin/logs*')) {
            return;
        }

        $params = $request->all();
        // Hide sensitive fields
        if (isset($params['password']))
            $params['password'] = '******';
        if (isset($params['password_confirmation']))
            $params['password_confirmation'] = '******';
        if (isset($params['old_password']))
            $params['old_password'] = '******';
        if (isset($params['new_password']))
            $params['new_password'] = '******';

        // Capture Response Content (limit length to avoid massive DB growth)
        $responseContent = $response->getContent();
        // Try to decode JSON to ensure we store valid JSON or just string
        $jsonResponse = json_decode($responseContent, true);
        if ($jsonResponse) {
            $responseContent = json_encode($jsonResponse, JSON_UNESCAPED_UNICODE);
        }
        else {
            // Limit non-JSON response logging (e.g. HTML pages) to 500 chars to save space
            // User example showed JSON, so they care about APIs basically.
            // If it's a View, logging valid HTML might be too much.
            // Let's log full content if it's not too huge, mostly needed for debugging API.
            // But for standard HTML pages, it's just HTML.
            if (strlen($responseContent) > 20000) {
                $responseContent = substr($responseContent, 0, 20000) . '... [Truncated]';
            }
        }

        SystemLog::create([
            'user_id' => Auth::check() ?Auth::id() : null,
            'request_url' => $request->fullUrl(),
            'method' => $request->method(),
            'params' => $params, // casted to array in model, JSON in DB
            'response' => $responseContent,
            'ip' => $request->ip(),
        ]);
    }
}
