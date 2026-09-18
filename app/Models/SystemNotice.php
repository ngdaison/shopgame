<?php

namespace App\Models;

use Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Schema;

class SystemNotice extends Model
{
  use HasFactory;

  protected $table = 'system_notices';

  protected $fillable = [
    'name',
    'type',
    'value',
    'domain'
  ];

  protected $casts = [
    'value' => 'string',
  ];

  // protected static function booted()
  // {
  //   static::addGlobalScope('domain', function ($query) {
  //     if (Schema::hasColumn($query->getModel()->getTable(), 'domain')) {
  //       $query->where('domain', Helper::getDomain());
  //     }
  //   });

  //   static::creating(function ($model) {
  //     if (Schema::hasColumn($model->getTable(), 'domain')) {
  //       $model->domain = Helper::getDomain();
  //     }
  //   });
  // }
}
