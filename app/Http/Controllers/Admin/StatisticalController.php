<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Campaign;

class StatisticalController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = Campaign::orderBy('created_at', 'DESC')
            ->paginate(20);

        return view('admin.statistical.index', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name'               => 'required|string|max:255',
            'tracking_code'      => 'required|string|unique:campaigns,tracking_code|max:255|regex:/^[a-zA-Z0-9_-]+$/',
            'referral_link'      => 'nullable|string|max:255',
            'commission_type'    => 'required|in:deposit,order',
            'status'             => 'required|boolean',
            'comm_percent'       => 'required|numeric|min:0|max:100',
            'limit_mode'         => 'required|in:count,days,both',
            'limit_days'         => 'nullable|integer|min:0',
            'limit_count'        => 'nullable|integer|min:0',
        ]);

        $payload['type'] = 'affiliate';

        $campaign = Campaign::create($payload);

        return response()->json([
            'status'  => true,
            'message' => 'Tạo chiến dịch thành công!',
            'data'    => $campaign,
        ]);
    }

    public function show($id)
    {
        $campaign = Campaign::findOrFail($id);

        $limitDate = auth()->user()->getHistoryLimitDate();
        
        $clicks = \App\Models\CampaignLog::where('campaign_id', $id)
            ->when($limitDate, function($q) use ($limitDate) {
                return $q->where('created_at', '>=', $limitDate);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'clicks_page');

        $registrations = User::where('campaign_id', $id)
            ->when($limitDate, function($q) use ($limitDate) {
                return $q->where('created_at', '>=', $limitDate);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'users_page');

        $commissions = \App\Models\WalletLog::where('campaign_id', $id)
            ->where('type', 'campaign_commission')
            ->when($limitDate, function($q) use ($limitDate) {
                return $q->where('created_at', '>=', $limitDate);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'comms_page');

        return view('admin.statistical.show', compact('campaign', 'clicks', 'registrations', 'commissions'));
    }

    public function update(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);

        $payload = $request->validate([
            'name'               => 'required|string|max:255',
            'tracking_code'      => 'required|string|max:255|regex:/^[a-zA-Z0-9_-]+$/|unique:campaigns,tracking_code,' . $id,
            'referral_link'      => 'nullable|string|max:255',
            'commission_type'    => 'required|in:deposit,order',
            'status'             => 'required|boolean',
            'comm_percent'       => 'required|numeric|min:0|max:100',
            'limit_mode'         => 'required|in:count,days,both',
            'limit_days'         => 'nullable|integer|min:0',
            'limit_count'        => 'nullable|integer|min:0',
        ]);

        $payload['type'] = 'affiliate';

        $campaign->update($payload);

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật chiến dịch thành công!',
            'data'    => $campaign,
        ]);
    }

    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Xóa chiến dịch thành công!',
        ]);
    }
}
