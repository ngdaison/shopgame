<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainSetting extends Model
{
    protected $table = 'domain_settings';

    protected $fillable = [
        'domain',
        'is_redirect',
        'redirect_to',
        'language_id',
        'currency_id',
        'logo_light',
        'logo_dark',
        'favicon',
        'logo_share',
        'default_theme',
        'title',
        'description',
        'keywords',
        'admin_email',
        'banner',
        'youtube_id',
        'background_image_url',
        'primary_color',
        'font',
        'intro_text',
        'buy_button_text',
        'buy_button_image',
        'show_banner_top',
        'show_run_notify',
        'analytics_tags',
        'social_config',
        'footer_text_1',
        'footer_text_2',
        'dashboard_text_1',
        'notice_homepage',
        'notice_featured_homepage',
        'email_app_name',
        'fake_top_deposit',
    ];

    protected $casts = [
        'is_redirect' => 'boolean',
        'redirect_to' => 'array',
        'social_config' => 'array',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class , 'language_id');
    }

    public function sourceRedirect()
    {
        return $this->hasOne(DomainRedirect::class , 'source_domain_id');
    }

    public function targetRedirects()
    {
        return $this->hasMany(DomainRedirect::class , 'target_domain_id');
    }
}
