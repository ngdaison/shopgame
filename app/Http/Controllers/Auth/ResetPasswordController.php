<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    /**
     * Display the password reset view for the given token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(\Illuminate\Http\Request $request, $token = null)
    {
        $email = $request->email;
        $realToken = $token;

        // Try to decode Base64 URL Safe Payload
        // Check if it looks like base64 (alphanumeric + - _) and is long
        if (!str_contains($token, '_____') && !str_contains($token, '__')) {
            try {
                $base64 = str_replace(['-', '_'], ['+', '/'], $token);
                $json = base64_decode($base64);
                $data = json_decode($json, true);

                if (is_array($data) && isset($data['t']) && isset($data['e'])) {
                    $realToken = $data['t'];
                    $email = $data['e'];
                }
            } catch (\Exception $e) {}
        }

        // Fallback: Parse Composite Token (Format: TOKEN_____HEX_EMAIL_____PADDING)
        if (str_contains($token, '_____')) {
            $parts = explode('_____', $token);
            if (count($parts) >= 2) {
                // Real Token
                $realToken = $parts[0];
                
                // Extract Email if present (hex encoded)
                if (isset($parts[1]) && !empty($parts[1])) {
                    try {
                        $decodedEmail = hex2bin($parts[1]);
                        if (filter_var($decodedEmail, FILTER_VALIDATE_EMAIL)) {
                            $email = $decodedEmail;
                        }
                    } catch (\Exception $e) {}
                }
            }
        }
        // Fallback for old padding format (just in case)
        elseif (str_contains($token, '__')) {
            $realToken = explode('__', $token)[0];
        }

        return view('auth.passwords.reset')->with(
            ['token' => $realToken, 'email' => $email]
        );
    }
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(\Illuminate\Support\Str::random(60));

        $user->save();

        event(new \Illuminate\Auth\Events\PasswordReset($user));

        $this->guard()->login($user);

        // Notification
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type'    => 'system',
            'title'   => 'Đổi mật khẩu thành công',
            'content' => 'Mật khẩu tài khoản của bạn đã được thay đổi thành công.',
            'icon'    => 'fa fa-key ps-1',
            'is_read' => false
        ]);
    }
}
