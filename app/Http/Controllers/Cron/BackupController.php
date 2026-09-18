<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
  public function run()
  { // shopnick2
    $rows = DB::table('users1')->limit(500)->get();

    foreach ($rows as $item) {
      if ($item->banned != 0)
        continue;

      try {
        $exist = User::where('username', $item->username)->first();
        if ($exist) {
          DB::table('users1')->where('id', $item->id)->delete();
          continue;
        }

        $exist = User::where('email', $item->email)->first();
        if ($exist) {
          DB::table('users1')->where('id', $item->id)->delete();
          continue;
        }
        $user = User::create([
          'username'      => $item->username,
          'password'      => bcrypt($item->password),
          'email'         => !empty($item->email) ? $item->email : str()->random(6) . '@network.local',
          'balance'       => $item->money,
          'total_deposit' => $item->money,
          'created_at'    => $item->createdate,
          'referral_code' => str()->random(8),
        ]);

        if ($user) {
          DB::table('users1')->where('id', $item->id)->delete();
        }
      } catch (\Exception $e) {
        echo $e->getMessage() . '<br />';
      }
    }

    return 'rows count: ' . count($rows);
  }
}
