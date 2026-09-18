<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConfigsAndSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate existing tables
        $tables = [
            'configs',
            'domain_settings',
            'api_configs',
            'bank_config',
            'paypal_config',
            'perfect_money_config',
            'usdt_config',
            'security_settings',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();

        // api_configs
        DB::table('api_configs')->insert(['id' => 1, 'name' => 'auth_facebook', 'value' => '""', 'created_at' => '2026-01-07 13:18:59', 'updated_at' => '2026-01-07 13:18:59']);
        DB::table('api_configs')->insert(['id' => 2, 'name' => 'auth_google', 'value' => '""', 'created_at' => '2026-01-07 13:18:59', 'updated_at' => '2026-01-07 13:18:59']);
        DB::table('api_configs')->insert(['id' => 3, 'name' => 'charging_card', 'value' => '{"status":"on","api_url":"https:\/\/thesieure.com\/","partner_id":"24661069318","partner_key":"99735823ccb1dd0567975f99ca4ff902","fee":"0","types_list":"Viettel|VIETTEL|20|10000,20000,30000,40000,50000,60000,100000,500000,1000000,2000000:50\r\nVinaphone|VINAPHONE|0|10000:20\r\nMobifone|MOBIFONE|5|2000 ,50000\r\nVietnamobile|VNMOBI\r\nZing|ZING\r\nVCOIN|Vcoin\r\nGARENA|Garena","note":"<p style=\"text-align:center\"><span style=\"font-size:22px\"><strong>Vui l&ograve;ng chuy&ecil;n &dstrok;&uacute;ng n&period;i dung, kh&ocirc;ng s&period;e d&period;eung Cards , t&period;e d&period;eung c&period;&dstrok;ng ti&period;en trong 5<\/strong><\/span><\/p>\r\n\r\n<p style=\"text-align:center\"><br \/>\r\n<span style=\"font-size:18px\"><strong>Discord <span style=\"color:#e74c3c\">Support&nbsp;<\/span>n&ecil;u ch&period;ea &dstrok;&period;eeoc c&period;&dstrok;ng ti&period;en :&nbsp;<a href=\"https:\/\/discord.gg\/gbhAcrmKbq\"><span style=\"color:#3498db\">Discord<\/span><\/a><a href=\"https:\/\/discord.gg\/gbhAcrmKbq\"><span style=\"color:#3498db\">&nbsp;<\/span><\/a><br \/>\r\nFacebook <span style=\"color:#e74c3c\">Support&nbsp;<\/span>n&ecil;u ch&period;ea &dstrok;&period;eeoc c&period;&dstrok;ng ti&period;en :&nbsp;<\/strong><a href=\"https:\/\/www.facebook.com\/kiyovn.vn\"><span style=\"color:#3498db\"><strong>Facebook<\/strong><\/span><\/a><\/span><\/p>","fees":{"VIETTEL":20,"VINAPHONE":0,"MOBIFONE":5,"VIETNAMOBILE":0,"ZING":0,"VCOIN":0,"GARENA":0},"mapping":{"VIETTEL":"VIETTEL","VINAPHONE":"VINAPHONE","MOBIFONE":"MOBIFONE","VIETNAMOBILE":"VIETNAMOBILE","ZING":"ZING","VCOIN":"VCOIN","GARENA":"GARENA"},"specific_fees":{"VIETTEL":{"2000000":50},"VINAPHONE":{"10000":20}},"allowed_denominations":{"VIETTEL":[10000,20000,30000,40000,50000,60000,100000,500000,1000000,2000000],"VINAPHONE":[10000],"MOBIFONE":[2000,50000]}}', 'created_at' => '2026-01-07 13:19:37', 'updated_at' => '2026-01-15 14:07:16']);
        DB::table('api_configs')->insert(['id' => 4, 'name' => 'smtp_detail', 'value' => '{"host":"smtp.gmail.com","port":"587","user":"ctkiyovn@gmail.com","pass":"gbfqozpbigpybvka"}', 'created_at' => '2026-01-07 15:09:59', 'updated_at' => '2026-01-08 12:12:42']);
        DB::table('api_configs')->insert(['id' => 5, 'name' => 'paypal', 'value' => '{"exchange":"23000","client_id":"1","client_secret":"1"}', 'created_at' => '2026-01-08 07:39:52', 'updated_at' => '2026-01-11 12:57:30']);
        DB::table('api_configs')->insert(['id' => 6, 'name' => 'fpayment', 'value' => '{"status":"1","merchant_id":"23","api_token":"123","min":"0","max":"100000000","exchange":"23000","note":null,"type":"FPAYMENT.NET | TRC20, BEP20, POLYGON, SOLANA"}', 'created_at' => '2026-01-08 17:03:43', 'updated_at' => '2026-01-12 08:35:41']);
        DB::table('api_configs')->insert(['id' => 7, 'name' => 'perfect_money', 'value' => '{"exchange":"24000","account_id":"123","passphrase":"123"}', 'created_at' => '2026-01-08 17:03:59', 'updated_at' => '2026-01-08 17:03:59']);

        // bank_config
        DB::table('bank_config')->insert([
            ['id' => 1, 'config' => '{"prefix":"LO","discount":"0","status":1,"description":"","min":"0","max":"1000000000"}', 'bank_accounts' => '[{"image":"\/uploads\/15-01-2026\/72b2fd5a-3dd9-468e-af4c-ed51fe222c42.png","owner":"NGUYEN DUC DAI SON","number":"2906062050","password":"060610@Son","token":"7fc08204f06fce656de3dfb8e755899b","provider":"stc","bank_code":"MBBank","status":true,"bank_name":"MBBank","id":"0fb7c523-fc90-4325-8666-306180fb9235","created_at":"2026-01-15 19:45:41"}]', 'created_at' => '2026-01-15 12:31:58', 'updated_at' => '2026-01-25 08:53:09'],
        ]);

        // configs
        DB::table('configs')->insert([
            ['id' => 1, 'name' => 'general', 'value' => '{"allowed_domains":"localhost ,127.0.0.1","default_theme":"light","font_family":"Signika","primary_color":"#000000","youtube_id":null,"title":null,"description":null,"keywords":null,"captcha":"0","upload_provider":"public","captcha_site_key":null,"captcha_secret_key":null,"time_wait_free":null,"max_ip_reg":null,"rate_robux":"100|140,500|138","default_language_id":null,"default_currency_id":null,"logo_light":"\/uploads\/07-01-2026\/87dd207c-46c5-4e6a-97fb-99a6e2264e86.png","logo_dark":"\/uploads\/07-01-2026\/b44f0a49-3d04-4216-be46-2eb6c2d3c75e.png","favicon":"\/uploads\/07-01-2026\/e3bf8732-0913-4a57-a1f8-b6accd9fd8ff.png","logo_share":"\/uploads\/07-01-2026\/360259f2-37c8-4d27-8047-c8803d7b8543.png","banner":"\/uploads\/07-01-2026\/ba673ca5-361d-4833-8f1c-9aac0be5ebe4.png","background_image_url":"\/uploads\/07-01-2026\/61cfd8d0-1c3e-4c03-a8f6-6bdc22c07634.png"}', 'domain' => 'localhost', 'created_at' => '2026-01-07 13:05:47', 'updated_at' => '2026-01-19 11:03:38'],
            ['id' => 2, 'name' => 'theme_custom', 'value' => '{"card_stats":"1","product_info_type":"0","buy_button_img":"0","enable_custom_theme":"1","show_thongbao":"1","show_lsmua":"0","show_banner":"1","show_all_account_img":"1","minigame_show_value":"1","pin_type":"slide"}', 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-20 12:36:15'],
            ['id' => 3, 'name' => 'banner', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-07 13:16:31'],
            ['id' => 4, 'name' => 'shop_info', 'value' => '{"footer_text_1":"<p>H\\u1ec6 TH\\u1ed0NG B&Aacute;N ACC T\\u1ef0 \\u0110\\u1ed8NG \\u0110\\u1ea2M B\\u1ea2O UY T&Iacute;N V&Agrave; CH\\u1ea4T L\\u01af\\u1ee2NG.<\/p>  <p>Ch&uacute;ng t&ocirc;i lu&ocirc;n l\\u1ea5y uy t&iacute;n \\u0111\\u1eb7t tr&ecirc;n h&agrave;ng \\u0111\\u1ea7u \\u0111\\u1ed1i v\\u1edbi kh&aacute;ch h&agrave;ng, hy v\\u1ecdng ch&uacute;ng t&ocirc;i s\\u1ebd \\u0111\\u01b0\\u1ee3c ph\\u1ee5c v\\u1ee5 c&aacute;c b\\u1ea1n. C&aacute;m \\u01a1n!<\/p>","footer_text_2":"<p><strong><span style=\"font-size:16px\"><span style=\"color:#3498db\">-&nbsp;<\/span><a href=\"https:\/\/fpt.com\/vi\/ve-fpt\/thong-diep-chu-tich\"><span style=\"color:#3498db\">Th&ocirc;ng \\u0111i\\u1ec7p Ch\\u1ee7 t\\u1ecbch H\\u0110QT<\/span><\/a><br \/> <span style=\"color:#3498db\">-&nbsp;<\/span><a href=\"https:\/\/fpt.com\/vi\/ve-fpt\/thong-diep-tong-giam-doc\"><span style=\"color:#3498db\">Th&ocirc;ng \\u0111i\\u1ec7p T\\u1ed5ng gi&aacute;m \\u0111\\u1ed1c<\/span><\/a><br \/> <span style=\"color:#3498db\">-&nbsp;<\/span><a href=\"https:\/\/fpt.com\/vi\/ve-fpt\/gia-tri-cot-loi\"><span style=\"color:#3498db\">Gi&aacute; tr&period; c&period;et l&otilde;i<\/span><\/a><br \/> <span style=\"color:#3498db\">-&nbsp;<\/span><a href=\"https:\/\/fpt.com\/vi\/ve-fpt\/lich-su\"><span style=\"color:#3498db\">L&period;ich s&period;ee<\/span><\/a><br \/> <span style=\"color:#3498db\">-&nbsp;<\/span><a href=\"https:\/\/fpt.com\/vi\/ve-fpt\/tam-nhin-chien-luoc\"><span style=\"color:#3498db\">T&period;am nh&igrave;n chi&ecil;fn l&period;eeoc<\/span><\/a><br \/> <span style=\"color:#3498db\">-&nbsp;<\/span><a href=\"https:\/\/fpt.com\/vi\/ve-fpt\/mang-luoi-hoat-dong\"><span style=\"color:#3498db\">M&period;ang l&period;eeoi ho&period;at &dstrok;&period;eng<\/span><\/a><br \/> <span style=\"color:#3498db\">-&nbsp;<\/span><a href=\"https:\/\/fpt.com\/vi\/ve-fpt\/doi-ngu-lanh-dao\"><span style=\"color:#3498db\">&Dstrok;&period;ei ng&period;eo l&atilde;nh &dstrok;&period;ao<\/span><\/a><\/span><\/strong><\/p>","dashboard_text_1":"Shop Robux uy t&iacute;n top 1 Vi&ecil;t Nam chuy&ecil;n cung c&period;ap Robux gi&aacute; si&ecil;u r&period;e, acc Robox VIP t&period;ee ph&period;e th&ocirc;ng &dstrok;&ecil;n si&ecil;u hi&ecil;m, d&period;ich v&period;e n&period;ap h&period;e nhanh nh&period;at th&period;id tr&period;eeong, giao d&period;ich t&period;e &dstrok;&period;eng 24\/7 c&period;eec k&period;id an to&agrave;n, b&period;ao m&period;at tuy&ecil;t &dstrok;&period;ei th&ocirc;ng tin kh&aacute;ch h&agrave;ng, cam k&ecil;t ho&agrave;n ti&period;en n&ecil;u l&period;ei, &period;eeu &dstrok;&atilde;i kh&period;eng m&period;ei ng&agrave;y, h&period;e tr&period;e t&period;an t&period;am, &dstrok;&period;eng h&agrave;nh c&period;eng h&agrave;ng ch&period;eec ng&agrave;n game th&period;e &dstrok;am m&ecil; Robox x&period;ay d&period;eeng th&ecil; gi&period;ei &period;ao &dstrok;&period;inh cao nh&period;at m&period;ei th&period;id &dstrok;&period;ai!"}', 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-11 17:56:58'],
            ['id' => 5, 'name' => 'description', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-07 13:16:31'],
            ['id' => 6, 'name' => 'keywords', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-07 13:16:31'],
            ['id' => 7, 'name' => 'author', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-07 13:16:31'],
            ['id' => 8, 'name' => 'title', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-07 13:16:31'],
            ['id' => 9, 'name' => 'logo_share', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-07 13:16:31'],
            ['id' => 10, 'name' => 'favicon', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:16:31', 'updated_at' => '2026-01-07 13:16:31'],
            ['id' => 11, 'name' => 'logo_light', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:17:24', 'updated_at' => '2026-01-07 13:17:24'],
            ['id' => 12, 'name' => 'logo_dark', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:17:24', 'updated_at' => '2026-01-07 13:17:24'],
            ['id' => 13, 'name' => 'deposit_port', 'value' => '{"cards":1,"bank":1,"invoice":"1","crypto":1,"paypal":1,"perfect_money":1}', 'domain' => null, 'created_at' => '2026-01-07 13:17:24', 'updated_at' => '2026-01-12 08:35:09'],
            ['id' => 14, 'name' => 'contact_info', 'value' => '{"email":null,"twitter":null,"discord":null,"facebook":"https:\/\/www.facebook.com\/","telegram":null,"phone_no":"0369575506","instagram":null}', 'domain' => null, 'created_at' => '2026-01-07 13:17:24', 'updated_at' => '2026-01-11 18:53:37'],
            ['id' => 15, 'name' => 'get_gift', 'value' => '{"min":"0","max":"0","width":"0","image":null,"status":"0","balance":"0"}', 'domain' => null, 'created_at' => '2026-01-07 13:17:24', 'updated_at' => '2026-01-11 14:29:45'],
            ['id' => 16, 'name' => 'deposit_info', 'value' => '{"prefix":"LO","discount":"0","status":1,"description":"","min":"0","max":"1000000000"}', 'domain' => null, 'created_at' => '2026-01-07 13:19:37', 'updated_at' => '2026-01-25 08:53:09'],
            ['id' => 17, 'name' => 'version_code', 'value' => '1000', 'domain' => null, 'created_at' => '2026-01-07 13:20:57', 'updated_at' => '2026-01-07 13:20:57'],
            ['id' => 18, 'name' => 'telegram_config', 'value' => null, 'domain' => null, 'created_at' => '2026-01-07 13:32:08', 'updated_at' => '2026-01-07 13:32:08'],
            ['id' => 19, 'name' => 'affiliate_config', 'value' => '{"_token":"Ploye8c7d6dNxoDUPoaY0eJByfly01YQ2g7QjkBY","min_withdraw":"1000","max_withdraw":"100000","withdraw_status":"1","comm_percent":"5","commission_type":"order","limit_mode":"days","limit_count":"1","limit_days":"5","type":"affiliate_config"}', 'domain' => null, 'created_at' => '2026-01-07 13:32:08', 'updated_at' => '2026-01-07 17:08:02'],
            ['id' => 20, 'name' => 'mng_withdraw', 'value' => '"{\"unit\":\"Robux\"}"', 'domain' => null, 'created_at' => '2026-01-07 15:09:14', 'updated_at' => '2026-01-07 15:15:00'],
            ['id' => 21, 'name' => 'paypal_info', 'value' => '{"status":"1","rate":"23000","client_id":null,"client_secret":null,"note":null}', 'domain' => null, 'created_at' => '2026-01-12 05:23:50', 'updated_at' => '2026-01-12 05:24:00'],
            ['id' => 22, 'name' => 'perfect_money_info', 'value' => '{"status":"1","rate":"23000","currency":null,"account_id":null,"passphrase":null,"note":null}', 'domain' => null, 'created_at' => '2026-01-12 05:24:15', 'updated_at' => '2026-01-12 05:24:30'],
            ['id' => 23, 'name' => 'ticket_config', 'value' => '{"ticket_categories":"123 ,Ch? &dstrok;&ecil; Ticket ,M&period;ei d&period;ing 1 ch&period;e &dstrok;&ecil;"}', 'domain' => null, 'created_at' => '2026-01-15 16:07:04', 'updated_at' => '2026-01-15 16:23:02'],
        ]);

        // domain_settings
        DB::table('domain_settings')->insert([
            ['id' => 1, 'domain' => 'localhost', 'is_redirect' => 0, 'redirect_to' => '[]', 'language_id' => null, 'currency_id' => null, 'status' => 1, 'created_at' => '2026-01-08 23:57:05', 'updated_at' => '2026-01-08 23:57:05', 'logo_light' => null, 'logo_dark' => null, 'favicon' => null, 'logo_share' => null, 'banner' => null, 'background_image_url' => null, 'title' => null, 'description' => null, 'keywords' => null, 'primary_color' => null, 'youtube_id' => null, 'admin_email' => null, 'upload_provider' => 'public', 'captcha' => 0, 'captcha_site_key' => null, 'captcha_secret_key' => null, 'time_wait_free' => 0, 'max_ip_reg' => 0, 'rate_robux' => 0, 'default_theme' => 'light'],
            ['id' => 2, 'domain' => '127.0.0.1', 'is_redirect' => 0, 'redirect_to' => '[]', 'language_id' => null, 'currency_id' => null, 'status' => 1, 'created_at' => '2026-01-08 23:58:15', 'updated_at' => '2026-01-08 23:58:52', 'logo_light' => null, 'logo_dark' => null, 'favicon' => null, 'logo_share' => null, 'banner' => '/uploads/09-01-2026/8ac8aaf1-ffe5-438b-8df3-0d5c2c84c80c.png', 'background_image_url' => null, 'title' => null, 'description' => null, 'keywords' => null, 'primary_color' => '#000000', 'youtube_id' => null, 'admin_email' => null, 'upload_provider' => 'public', 'captcha' => 0, 'captcha_site_key' => null, 'captcha_secret_key' => null, 'time_wait_free' => 0, 'max_ip_reg' => 0, 'rate_robux' => 0, 'default_theme' => 'light'],
        ]);

        // paypal_config
        DB::table('paypal_config')->insert([
            ['id' => 1, 'config' => '{"status":"1","rate":"23000","client_id":"123","client_secret":"123","note":null}', 'paypal_accounts' => null, 'created_at' => '2026-01-15 14:45:58', 'updated_at' => '2026-01-23 18:44:12'],
        ]);

        // perfect_money_config
        DB::table('perfect_money_config')->insert([
            ['id' => 1, 'config' => '{"status":"1","rate":"23000","currency":null,"account_id":"123","passphrase":"123","note":null}', 'perfect_money_accounts' => null, 'created_at' => '2026-01-15 14:45:58', 'updated_at' => '2026-01-23 18:44:45'],
        ]);

        // usdt_config
        DB::table('usdt_config')->insert([
            ['id' => 1, 'config' => '{"status":"1","merchant_id":"23","api_token":"123","min":"0","max":"100000000","exchange":"23000","note":null,"type":"FPAYMENT.NET | TRC20, BEP20, POLYGON, SOLANA"}', 'usdt_accounts' => null, 'created_at' => '2026-01-15 14:45:58', 'updated_at' => '2026-01-15 14:45:58'],
        ]);

        // security_settings
        DB::table('security_settings')->insert([
            ['id' => 1, 'setting_key' => 'security_bruteforce_rules', 'setting_value' => '{"login_ip":{"label":"Kh&oacute;a IP n&ecil;u sai m&period;at kh&period;au qu&aacute; nhi&ecil;u l&period;an","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"login_acc":{"label":"Kh&oacute;a t&agrave;i kho&period;an n&ecil;u sai m&period;at kh&period;au qu&aacute; nhi&ecil;u l&period;an","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"api_key":{"label":"Kh&oacute;a IP n&ecil;u sai API KEY qu&aacute; nhi&ecil;u l&period;an","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"2fa":{"label":"Kh&oacute;a IP n&ecil;u sai m&atilde; 2FA qu&aacute; nhi&ecil;u l&period;an","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"otp":{"label":"Kh&oacute;a IP n&ecil;u sai m&atilde; OTP qu&aacute; nhi&ecil;u l&period;an (C&period;eea s&period;e 30 ph&uacute;t)","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"recovery":{"label":"Kh&oacute;a IP n&ecil;u y&ecil;u c&period;au kh&period;ei ph&period;ec m&period;at kh&period;au qu&aacute; nhi&ecil;u l&period;an (C&period;eea s&period;e 30 ph&uacute;t)","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"spam_products":{"label":"Kh&oacute;a IP n&ecil;u spam t&period;ai danh s&aacute;ch s&period;an ph&period;am qu&aacute; nhi&ecil;u l&period;an","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"cron_key":{"label":"Kh&oacute;a IP n&ecil;u sai kh&oacute;a Cron Job qu&aacute; nhi&ecil;u l&period;an","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"create_acc":{"label":"Kh&oacute;a IP n&ecil;u c&period;id g&period;ang t&period;ao t&agrave;i kho&period;an qu&aacute; nhi&ecil;u l&period;an (C&period;eea s&period;e 30 ph&uacute;t)","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"change_gmail":{"label":"Kh&oacute;a IP n&ecil;u y&ecil;u c&period;au &dstrok;&period;ei gmail qu&aacute; nhi&ecil;u l&period;an (C&period;eea s&period;e 30 ph&uacute;t)","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"change_gmail_dup":{"label":"Kh&oacute;a IP n&ecil;u y&ecil;u c&period;au &dstrok;&period;ei gmail tr&period;eng qu&aacute; nhi&ecil;u l&period;an trong 30 ph&uacute;t","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"tickets":{"label":"Kh&oacute;a IP n&ecil;u t&period;ao y&ecil;u c&period;au h&period;e tr&period;e (Ticket) qu&aacute; nhi&ecil;u l&period;an (C&period;eea s&period;e 60 ph&uacute;t)","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"},"ticket_msg":{"label":"Kh&oacute;a IP n&ecil;u g&period;edi qu&aacute; nhi&ecil;u tin nh&period;an ticket (C&period;eea s&period;e 1 ph&uacute;t)","max_attempts":"5","window_minutes":"10","ban_action":"ban_1_day"}}', 'updated_at' => '2026-01-25 03:04:34'],
            ['id' => 2, 'setting_key' => 'security_access_control', 'setting_value' => '{"admin_unauth_ban":{"duration":"15"}}', 'updated_at' => '2026-01-25 03:04:34'],
            ['id' => 3, 'setting_key' => 'security_other', 'setting_value' => '{"max_acc_per_ip":null,"session_duration":null,"cron_key":"1","api_key":"kTZWrhzk6Pikv9yf6aUtE1yZR0nAYNCP"}', 'updated_at' => '2026-01-25 03:04:34'],
            ['id' => 4, 'setting_key' => 'security_captcha', 'setting_value' => '{"provider":"none","site_key":null,"secret_key":null}', 'updated_at' => '2026-01-25 03:04:34'],
        ]);
    }
}
