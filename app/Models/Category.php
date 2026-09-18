<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sub_name',
        'slug',
        'image',
        '_lft',
        'status',
        'username',
        'priority',
    ];

    public function gbGroups()
    {
        return $this->morphedByMany(GBGroup::class, 'categoryable');
    }

    public function itemGroups()
    {
        return $this->morphedByMany(ItemGroup::class, 'categoryable');
    }

    public function accountGroups()
    {
        return $this->morphedByMany(Group::class, 'categoryable');
    }

    public function accountV2Groups()
    {
        return $this->morphedByMany(GroupV2::class, 'categoryable');
    }

    public function groups()
    {
        return $this->accountGroups();
    }

    public function spinServices()
    {
        // Custom retrieval via service_category_group where group_type is Category
        return $this->belongsToMany(ServiceCategory::class, 'service_category_group', 'group_id', 'service_category_id')
            ->where('product_type', 'spin')
            ->wherePivot('group_type', self::class);
    }

    public function robuxServices()
    {
        return $this->belongsToMany(ServiceCategory::class, 'service_category_group', 'group_id', 'service_category_id')
            ->where('product_type', 'robux')
            ->wherePivot('group_type', self::class);
    }


    public static function generateSlug($name, $id = null)
    {
        $slug = str()->slug($name);

        $query = self::where('slug', $slug);
        if ($id) {
            $query->where('id', '!=', $id);
        }

        if ($query->exists()) {
            $slug .= '-'.rand(1000, 9999);
        }

        return $slug;
    }

}
