<?php

namespace App\Services;

use App\Models\ApiConfig;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\ItemOrder;
use App\Models\GBOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KiyoAIService
{
    protected $config;

    public function __construct()
    {
        $chatgptConfig = ApiConfig::where('name', 'chatgpt')->first();
        $this->config = $chatgptConfig ? $chatgptConfig->value : [];
    }

    public function getResponse($message, $history = [], $model = null, $memoryEnabled = true, $ai_type = 'default')
    {
        // If ai_type is default, ALWAYS handle locally to avoid API missing errors
        if ($ai_type === 'default') {
            if ($this->canAnswerLocally($message)) {
                return $this->getLocalResponse($message);
            }
            return [
                'status' => true,
                'message' => "Tôi chỉ hỗ trợ trả lời các câu hỏi về **thống kê Dashboard** (Doanh thu, Thành viên, Đơn hàng...) ở chế độ này.\n\n💡 *Bạn có cần mình trợ giúp gì nữa không?*"
            ];
        }

        if (empty($this->config['chatgpt_api_key'])) {
            return [
                'status' => false,
                'message' => 'ChatGPT API chưa được cấu hình. Vui lòng vào Cấu hình hệ thống để thiết lập.'
            ];
        }

        try {
            // Step 1: Analyze Intent and Fetch Data
            $context = $this->fetchDataContext($message);

            // Step 2: Call ChatGPT with Context
            return $this->callChatGPT($message, $context, $history, $model, $memoryEnabled);

        } catch (\Exception $e) {
            Log::error('KiyoAI Error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Có lỗi xảy ra khi xử lý yêu cầu: ' . $e->getMessage()
            ];
        }
    }

    protected function canAnswerLocally($message)
    {
        $message = mb_strtolower($message);
        $keywords = ['doanh thu', 'lợi nhuận', 'thành viên', 'người dùng', 'đơn hàng', 'nạp tiền', 'số dư', 'thống kê', 'tổng', 'tuần', 'tháng', 'hôm nay'];
        foreach ($keywords as $kw) {
            if (str_contains($message, $kw)) return true;
        }
        return false;
    }

    protected function getLocalResponse($message)
    {
        $message = mb_strtolower($message);
        $data = json_decode($this->fetchDataContext($message), true);
        
        $output = "";

        if (isset($data['today_revenue'])) {
            $output .= "💰 **Doanh thu hôm nay:** " . number_format($data['today_revenue']) . "đ\n";
            $output .= "📈 **Doanh thu tháng này:** " . number_format($data['month_revenue']) . "đ\n";
        }

        if (isset($data['total_users'])) {
            $output .= "👥 **Tổng thành viên:** " . number_format($data['total_users']) . "\n";
        }

        if (isset($data['recent_orders_count'])) {
            $output .= "📦 **Đơn hàng hôm nay:** " . number_format($data['recent_orders_count']) . "\n";
        }

        if (isset($data['general_stats'])) {
            $output .= "👥 **Tổng User:** " . number_format($data['general_stats']['total_users']) . "\n";
            $output .= "💰 **Tổng Doanh thu:** " . number_format($data['general_stats']['total_revenue']) . "đ\n";
            $output .= "📦 **Tổng Đơn hàng:** " . number_format($data['general_stats']['total_orders']) . "\n";
        }

        $output .= "\n💡 *Bạn có cần mình trợ giúp gì nữa không?*";

        return [
            'status' => true,
            'message' => $output
        ];
    }

    protected function fetchDataContext($message)
    {
        $message = mb_strtolower($message);
        $data = [];

        // Simple keyword-based intent detection for common queries
        if (str_contains($message, 'doanh thu') || str_contains($message, 'tiền nạp')) {
            $data['today_revenue'] = Invoice::whereDate('created_at', now())->where('status', 'paid')->sum('amount');
            $data['month_revenue'] = Invoice::whereMonth('created_at', now()->month)->where('status', 'paid')->sum('amount');
            $data['last_month_revenue'] = Invoice::whereMonth('created_at', now()->subMonth()->month)->where('status', 'paid')->sum('amount');
        }

        if (str_contains($message, 'user') || str_contains($message, 'người dùng') || str_contains($message, 'khách')) {
            $data['total_users'] = User::count();
            $data['top_users_by_balance'] = User::orderBy('balance', 'desc')->limit(10)->get(['username', 'balance', 'email'])->toArray();
            $data['top_users_by_spent'] = User::orderBy('total_spent', 'desc')->limit(10)->get(['username', 'total_spent', 'email'])->toArray();
        }

        if (str_contains($message, 'đơn hàng') || str_contains($message, 'bán chạy') || str_contains($message, 'dịch vụ')) {
            $data['recent_orders_count'] = ItemOrder::whereDate('created_at', now())->count() + GBOrder::whereDate('created_at', now())->count();
            // This is simplified, real logic would need joining with products/items
            $data['top_items'] = ItemOrder::select('item_id', DB::raw('count(*) as total'))
                ->groupBy('item_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get()
                ->toArray();
        }
        
        // General stats for any question if no context found yet
        if (empty($data)) {
            $data['general_stats'] = [
                'total_users' => User::count(),
                'total_revenue' => Invoice::where('status', 'paid')->sum('amount'),
                'total_orders' => ItemOrder::count() + GBOrder::count(),
            ];
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function callChatGPT($prompt, $context, $history = [], $model = null, $memoryEnabled = true)
    {
        $apiKey = $this->config['chatgpt_api_key'];
        $model = $model ?? ($this->config['chatgpt_model'] ?? 'gpt-4o-mini');
        $maxTokens = $this->config['max_tokens'] ?? 2000;
        $temperature = (float) ($this->config['temperature'] ?? 0.7);

        $systemPrompt = "Bạn là KiyoAI, một trợ lý AI thông minh tích hợp vào hệ thống admin của website. " .
            "Nhiệm vụ của bạn là phân tích dữ liệu website và trả lời các câu hỏi của admin một cách ngắn gọn, súc tích và chính xác bằng tiếng Việt. " .
            "Dưới đây là ngữ cảnh dữ liệu hiện tại từ hệ thống (JSON): \n" . $context . "\n\n" .
            "Quy tắc: \n" .
            "1. Chỉ sử dụng dữ liệu được cung cấp. \n" .
            "2. Nếu không có dữ liệu, hãy báo cho admin biết. \n" .
            "3. Không tiết lộ các thông tin nhạy cảm như mật khẩu hay token bảo mật (nếu có trong context). \n" .
            "4. Thể hiện sự chuyên nghiệp, thân thiện. \n" .
            "5. Định dạng câu trả lời rõ ràng (sử dụng markdown, bullet points nếu cần).";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        if ($memoryEnabled) {
            foreach ($history as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => (int)$maxTokens,
            'temperature' => $temperature,
        ]);

        if ($response->successful()) {
            return [
                'status' => true,
                'message' => $response->json('choices.0.message.content')
            ];
        }

        return [
            'status' => false,
            'message' => 'API ChatGPT trả về lỗi: ' . ($response->json('error.message') ?? 'Lỗi không xác định')
        ];
    }
}
