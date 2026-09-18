<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class SystemReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset toàn bộ dữ liệu hệ thống về mặc định (Giữ lại Admin ID 1)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('HÀNH ĐỘNG NÀY SẼ XÓA TOÀN BỘ DỮ LIỆU! Bạn có chắc chắn muốn tiếp tục?')) {
            $this->info('Đã hủy bỏ.');
            return;
        }

        $this->info('Đang sao lưu tài khoản Admin...');
        $admin = User::find(1);
        if (!$admin) {
             // Fallback: Try to find first admin role or just any user if ID 1 missing
             $admin = User::orderBy('id', 'asc')->first();
        }
        
        if (!$admin) {
            if (!$this->confirm('Không tìm thấy tài khoản Admin nào để sao lưu. Bạn có muốn tiếp tục và xóa SẠCH sẽ không?')) {
                 return;
            }
        } else {
             $this->info("Đã tìm thấy Admin: {$admin->username} (ID: {$admin->id})");
        }
        
        // Backup Admin Data as Array
        $adminData = $admin ? $admin->toArray() : [];
        if ($admin) {
            // Unset ID/timestamps/etc if we want a fresh insert, 
            // BUT we want to keep ID 1 to match expectations.
            // So we keep ID. 
            // Password needs to be preserved.
             $adminData['password'] = $admin->password; // hidden in array usually? Check User model later. 
             // Actually `toArray` hides hidden fields. We need to manually get attributes.
             $adminData = $admin->getAttributes();
        }

        $this->info('Đang tắt kiểm tra khóa ngoại...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->info('Đang lấy danh sách bảng...');
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');
        $colName = "Tables_in_" . $dbName;
        
        // Fallback for different MySQL versions/drivers if colName incorrect
        if (empty($tables)) {
             $this->error("Không tìm thấy bảng nào.");
             DB::statement('SET FOREIGN_KEY_CHECKS=1;');
             return;
        }

        // Detect column key dynamically
        $firstRow = (array)$tables[0];
        $keys = array_keys($firstRow);
        $keyName = $keys[0];

        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        $excludedTables = [
            'migrations',
            'configs',
            'domain_settings',
            'languages',
            'currencies',
            'roles',
            'permissions',
            'menus',
            'system_notices', // Maybe preserving scripts/notices is desired
            'api_configs',
            'personal_access_tokens' // Optional: keep sessions/tokens? user didn't ask but often related to "config" of access. Let's stick to explicit settings.
        ];

        foreach ($tables as $table) {
            $tableName = $table->$keyName;
            
            // Skip excluded tables
            if (in_array($tableName, $excludedTables)) continue;

            DB::table($tableName)->truncate();
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->info('Đang bật lại kiểm tra khóa ngoại...');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        if ($admin && !empty($adminData)) {
            $this->info('Đang khôi phục Admin...');
            // Re-insert admin
            // We use DB table insert to avoid model events or generic 'create' logic that might fail with empty setup
            try {
                DB::table('users')->insert($adminData);
                $this->info("Đã khôi phục Admin ID {$adminData['id']}");
            } catch (\Exception $e) {
                // Try removing ID to just create new
                unset($adminData['id']);
                 try {
                     $newId = DB::table('users')->insertGetId($adminData);
                     $this->info("Đã khôi phục Admin với ID mới: $newId (Do ID cũ bị xung đột)");
                 } catch (\Exception $ex) {
                     $this->error("Lỗi khi khôi phục Admin: " . $ex->getMessage());
                     $this->error("Vui lòng tạo admin mới thủ công!");
                 }
            }
        }

        $this->info('Đang dọn dẹp cache...');
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('view:clear');

        $this->info('HOÀN TẤT! Hệ thống đã được reset.');
    }
}
