<?php
/**
 * @author baodev@cmsnt.co
 *
 * @version 1.0.1
 */

namespace App\Helpers;

use Error;
use Helper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class Update
{
  private static $api_url;
  private static $hash_key;
  private static $base_path;
  private static $client_key;

  public function __construct()
  {
    self::$api_url    = 'https://updates.baocms.net/shopnickv3/index.php';
    self::$hash_key   = 'e01cc7e1e3957ff1cec61d5de0b8c964';
    self::$base_path  = base_path('devonly');
    self::$client_key = env('CLIENT_SECRET_KEY', 'e01cc7e1e3957ff1cec61d5de0b8c964');
  }

  public static function enableUpdate()
  {
    return env('SERVER_ALLOW_UPDATE', false);
  }

  public static function currentVersion()
  {
    $version = Helper::getConfig('version_code', 1000);

    return $version;
  }

  public static function latestVersion()
  {
    try {
      $response = Http::get(self::$api_url, ['route' => 'check-update', 'hash' => self::$hash_key, 'secret' => self::$client_key]);

      if ($response->successful()) {
        $data = $response->json();

        return $data['data']['version_code'];
      }

      return self::currentVersion();
    } catch (\Throwable $th) {
      return self::currentVersion();
    }
  }

  public static function checkUpdate()
  {
    $latestVersion = self::latestVersion();

    if ($latestVersion > self::currentVersion()) {
      return $latestVersion;
    }

    return 0;
  }

  public static function downloadUpdate()
  {
    if (!self::enableUpdate()) {
      return false;
    }

    if (self::checkUpdate() === null) {
      return false;
    }

    if (!is_dir(self::$base_path)) {
      mkdir(self::$base_path);
    }

    $filename = md5(time() . rand(0, 9999)) . '.zip';

    $response = Http::timeout(120)->get(self::$api_url, [
      'hash'   => self::$hash_key,
      'route'  => 'download-update',
      'secret' => self::$client_key,
    ]);

    if ($response->successful()) {

      $data = $response->json();

      if (isset($data['status']) && $data['status'] === 403) {
        return false;
      }

      $file = self::$base_path . '/' . $filename;

      file_put_contents($file, $response->body());

      return $file;
    }

    return false;
  }

  public static function extractUpdate($file)
  {
    if (!self::enableUpdate()) {
      return false;
    }

    if (!is_file($file)) {
      return false;
    }

    $zip = new \ZipArchive();

    if ($zip->open($file) === true) {
      $zip->extractTo(base_path());
      $zip->close();

      return true;
    }

    return false;
  }

  public static function cleanUpdate()
  {
    if (!self::enableUpdate()) {
      return false;
    }

    if (!is_dir(self::$base_path)) {
      return false;
    }

    $files = glob(self::$base_path . '/*');

    foreach ($files as $file) {
      if (is_file($file)) {
        unlink($file);
      }
    }

    return true;
  }

  public static function runUpdate()
  {
    try {
      // command for update
      $config = \App\Models\Config::where(['name' => 'version_code'])->firstOrNew(['name' => 'version_code']);

      $config->value = self::latestVersion();

      // clear cache
      Artisan::call('cache:clear');
      // clear config
      Artisan::call('config:clear');
      // clear view
      Artisan::call('view:clear');
      // clear route
      Artisan::call('route:clear');
      // clear optimize
      Artisan::call('optimize:clear');
      // regenrate app key
      Artisan::call('key:generate');
      // databases migrate
      Artisan::call('migrate', [
        '--force' => true,
      ]);


      //alter table | add columns
      if (!Schema::hasColumn('groups', 'descr_seo')) {
        Schema::table('groups', function (Blueprint $table) {
          $table->longText('descr_seo')->nullable()->after('descr');
        });
      }

      if (!Schema::hasColumn('groups', 'meta_seo')) {
        Schema::table('groups', function (Blueprint $table) {
          $table->json('meta_seo')->nullable()->after('descr');
        });
      }

      /*
      $table->json('list_skin')->nullable();
      $table->text('raw_skins')->nullable();
      $table->json('list_champ')->nullable();
      $table->text('raw_champions')->nullable();
      */
      if (!Schema::hasColumn('list_items', 'list_skin')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->json('list_skin')->nullable()->after('priority');
        });
      }

      if (!Schema::hasColumn('list_items', 'raw_skins')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->text('raw_skins')->nullable()->after('list_skin');
        });
      }

      if (!Schema::hasColumn('list_items', 'list_champ')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->json('list_champ')->nullable()->after('raw_skins');
        });
      }

      if (!Schema::hasColumn('list_items', 'raw_champions')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->text('raw_champions')->nullable()->after('list_champ');
        });
      }

      if (!Schema::hasColumn('list_items', 'cf_the_loai')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->integer('cf_the_loai')->nullable()->after('raw_champions');
        });
      }

      if (!Schema::hasColumn('list_items', 'cf_vip_ingame')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->integer('cf_vip_ingame')->nullable()->default(0)->after('cf_the_loai');
        });
      }

      if (!Schema::hasColumn('list_items', 'cf_vip_ingame')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->integer('cf_vip_amount')->nullable()->default(0)->after('cf_vip_ingame');
        });
      }

      // alter table list_items add cost double default 0 not null after image;
      if (!Schema::hasColumn('list_items', 'cost')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->double('cost')->default(0)->after('image');
        });
      }

      // alter table transactions add cost_amount int default 0 not null after amount;
      if (!Schema::hasColumn('transactions', 'cost_amount')) {
        Schema::table('transactions', function (Blueprint $table) {
          $table->integer('cost_amount')->default(0)->after('amount');
        });
      }

      // alter table list_items modify highlights json null;
      if (Schema::hasColumn('list_items', 'highlights')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->json('highlights')->nullable()->change();
        });
      }

      // alter table `groups` modify descr longtext null;
      if (Schema::hasColumn('groups', 'descr')) {
        Schema::table('groups', function (Blueprint $table) {
          $table->longText('descr')->nullable()->change();
        });
      }

      // alter table users add balance_2 int default 0 not null after balance_1;
      if (!Schema::hasColumn('users', 'balance_2')) {
        Schema::table('users', function (Blueprint $table) {
          $table->integer('balance_2')->default(0)->after('balance_1');
        });
      }

      /*
      alter table transactions
    modify amount bigint not null;

    alter table transactions
        modify cost_amount bigint default 0 not null;

    alter table transactions
        modify balance_after bigint not null;

    alter table transactions
        modify balance_before bigint not null;
        */

      if (Schema::hasColumn('transactions', 'amount')) {
        Schema::table('transactions', function (Blueprint $table) {
          $table->bigInteger('amount')->change();
        });
      }

      if (Schema::hasColumn('transactions', 'cost_amount')) {
        Schema::table('transactions', function (Blueprint $table) {
          $table->bigInteger('cost_amount')->default(0)->change();
        });
      }

      if (Schema::hasColumn('transactions', 'balance_after')) {
        Schema::table('transactions', function (Blueprint $table) {
          $table->bigInteger('balance_after')->change();
        });
      }

      if (Schema::hasColumn('transactions', 'balance_before')) {
        Schema::table('transactions', function (Blueprint $table) {
          $table->bigInteger('balance_before')->change();
        });
      }

      // alter table invoices modify amount bigint not null;
      if (Schema::hasColumn('invoices', 'amount')) {
        Schema::table('invoices', function (Blueprint $table) {
          $table->bigInteger('amount')->change();
        });
      }

      // invoices
      if (!Schema::hasColumn('invoices', 'trans_id')) {
        Schema::table('invoices', function (Blueprint $table) {
          $table->string('trans_id')->nullable()->after('username');
        });
      }

      // alter table posts add priority int default 0 not null after type;
      if (!Schema::hasColumn('posts', 'priority')) {
        Schema::table('posts', function (Blueprint $table) {
          $table->integer('priority')->default(0)->after('type');
        });
      }

      // alter table invoices add request_id int default 0 not null after currency;
      if (!Schema::hasColumn('invoices', 'request_id')) {
        Schema::table('invoices', function (Blueprint $table) {
          $table->string('request_id')->nullable()->after('currency');
        });
      }

      // alter table users add register_ip varchar(128) null after register_by;
      if (!Schema::hasColumn('users', 'register_ip')) {
        Schema::table('users', function (Blueprint $table) {
          $table->string('register_ip')->nullable()->after('register_by');
        });
      }

      // alter table users add last_action timestamp default current_timestamp() not null after register_ip;
      if (!Schema::hasColumn('users', 'last_action')) {
        Schema::table('users', function (Blueprint $table) {
          $table->timestamp('last_action')->nullable()->after('register_ip');
        });
      }

      // alter table transactions add domain varchar(255) null after username;
      if (!Schema::hasColumn('transactions', 'domain')) {
        Schema::table('transactions', function (Blueprint $table) {
          $table->string('domain')->nullable()->after('username');
        });
      }

      // alter table configs add domain varchar(255) null after value;
      if (!Schema::hasColumn('configs', 'domain')) {
        Schema::table('configs', function (Blueprint $table) {
          $table->string('domain')->nullable()->after('value');
        });
      }

      // remove unique key `name` of configs table
      if (Schema::hasColumn('configs', 'name')) {
        Schema::table('configs', function (Blueprint $table) {
          // check if unique key exists
          $indexExists = collect(DB::select("SHOW INDEXES FROM configs WHERE Key_name = 'configs_name_unique'"))->count();
          if ($indexExists) {
            $table->dropUnique('configs_name_unique');
          }

        });
      }

      // alter table list_items add domain varchar(255) null after price;
      if (!Schema::hasColumn('list_items', 'domain')) {
        Schema::table('list_items', function (Blueprint $table) {
          $table->string('domain')->nullable()->after('price');
        });
      }

      // alter table resource_v2_s add domain varchar(255) null after type;
      if (!Schema::hasColumn('resource_v2_s', 'domain')) {
        Schema::table('resource_v2_s', function (Blueprint $table) {
          $table->string('domain')->nullable()->after('type');
        });
      }

      // alter table notifications add domain varchar(255) null after value;
      if (!Schema::hasColumn('notifications', 'domain')) {
        Schema::table('notifications', function (Blueprint $table) {
          $table->string('domain')->nullable()->after('value');
        });
      }

      // alter table item_data add robux int default 0 not null after price;
      if (!Schema::hasColumn('item_data', 'robux')) {
        Schema::table('item_data', function (Blueprint $table) {
          $table->integer('robux')->default(0)->after('price');
        });
      }

      // alter table item_orders add robux int default 0 not null after data;
      if (!Schema::hasColumn('item_orders', 'robux')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->integer('robux')->default(0)->after('data');
        });
      }

      // alter table item_orders add rate_robux int default 0 not null after payment;
      if (!Schema::hasColumn('item_orders', 'rate_robux')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->integer('rate_robux')->default(0)->after('robux');
        });
      }

      // alter table item_data add ingame_id int null after currency;
      if (!Schema::hasColumn('item_data', 'ingame_id')) {
        Schema::table('item_data', function (Blueprint $table) {
          $table->integer('ingame_id')->nullable()->after('currency');
        });
      }

      // alter table users add colla_type varchar(255) null after role;
      if (!Schema::hasColumn('users', 'colla_type')) {
        Schema::table('users', function (Blueprint $table) {
          $table->string('colla_type')->nullable()->after('role');
        });
      }

      // alter table users add colla_percent int default 0 not null after colla_type;
      if (!Schema::hasColumn('users', 'colla_percent')) {
        Schema::table('users', function (Blueprint $table) {
          $table->integer('colla_percent')->default(0)->after('colla_type');
        });
      }

      // alter table users add colla_balance int default 0 not null after colla_percent;
      if (!Schema::hasColumn('users', 'colla_balance')) {
        Schema::table('users', function (Blueprint $table) {
          $table->integer('colla_balance')->default(0)->after('colla_percent');
        });
      }

      // alter table users add colla_pending int default 0 not null after colla_balance;
      if (!Schema::hasColumn('users', 'colla_pending')) {
        Schema::table('users', function (Blueprint $table) {
          $table->integer('colla_pending')->default(0)->after('colla_balance');
        });
      }

      // alter table users add colla_withdraw int default 0 not null after colla_pending;
      if (!Schema::hasColumn('users', 'colla_withdraw')) {
        Schema::table('users', function (Blueprint $table) {
          $table->integer('colla_withdraw')->default(0)->after('colla_pending');
        });
      }

      // alter table item_orders add assigned_to varchar(255) null after extra_data;
      if (!Schema::hasColumn('item_orders', 'assigned_to')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->string('assigned_to')->nullable()->after('extra_data');
        });
      }

      // alter table item_orders add assigned_note varchar(255) null after assigned_to;
      if (!Schema::hasColumn('item_orders', 'assigned_note')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->string('assigned_note')->nullable()->after('assigned_to');
        });
      }

      // alter table item_orders add assigned_at datetime null after assigned_note;
      if (!Schema::hasColumn('item_orders', 'assigned_at')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->timestamp('assigned_at')->nullable()->after('assigned_note');
        });
      }

      // alter table item_orders add assigned_type varchar(255) null after assigned_at;
      if (!Schema::hasColumn('item_orders', 'assigned_type')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->string('assigned_type')->nullable()->after('assigned_at');
        });
      }

      // alter table item_orders add assigned_compain boolean default 0 not null after assigned_type;
      if (!Schema::hasColumn('item_orders', 'assigned_complain')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->boolean('assigned_complain')->default(false)->after('assigned_type');
        });
      }

      // alter table item_orders add assigned_payment int default -1 not null after assigned_compain;
      if (!Schema::hasColumn('item_orders', 'assigned_payment')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->integer('assigned_payment')->default(-1)->after('assigned_complain');
        });
      }

      // alter table item_orders add assigned_completed datetime null after assigned_payment;
      if (!Schema::hasColumn('item_orders', 'assigned_completed')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->timestamp('assigned_completed')->nullable()->after('assigned_payment');
        });
      }

      // alter table item_orders add assigned_status varchar(255) null after assigned_type;
      if (!Schema::hasColumn('item_orders', 'assigned_status')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->string('assigned_status')->nullable()->after('assigned_type');
        });
      }

      // ---


      // alter table g_b_orders add assigned_to varchar(255) null after admin_note;
      if (!Schema::hasColumn('g_b_orders', 'assigned_to')) {
        Schema::table('g_b_orders', function (Blueprint $table) {
          $table->string('assigned_to')->nullable()->after('admin_note');
        });
      }

      // alter table g_b_orders add assigned_note varchar(255) null after assigned_to;
      if (!Schema::hasColumn('g_b_orders', 'assigned_note')) {
        Schema::table('g_b_orders', function (Blueprint $table) {
          $table->string('assigned_note')->nullable()->after('assigned_to');
        });
      }

      // alter table g_b_orders add assigned_at datetime null after assigned_note;
      if (!Schema::hasColumn('g_b_orders', 'assigned_at')) {
        Schema::table('g_b_orders', function (Blueprint $table) {
          $table->timestamp('assigned_at')->nullable()->after('assigned_note');
        });
      }

      // alter table g_b_orders add assigned_type varchar(255) null after assigned_at;
      if (!Schema::hasColumn('g_b_orders', 'assigned_type')) {
        Schema::table('g_b_orders', function (Blueprint $table) {
          $table->string('assigned_type')->nullable()->after('assigned_at');
        });
      }

      // alter table g_b_orders add assigned_compain boolean default 0 not null after assigned_type;
      if (!Schema::hasColumn('g_b_orders', 'assigned_complain')) {
        Schema::table('g_b_orders', function (Blueprint $table) {
          $table->boolean('assigned_complain')->default(false)->after('assigned_type');
        });
      }

      // alter table g_b_orders add assigned_payment int default -1 not null after assigned_compain;
      if (!Schema::hasColumn('g_b_orders', 'assigned_payment')) {
        Schema::table('g_b_orders', function (Blueprint $table) {
          $table->integer('assigned_payment')->default(-1)->after('assigned_complain');
        });
      }

      // alter table g_b_orders add assigned_completed datetime null after assigned_payment;
      if (!Schema::hasColumn('g_b_orders', 'assigned_completed')) {
        Schema::table('g_b_orders', function (Blueprint $table) {
          $table->timestamp('assigned_completed')->nullable()->after('assigned_payment');
        });
      }

      // alter table g_b_orders add assigned_status varchar(255) null after assigned_type;
      if (!Schema::hasColumn('g_b_orders', 'assigned_status')) {
        Schema::table('g_b_orders', function (Blueprint $table) {
          $table->string('assigned_status')->nullable()->after('assigned_type');
        });
      }

      // alter table spin_quests add invar_id int null after priority;
      if (!Schema::hasColumn('spin_quests', 'invar_id')) {
        Schema::table('spin_quests', function (Blueprint $table) {
          $table->integer('invar_id')->nullable()->after('priority');
        });
      }

      // alter table spin_quests add play_times int default 1 not null after invar_id;
      if (!Schema::hasColumn('spin_quests', 'play_times')) {
        Schema::table('spin_quests', function (Blueprint $table) {
          $table->integer('play_times')->default(1)->after('invar_id');
        });
      }

      // alter table users add received_gift boolean default 0 not null after register_ip;
      if (!Schema::hasColumn('users', 'received_gift')) {
        Schema::table('users', function (Blueprint $table) {
          $table->boolean('received_gift')->default(false)->after('register_ip');
        });
      }

      // alter table spin_quests add category_id int null after play_times;
      if (!Schema::hasColumn('spin_quests', 'category_id')) {
        Schema::table('spin_quests', function (Blueprint $table) {
          $table->integer('category_id')->nullable()->after('play_times');
        });
      }

      // alter table item_groups add login_with json null after username;
      if (!Schema::hasColumn('item_groups', 'login_with')) {
        Schema::table('item_groups', function (Blueprint $table) {
          $table->json('login_with')->nullable()->after('username');
        });
      }

      // alter table item_orders add input_contact varchar(255) null after input_auth;
      if (!Schema::hasColumn('item_orders', 'input_contact')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->string('input_contact')->nullable()->after('input_auth');
        });
      }

      // alter table item_orders add input_ingame_n varchar(255) null after input_ingame;
      if (!Schema::hasColumn('item_orders', 'input_ingame_n')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->string('input_ingame_n')->nullable()->after('input_ingame');
        });
      }


      // alter table spin_quest_logs drop foreign key spin_quest_logs_user_id_foreign;
      try {
        DB::statement("alter table spin_quest_logs drop foreign key spin_quest_logs_user_id_foreign;");
      } catch (\Exception $e) {
        //
      }

      // alter table list_item_v2_s add is_bulk boolean default 0 not null after status;
      if (!Schema::hasColumn('list_item_v2_s', 'is_bulk')) {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
          $table->integer('is_bulk')->default(1)->after('status');
        });
      }

      // alter table resource_v2_s add is_bulk boolean default false not null after domain;
      if (!Schema::hasColumn('resource_v2_s', 'is_bulk')) {
        Schema::table('resource_v2_s', function (Blueprint $table) {
          $table->boolean('is_bulk')->default(false)->after('domain');
        });
      }


      // alter table resource_v2_s add group_id varchar(255) null after is_bulk;
      if (!Schema::hasColumn('resource_v2_s', 'group_id')) {
        Schema::table('resource_v2_s', function (Blueprint $table) {
          $table->string('group_id')->nullable()->after('is_bulk');
        });
      }

      // alter table item_orders add image varchar(255) null after code;
      if (!Schema::hasColumn('item_orders', 'image')) {
        Schema::table('item_orders', function (Blueprint $table) {
          $table->string('image')->nullable()->after('code');
        });
      }


      // edit extra_data column of list_item_v2_s table
      try {
        if (Schema::hasColumn('list_item_v2_s', 'extra_data')) {
          Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->text('extra_data')->nullable()->change();
          });
        }
      } catch (\Exception $e) {
        //
      }

      // query remove all rows domain=Helper::getDomain()
      // DB::table('configs')->where('domain', Helper::getDomain())->delete();
      // DB::table('noticiations')->where('domain', Helper::getDomain())->delete();

      return $config->save();
    } catch (\Exception $th) {
      throw new Error($th->getMessage());
    } finally {
      self::cleanUpdate();
    }
  }
}
