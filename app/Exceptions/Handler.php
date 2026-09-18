<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Http;

class Handler extends ExceptionHandler
{
  /**
   * The list of the inputs that are never flashed to the session on validation exceptions.
   *
   * @var array<int, string>
   */
  protected $dontFlash = [
    'current_password',
    'password',
    'password_confirmation',
  ];

  /**
   * Register the exception handling callbacks for the application.
   */
  public function register(): void
  {
    $this->reportable(function (Throwable $e) {
      //
    });
  }

  /**
   * Render an exception into an HTTP response.
   */
  public function render($request, Throwable $exception)
  {
      if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException || $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
          
          $shouldRedirect = false;
          try {
              if (class_exists('Helper')) {
                  $shouldRedirect = (\Helper::getConfig('redirect_404_to_home') == 1);
              }
              if (!$shouldRedirect && function_exists('setting')) {
                  $shouldRedirect = (setting('redirect_404_to_home') == 1);
              }
          } catch (\Exception $e) {}

          if ($shouldRedirect) {
              \Illuminate\Support\Facades\Log::info("404 Route Catch-all Redirecting to home: " . request()->fullUrl());
              return redirect()->to(url('/'));
          }

          try {
              $url = $request->fullUrl();
              $ip = $request->ip();

              $messageTelegram = "⚠️ *Cảnh Báo 404*\nTrang: $url\nIP: $ip";
              $messageDiscord = "**⚠️ Cảnh Báo 404**\nTrang: `$url`\nIP: `$ip`";

              // Send Telegram 
              $telegram = setting('telegram_config', []);
              if (!empty($telegram['bot_token'])) {
                  $chatIds = array_filter([$telegram['chat_id_deposit'] ?? null, $telegram['chat_id_order'] ?? null, $telegram['chat_id'] ?? null]);
                  if(!empty($chatIds)){
                      $chatId = reset($chatIds); 
                      Http::post("https://api.telegram.org/bot{$telegram['bot_token']}/sendMessage", [
                          'chat_id' => $chatId,
                          'text' => $messageTelegram,
                          'parse_mode' => 'Markdown'
                      ]);
                  }
              }

              // Send Discord
              $discord = setting('discord_config', []);
              $webhookUrls = array_filter([$discord['webhook_deposit'] ?? null, $discord['webhook_order'] ?? null]);
              if (!empty($webhookUrls)) {
                  $webhookUrl = reset($webhookUrls);
                  Http::post($webhookUrl, [
                      'content' => $messageDiscord
                  ]);
              }
          } catch (\Exception $e) {
              // Ignore exception
          }

          if ($request->expectsJson()) {
              return response()->json(['message' => 'Not Found.'], 404);
          }
          return response()->view('errors.404', [], 404);
      }

      return parent::render($request, $exception);
  }
}
