<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Collaborator
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (!auth()->check()) {
      return redirect()->route('login');
    }

    $user = auth()->user();

    // Check status
    if ($user->status !== 'active') {
      return abort(401, 'Tài khoản của bạn đã bị khóa hoặc không hoạt động');
    }

    // Trim values in colla_type array to handle potential whitespace issues
    $collaType = array_map('trim', (array) $user->colla_type);
    
    // Check if user has at least one valid collaborator type OR is an admin
    $hasCategory = count(array_intersect($collaType, ['account', 'boosting', 'items'])) > 0;
    
    if ($user->role !== 'admin' && !$hasCategory) {
      return abort(403, 'Bạn không có quyền cộng tác viên để truy cập trang này');
    }

    return $next($request);
  }
}
