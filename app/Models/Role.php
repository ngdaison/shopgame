<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'permissions', 'level', 'hide_old_history'];
    
    protected $casts = [
        'permissions' => 'array',
        'hide_old_history' => 'date',
    ];

    public static function getPermissionsMap()
    {
        return [
            'Portal CTV (Staff)' => [
                'Bảng Điều Khiển' => [
                    'staff_dashboard_view' => 'xem',
                ],
                'Quản lý đơn hàng' => [
                    'staff_orders_items_view' => 'xem vật phẩm',
                    'staff_orders_items_edit' => 'xử lý vật phẩm',
                    'staff_orders_boostings_view' => 'xem cày thuê',
                    'staff_orders_boostings_edit' => 'xử lý cày thuê',
                    'staff_orders_accounts_view' => 'xem tài khoản',
                ],
                'Quản lý kho nick' => [
                    'staff_products_accounts_view' => 'xem',
                    'staff_products_accounts_add' => 'đăng tài khoản',
                    'staff_products_accounts_edit' => 'cập nhật',
                ],
            ],
            'Portal Đối Tác (Partner)' => [
                'Bảng điều khiển' => [
                    'partner_dashboard_view' => 'xem',
                ],
                'Cài đặt' => [
                    'partner_settings_view' => 'xem',
                    'partner_settings_update' => 'cập nhật',
                ],
                'Thông báo' => [
                    'partner_notices_view' => 'xem',
                    'partner_notices_update' => 'cập nhật',
                ],
            ],
            'Bảng Điều Khiển & Thống Kê' => [
                'admin_dashboard_view' => 'Xem Bảng Điều Khiển Admin',
                'admin_statistical_view' => 'Xem Thống Kê Chiến Dịch',
                'admin_kiyoai_use' => 'Sử dụng KiyoAI Assistant',
            ],
            'Bảo Mật Hệ Thống' => [
                'Cấu hình bảo mật' => [
                    'admin_security_settings_view' => 'xem',
                    'admin_security_settings_update' => 'cập nhật',
                ],
                'Quản lý chặn (Block)' => [
                    'admin_security_block_view' => 'xem',
                    'admin_security_block_add' => 'thêm',
                    'admin_security_block_delete' => 'xóa',
                ],
            ],
            'Cài Đặt Hệ Thống' => [
                'Cài đặt chung' => [
                    'admin_settings_general_view' => 'xem',
                    'admin_settings_general_system_edit' => 'cập nhật hệ thống',
                ],
                'Quản lý tính năng' => [
                    'admin_settings_general_get_gift_edit' => 'rút thưởng miễn phí',
                    'admin_settings_general_affiliate_edit' => 'cấu hình affiliate',
                    'admin_settings_general_ticket_edit' => 'cấu hình ticket',
                ],
                'Thông tin & Liên hệ' => [
                    'admin_settings_general_shop_info_edit' => 'thông tin giới thiệu',
                    'admin_settings_general_social_edit' => 'mạng xã hội',
                    'admin_settings_general_contact_edit' => 'thông tin liên hệ',
                ],
                'Giao diện & Scripts' => [
                    'admin_settings_general_theme_edit' => 'tùy chỉnh giao diện',
                    'admin_settings_general_scripts_edit' => 'header/footer scripts',
                ],
                'Cấu hình API Keys' => [
                    'admin_settings_apis_view' => 'xem',
                    'admin_settings_apis_update' => 'cập nhật',
                ],
                'Cấu hình Thông báo' => [
                    'admin_settings_notices_view' => 'xem',
                    'admin_settings_notices_update' => 'cập nhật',
                ],
                'Tác vụ Tự động (Cron)' => [
                    'admin_automations_view' => 'xem',
                    'admin_automations_run' => 'chạy',
                    'admin_automations_delete' => 'xóa',
                ],
                'Quản lý Tên miền' => [
                    'admin_domain_view' => 'xem',
                    'admin_domain_add' => 'thêm',
                    'admin_domain_edit' => 'sửa',
                    'admin_domain_delete' => 'xóa',
                ],
                'Quản lý Tiền tệ' => [
                    'admin_currency_view' => 'xem',
                    'admin_currency_add' => 'thêm',
                    'admin_currency_edit' => 'sửa',
                    'admin_currency_delete' => 'xóa',
                    'admin_currency_sync_all' => 'đồng bộ tỉ giá',
                ],
                'Quản lý Ngôn ngữ' => [
                    'admin_language_view' => 'xem',
                    'admin_language_add' => 'thêm',
                    'admin_language_edit' => 'sửa',
                    'admin_language_delete' => 'xóa',
                ],
                'Quản lý Ghim (Pin)' => [
                    'admin_pin_groups_view' => 'xem',
                    'admin_pin_groups_add' => 'thêm',
                    'admin_pin_groups_edit' => 'sửa',
                    'admin_pin_groups_delete' => 'xóa',
                ],
            ],
            'Quản Lý Thành Viên' => [
                'admin_users_view' => 'Xem Danh Sách Thành Viên',
                'admin_users_add' => 'Thêm Thành Viên Mới',
                'admin_users_edit' => 'Chỉnh Sửa Thành Viên',
                'admin_users_delete' => 'Xóa Thành Viên',
                'admin_users_update_balance' => 'Cộng/Trừ Tiền Thành Viên',
            ],
            'Quản Lý Vai Trò (Roles)' => [
                'admin_role_view' => 'Xem Danh Sách Role/Quyền',
                'admin_role_add' => 'Thêm Role Mới',
                'admin_role_edit' => 'Chỉnh Sửa Role/Quyền',
                'admin_role_delete' => 'Xóa Role',
            ],
            'Lịch Sử & Logs' => [
                'admin_transactions_view' => 'Xem Lịch Sử Giao Dịch',
                'admin_histories_view' => 'Xem Lịch Sử Hoạt Động',
                'admin_logs_view' => 'Xem Logs Hệ Thống',
            ],
            'Quản Lý Nạp Tiền' => [
                'Nạp Ngân Hàng' => [
                    'admin_deposit_banks_view' => 'xem',
                    'admin_deposit_banks_edit' => 'cập nhật',
                ],
                'Nạp Thẻ Cào' => [
                    'admin_deposit_cards_view' => 'xem',
                    'admin_deposit_cards_edit' => 'cập nhật',
                ],
                'Nạp USDT' => [
                    'admin_deposit_usdt_view' => 'xem',
                    'admin_deposit_usdt_edit' => 'cập nhật',
                ],
                'Nạp Paypal' => [
                    'admin_deposit_paypal_view' => 'xem',
                    'admin_deposit_paypal_edit' => 'cập nhật',
                ],
                'Nạp Perfect Money' => [
                    'admin_deposit_perfect_money_view' => 'xem',
                    'admin_deposit_perfect_money_edit' => 'cập nhật',
                ],
            ],
            'Quản Lý Hoá Đơn' => [
                'admin_invoices_view' => 'Xem Quản Lý Hoá Đơn',
                'admin_invoices_delete' => 'Xóa Hoá Đơn',
            ],
            'Mail Template' => [
                'admin_template_view' => 'Xem Mail Template',
                'admin_template_add' => 'Thêm Mail Template',
                'admin_template_edit' => 'Sửa Mail Template',
                'admin_template_delete' => 'Xóa Mail Template',
            ],
            'Hỗ Trợ (Tickets)' => [
                'admin_tickets_view' => 'Xem Tickets Hỗ Trợ',
                'admin_tickets_reply' => 'Trả Lời Tickets',
                'admin_tickets_close' => 'Đóng Tickets',
                'admin_tickets_delete' => 'Xóa Tickets',
            ],
            'Thông Báo Hệ Thống' => [
                'admin_notifications_view' => 'Xem Thông Báo',
                'admin_notifications_send' => 'Gửi Thông Báo Mới',
                'admin_notifications_delete' => 'Xóa Thông Báo',
            ],
            'Quản lý Bài Viết & Tin Tức' => [
                'admin_posts_view' => 'Xem Danh Sách Bài Viết',
                'admin_posts_add' => 'Thêm Bài Viết Mới',
                'admin_posts_edit' => 'Chỉnh Sửa Bài Viết',
                'admin_posts_delete' => 'Xóa Bài Viết',
            ],
            'Hệ Thống Marketing' => [
                'Mã Giảm Giá' => [
                    'admin_coupons_view' => 'xem',
                    'admin_coupons_add' => 'thêm',
                    'admin_coupons_edit' => 'sửa',
                    'admin_coupons_delete' => 'xóa',
                ],
                'Khuyến Mãi Nạp' => [
                    'admin_promotions_view' => 'xem',
                    'admin_promotions_add' => 'thêm',
                    'admin_promotions_edit' => 'sửa',
                    'admin_promotions_delete' => 'xóa',
                ],
                'Hoa Hồng Affiliate' => [
                    'admin_affiliates_view' => 'xem cấu hình',
                    'admin_withdraws_view' => 'xem yêu cầu rút thưởng',
                    'admin_withdraws_approve' => 'duyệt yêu cầu rút thưởng',
                ],
                'Rút Tiền CTV (Staff)' => [
                    'admin_staff_withdraws_view' => 'xem yêu cầu',
                    'admin_staff_withdraws_approve' => 'duyệt yêu cầu',
                ],
            ],
            'Quản Lý Sản Phẩm & Dịch Vụ' => [
                'Chuyên mục & Dịch vụ' => [
                    'admin_categories_view' => 'xem chuyên mục',
                    'admin_categories_edit' => 'cập nhật chuyên mục',
                    'admin_categories_delete' => 'xóa chuyên mục',
                    'admin_service_view' => 'xem dịch vụ khác',
                    'admin_service_edit' => 'cập nhật dịch vụ khác',
                    'admin_service_delete' => 'xóa dịch vụ khác',
                ],
                'Dịch Vụ Cày Thuê' => [
                    'admin_boosting_groups_view' => 'xem nhóm',
                    'admin_boosting_groups_edit' => 'cập nhật nhóm',
                    'admin_boosting_groups_delete' => 'xóa nhóm',
                    'admin_boosting_orders_view' => 'xem đơn hàng',
                    'admin_boosting_orders_edit' => 'xử lý đơn hàng',
                ],
                'Dịch Vụ Vật Phẩm' => [
                    'admin_items_groups_view' => 'xem nhóm',
                    'admin_items_groups_edit' => 'cập nhật nhóm',
                    'admin_items_groups_delete' => 'xóa nhóm',
                    'admin_items_orders_view' => 'xem đơn hàng',
                    'admin_items_orders_edit' => 'xử lý đơn hàng',
                ],
                'Shop Nick v1' => [
                    'admin_accounts_groups_view' => 'xem nhóm',
                    'admin_accounts_groups_edit' => 'cập nhật nhóm',
                    'admin_accounts_groups_delete' => 'xóa nhóm',
                    'admin_accounts_items_view' => 'xem kho',
                    'admin_accounts_items_add' => 'đăng tài khoản',
                    'admin_accounts_items_edit' => 'cập nhật tài khoản',
                    'admin_accounts_items_delete' => 'xóa tài khoản',
                ],
                'Shop Nick v2' => [
                    'admin_accountsv2_groups_view' => 'xem nhóm',
                    'admin_accountsv2_groups_edit' => 'cập nhật nhóm',
                    'admin_accountsv2_groups_delete' => 'xóa nhóm',
                    'admin_accountsv2_items_view' => 'xem kho',
                    'admin_accountsv2_items_add' => 'đăng tài khoản',
                    'admin_accountsv2_items_edit' => 'cập nhật tài khoản',
                    'admin_accountsv2_items_delete' => 'xóa tài khoản',
                    'admin_accountsv2_orders_view' => 'xem đơn hàng',
                    'admin_accountsv2_api_view' => 'xem cấu hình API',
                    'admin_accountsv2_api_update' => 'cập nhật API',
                ],
            ],
            'Quản Lý Quà Tặng (Gift)' => [
                'Kho Hàng (Vars)' => [
                    'admin_inventories_vars_view' => 'xem',
                    'admin_inventories_vars_add' => 'thêm',
                    'admin_inventories_vars_edit' => 'cập nhật',
                    'admin_inventories_vars_delete' => 'xóa',
                ],
                'Quản lý phần thưởng' => [
                    'admin_inventories_view' => 'xem',
                    'admin_inventories_add' => 'thêm',
                    'admin_inventories_edit' => 'cập nhật',
                    'admin_inventories_delete' => 'xóa',
                ],
            ],
        ];
    }
}
