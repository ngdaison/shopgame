<?php

namespace App\Models;

use Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollaTransaction extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'username',
    'type',
    'amount',
    'status',
    'reference',
    'description',
    'balance_before',
    'balance_after',
    'payment_info',
    'user_note',
    'sys_note',
    'order_id',
  ];

  protected $casts = [
    'balance_before' => 'integer',
    'balance_after'  => 'integer',
    'payment_info'   => 'array',
  ];

  protected $appends = [
    'prefix', // Add this line
    'formatted_amount',
    'formatted_balance_before',
    'formatted_balance_after',
  ];

  public function getPrefixAttribute() // Add this method
  {
    return $this->balance_after > $this->balance_before ? '+' : '-';
  }

  public function getFormattedAmountAttribute()
  {
    return Helper::formatCurrency($this->amount);
  }

  public function getFormattedBalanceBeforeAttribute()
  {
    return Helper::formatCurrency($this->balance_before);
  }

  public function getFormattedBalanceAfterAttribute()
  {
    return Helper::formatCurrency($this->balance_after);
  }

  public function getStatusHtmlAttribute()
  {
      $status = $this->status;
      $badges = [
          'Pending'   => '<span class="badge bg-warning text-dark">' . __('Chờ xử lý') . '</span>',
          'Completed' => '<span class="badge bg-success">' . __('Hoàn thành') . '</span>',
          'Cancelled' => '<span class="badge bg-danger">' . __('Đã bị hủy') . '</span>',
          'Declined'  => '<span class="badge bg-danger">' . __('Đã bị hủy') . '</span>',
          'Rejected'  => '<span class="badge bg-danger">' . __('Từ chối') . '</span>',
      ];

      return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
  }


  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
