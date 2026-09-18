<?php

namespace App\Models;

use Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Schema;

class Config extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'value',
    'domain'
  ];

  protected $casts = [
    'value' => 'array',
  ];

  // boot where with domain
  protected static function booted()
  {
    static::addGlobalScope('domain', function ($query) {
      if (Schema::hasColumn($query->getModel()->getTable(), 'domain')) {
        // $query->where('domain', Helper::getDomain());
      }
    });

    static::creating(function ($model) {
      if (Schema::hasColumn($model->getTable(), 'domain')) {
        // $model->domain = Helper::getDomain();
      }
    });
  }
}
