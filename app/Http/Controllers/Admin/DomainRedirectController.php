<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainRedirect;
use App\Models\DomainSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class DomainRedirectController extends Controller
{
    public function index()
    {
        // Group by target for better display
        $redirects = DomainRedirect::with('source', 'target')
            ->get()
            ->groupBy('target_domain_id');
            
        return view('admin.domain.redirects.index', compact('redirects'));
    }

    public function create()
    {
        $domains = DomainSetting::all();
        return view('admin.domain.redirects.create', compact('domains'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_domain_id' => 'required|exists:domain_settings,id',
            'source_domain_ids' => 'required|array|min:1',
            'source_domain_ids.*' => 'exists:domain_settings,id|different:target_domain_id',
        ], [
            'source_domain_ids.*.different' => 'Tên miền nguồn không được trùng với tên miền đích.',
        ]);

        $targetId = $request->target_domain_id;
        $sourceIds = $request->source_domain_ids;

        // Check circular redirects
        foreach ($sourceIds as $sourceId) {
            if ($this->checkCircular($sourceId, $targetId)) {
                throw ValidationException::withMessages([
                    'source_domain_ids' => ['Phát hiện vòng lặp chuyển tiếp (Circular Redirect) với tên miền ID: ' . $sourceId],
                ]);
            }
        }

        DB::transaction(function () use ($targetId, $sourceIds) {
            foreach ($sourceIds as $sourceId) {
                // Update or Create
                DomainRedirect::updateOrCreate(
                    ['source_domain_id' => $sourceId],
                    ['target_domain_id' => $targetId, 'status' => true]
                );
            }
        });

        return redirect()->route('admin.domain.redirects.index')
            ->with('success', 'Đã tạo chuyển tiếp thành công.');
    }

    public function edit($id)
    {
        // $id could be a DomainRedirect ID. We want to edit the "Relationship Group" this ID belongs to.
        $redirect = DomainRedirect::findOrFail($id);
        $targetId = $redirect->target_domain_id;

        // Get all sources for this target
        $currentSourceIds = DomainRedirect::where('target_domain_id', $targetId)
            ->pluck('source_domain_id')
            ->toArray();

        $domains = DomainSetting::all();

        return view('admin.domain.redirects.edit', compact('domains', 'targetId', 'currentSourceIds', 'redirect'));
    }

    public function update(Request $request, $id)
    {
        // We act on the group defined by the target_domain_id in the request, 
        // ignoring the route $id mostly, but validating it exists.
        
        $request->validate([
            'target_domain_id' => 'required|exists:domain_settings,id',
            'source_domain_ids' => 'required|array|min:1',
            'source_domain_ids.*' => 'exists:domain_settings,id|different:target_domain_id',
        ]);

        $newTargetId = $request->target_domain_id;
        $newSourceIds = $request->source_domain_ids;

        // Check circular
        foreach ($newSourceIds as $sourceId) {
            if ($this->checkCircular($sourceId, $newTargetId)) {
                throw ValidationException::withMessages([
                    'source_domain_ids' => ['Phát hiện vòng lặp chuyển tiếp với tên miền ID: ' . $sourceId],
                ]);
            }
        }

        DB::transaction(function () use ($newTargetId, $newSourceIds) {
            // Logic:
            // 1. We are setting the state: "These Sources -> This Target"
            // 2. Any source in $newSourceIds MUST point to $newTargetId.
            // 3. What about sources that WERE pointing to $newTargetId but are NOT in $newSourceIds?
            //    Requirement implies "Update Mode" syncs the sources.
            //    So we should remove redirects for sources that were pointing to this target but are now deselected.
            //    However, if we changed the Target, it gets complicated.
            //    Let's assume the UI locks the "Target" or allows changing it. 
            //    If I change Target A -> B, and keep Sources X, Y.
            //    X, Y now point to B.
            //    What about Z that used to point to A?
            //    If I kept A as target, I would see X, Y, Z. If I deselect Z, Z is deleted.
            //    So, first: Identify the "Previous Target" context needed? 
            //    The route ID is one of the redirects. Let's find the OLD Target from it?
            //    But user might have just picked arbitrary sources.
            
            // Simplified Logic as per requirements "Replace set":
            // "Replace set: remove old sources not in new list" - this refers to sources *for this target*.
            // But if we change the target, we are creating new rules for the NEW target.
            // The requirement says "Target domain: Single Select". "Source domains: Multi Select".
            // Implementation:
            // 1. Update/Create all $newSourceIds to point to $newTargetId.
            // 2. If the user INTENDED to sync the list for $newTargetId, we should remove sources pointing to $newTargetId that are NOT in $newSourceIds.
            //    YES.
            
            // Step A: Remove any redirect pointing to $newTargetId that is NOT in $newSourceIds.
            DomainRedirect::where('target_domain_id', $newTargetId)
                ->whereNotIn('source_domain_id', $newSourceIds)
                ->delete();

            // Step B: Set all $newSourceIds to point to $newTargetId
            foreach ($newSourceIds as $sourceId) {
                DomainRedirect::updateOrCreate(
                    ['source_domain_id' => $sourceId],
                    ['target_domain_id' => $newTargetId, 'status' => true]
                );
            }
        });

        return redirect()->route('admin.domain.redirects.index')
            ->with('success', 'Cập nhật chuyển tiếp thành công.');
    }

    public function destroy($id)
    {
        $redirect = DomainRedirect::findOrFail($id);
        $redirect->delete();
        return back()->with('success', 'Đã xoá chuyển tiếp.');
    }

    protected function checkCircular($sourceId, $targetId)
    {
        // 1. Direct check: target cannot be source
        if ($sourceId == $targetId) return true;

        // 2. Transitive check: 
        // If we make A -> B.
        // We must check if B redirects to A (B->A) or B->C->A.
        // Traverse the chain starting from Target.
        
        $current = $targetId;
        $visited = [$sourceId]; // A is where we start conceptually (Source -> Target)

        while ($redirect = DomainRedirect::where('source_domain_id', $current)->first()) {
            $nextTarget = $redirect->target_domain_id;
            
            if (in_array($nextTarget, $visited)) {
                return true; // Loop detected
            }
            
            $visited[] = $current;
            $current = $nextTarget;
            
            // Safety break
            if (count($visited) > 20) break;
        }

        return false;
    }
}
