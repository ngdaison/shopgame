<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceCategory;

class SpinQuestController extends Controller
{
  public function index($id = null)
  {
    if ($id !== null) {
      $spinQuest = ServiceCategory::where('id', $id)
                    ->where('product_type', 'spin')
                    ->where('status', true)
                    ->firstOrFail();

      return view('game.spin-quest.show', [
        'pageTitle' => $spinQuest->name,
      ], compact('spinQuest'));
    }
    
    $spinQuests = ServiceCategory::where('product_type', 'spin')
                  ->where('status', true)
                  ->orderBy('priority', 'desc')
                  ->get();

    return view('game.spin-quest.index', [
      'pageTitle' => 'Vòng quay may mắn',
    ], compact('spinQuests'));
  }
}
