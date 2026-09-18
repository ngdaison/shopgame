<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sub_name',
        'image',
        'status',
        'priority',
        'product_type',
        'show_global',
        'cover',
        'price',
        'prizes',
        'descr',
        'invar_id',
        'play_times',
        'display_position',
        'slug',
        'display_mode',
        'robux_type',
        'warranty_hours',
    ];

    protected $casts = [
        'status' => 'boolean',
        'show_global' => 'boolean',
        'prizes' => 'array',
        // 'price' => 'integer',  // Removed because it can store tiered config strings
        'play_times' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = \Illuminate\Support\Str::slug($model->name);
                
                // Simple uniqueness check (can be improved)
                $count = static::where('slug', $model->slug)->where('id', '!=', $model->id ?? 0)->count();
                if ($count > 0) {
                     $model->slug .= '-' . ($count + 1);
                }
            }
        });
    }

    public function groups()
    {
        // Using a manual many-to-many that handles the group_type
        return $this->belongsToMany(Group::class, 'service_category_group', 'service_category_id', 'group_id')
                    ->withPivot('group_type');
    }

    public function serviceCategoryGroups()
    {
        return $this->hasMany(\Illuminate\Support\Facades\DB::table('service_category_group')->getModel() ? 'App\\Models\\ServiceCategoryGroup' : 'App\\Models\\ServiceCategoryGroup', 'service_category_id');
    }
    // Actually, I'll just create a Model for the pivot if I want to use Relationships neatly.
    // Or I'll use a direct accessor that returns the raw pivot data.
    
    public function getPivotGroupsAttribute()
    {
        return \Illuminate\Support\Facades\DB::table('service_category_group')
            ->where('service_category_id', $this->id)
            ->get();
    }

    public function getResolvedGroupsAttribute()
    {
        $pivotRecords = $this->pivot_groups;
            
        $resolved = collect();
        foreach ($pivotRecords as $record) {
            $modelClass = $record->group_type;
            if (class_exists($modelClass)) {
                $actual = $modelClass::find($record->group_id);
                if ($actual) {
                    $resolved->push($actual);
                }
            }
        }
        return $resolved;
    }

    public function inventoryVar()
    {
        return $this->belongsTo(InventoryVar::class, 'invar_id');
    }

    public function canPlay()
    {
        $arr = $this->prizes ?? [];
        $totalPercent = 0;
        foreach ($arr as $item) {
            $totalPercent += $item['percent'] ?? 0;
        }
        return $totalPercent > 0;
    }

    public function playGame($test = false)
    {
        $items         = $this->prizes ?? [];
        $weightedItems = [];

        if ($test) {
            foreach ($items as $index => $item) {
                $items[$index]['percent'] = 80;
            }
        }

        foreach ($items as $index => $item) {
            if ((int) ($item['percent'] ?? 0) === 0) {
                continue;
            }

            for ($i = 0; $i < $item['percent']; $i++) {
                $weightedItems[] = $index;
            }
        }
        
        if (empty($weightedItems)) return null;

        $randomIndex = $weightedItems[array_rand($weightedItems)] + 1;

        $location = null;
        switch ($randomIndex) {
            case '1': $location = 360; break;
            case '2': $location = 320; break;
            case '3': $location = 270; break;
            case '4': $location = 230; break;
            case '5': $location = 180; break;
            case '6': $location = 130; break;
            case '7': $location = 85; break;
            case '8': $location = 44; break;
        }

        return [
            'data'     => $items[$randomIndex - 1] ?? null,
            'location' => $location ?? null,
        ];
    }
}
