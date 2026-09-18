<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateNotificationSiteTitle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:update-site-title {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace occurrences of "Laravel" in notifications.content with the current site title (Helper::branding or config)';

    public function handle()
    {
        // Determine site title
        try {
            $siteTitle = class_exists('Helper') ? \Helper::branding('title', null, true) : null;
        } catch (\Exception $e) {
            $siteTitle = null;
        }

        $siteTitle = $siteTitle ?: config('app.name', 'KiyoVN');

        $this->info("Using site title: {$siteTitle}");

        $query = DB::table('notifications')->where('content', 'like', '%Laravel%');

        $count = $query->count();
        if ($count === 0) {
            $this->info('No notifications found containing "Laravel".');
            return 0;
        }

        $this->info("Found {$count} notification(s) containing 'Laravel'.");

        if ($this->option('dry-run')) {
            $this->info('Dry run enabled — no changes will be made.');
            return 0;
        }

        $updated = 0;

        $items = $query->get();
        foreach ($items as $item) {
            $newContent = str_replace('Laravel', $siteTitle, $item->content);
            if ($newContent !== $item->content) {
                DB::table('notifications')->where('id', $item->id)->update(['content' => $newContent]);
                $updated++;
            }
        }

        $this->info("Updated {$updated} notification(s).");
        return 0;
    }
}
