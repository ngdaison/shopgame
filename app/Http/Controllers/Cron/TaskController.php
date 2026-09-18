<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Helper;

class TaskController extends Controller
{
    public function handle(Request $request)
    {
        // Require key if needed, for now just simple execution
        // if ($request->key !== config('app.cron_key')) abort(403);

        $tasks = Automation::where('status', true)->get();
        $results = [];

        foreach ($tasks as $task) {
            $results[$task->name] = $this->executeTask($task);
        }

        return response()->json([
            'status' => true,
            'results' => $results
        ]);
    }

    private function executeTask(Automation $task)
    {
        try {
            switch ($task->type) {
                case 'delete_no_deposit_user':
                    return $this->deleteNoDepositUsers($task);
                case 'delete_inactive_no_deposit_user':
                    return $this->deleteInactiveNoDepositUsers($task);
                case 'delete_logs':
                    return $this->deleteLogs($task);
                case 'delete_notifications':
                    return $this->deleteNotifications($task);
                default:
                    return 'Unknown task type';
            }
        }
        catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    private function deleteNoDepositUsers(Automation $task)
    {
        $limitTime = Carbon::now()->subHours($task->time_in_hours);

        // Find users with 0 deposit and created before limitTime
        // Process in chunks to avoid memory limit
        $count = 0;

        User::where('total_deposit', 0)
            ->where('created_at', '<=', $limitTime)
            ->whereNotIn('role', ['admin', 'accounting', 'partner'])
            ->chunkById(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                // Check if user has any balance just in case (e.g. from giftcode)
                // Re-reading the requirement: "User chưa từng phát sinh giao dịch nạp tiền" -> total_deposit = 0
                // Action: "Tiến hành xóa user"

                // Optional: checking balance > 0 to be safe? 
                // Requirement implies "no deposit" is the only criteria.
                // But usually we shouldn't delete users with balance.
                // I will stick to the requirement: total_deposit 0.

                $user->delete();
                $count++;
            }
        });

        return "Deleted $count users";
    }

    private function deleteLogs(Automation $task)
    {
        $limitTime = Carbon::now()->subHours($task->time_in_hours);

        // Delete system logs older than limitTime
        // We can use delete() directly as logs are usually just rows
        // If table is huge, chunk deletion might be better, but delete() is standard for now.
        // Let's use chunked delete to be safe for large log tables to avoid lock/timeout

        $count = 0;

        // Use a loop to delete in chunks of 5000 to avoid locking the table for too long
        do {
            $deleted = \App\Models\SystemLog::where('created_at', '<=', $limitTime)
                ->limit(5000)
                ->delete();
            $count += $deleted;
        } while ($deleted > 0);

        return "Deleted $count logs";
    }

    private function deleteInactiveNoDepositUsers(Automation $task)
    {
        $limitTime = Carbon::now()->subHours($task->time_in_hours);

        // Find users with 0 deposit
        // AND (last_login_at < limitTime OR (last_login_at is null AND created_at < limitTime))

        $count = 0;

        User::where('total_deposit', 0)
            ->where(function ($query) use ($limitTime) {
            $query->where('last_login_at', '<=', $limitTime)
                ->orWhere(function ($q) use ($limitTime) {
                $q->whereNull('last_login_at')
                    ->where('created_at', '<=', $limitTime);
            }
            );
        })
            ->whereNotIn('role', ['admin', 'accounting', 'partner'])
            ->chunkById(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                $user->delete();
                $count++;
            }
        });

        return "Deleted $count users";
    }

    private function deleteNotifications(Automation $task)
    {
        $limitTime = Carbon::now()->subHours($task->time_in_hours);
        $count = 0;

        do {
            $deleted = \App\Models\Notification::where('created_at', '<=', $limitTime)
                ->limit(5000)
                ->delete();
            $count += $deleted;
        } while ($deleted > 0);

        return "Deleted $count notifications";
    }
}
