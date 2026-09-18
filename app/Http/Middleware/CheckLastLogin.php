<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLastLogin
{
  public function handle(Request $request, Closure $next)
  {
    if (Auth::check()) {
      $user = Auth::user();
      $lastLoginAt = $user->last_login_at;

      // Robust comparison using string format to avoid precision issues
      $sessionValue = session('last_login_at');

      $sessionStr = $sessionValue instanceof \DateTimeInterface ? $sessionValue->format('Y-m-d H:i:s') : (string)$sessionValue;
      $dbStr = $lastLoginAt instanceof \DateTimeInterface ? $lastLoginAt->format('Y-m-d H:i:s') : (string)$lastLoginAt;

      if ($sessionStr != $dbStr && $lastLoginAt != null) {
        Auth::logout();
        return redirect('/login')->with('message', 'Your account was logged in from another location.');
      }
    }

    return $next($request);
  }
}
