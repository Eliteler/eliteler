<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;
 
use App\AiCreditsPlan;
use App\Http\Controllers\Controller;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AiCreditsPlanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $request)
    {
        // Queries
        if ($request->ajax()) {
            $aiPlanCredits = AiCreditsPlan::where('status', '!=', 'deleted')->get();

            // Get all aicredits plans
            return DataTables::of($aiPlanCredits)
                ->addIndexColumn()
                ->addColumn('plan_name', function ($row) {
                    return __($row->plan_name);
                })
                ->addColumn('plan_price', function ($row) {
                    return formatCurrency($row->plan_price);
                })
                ->addColumn('no_of_ai_credits', function ($row) {
                    return $row->no_of_ai_credits;
                })
                ->addColumn('status', function ($row) {
                    switch ($row->status) {
                        case 'inactive':
                            return '<span class="badge bg-warning text-white">' . __('Inactive') . '</span>';
                        case 'active':
                            return '<span class="badge bg-success text-white">' . __('Active') . '</span>';
                        case 'deleted':
                            return '<span class="badge bg-danger text-white">' . __('Deleted') . '</span>';
                        default:
                            return '<span class="badge bg-success text-white">' . __('Active') . '</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('admin.edit.ai.credits.plan', $row->ai_credits_plan_id);
                    $activateDeactivate = $row->status == 'inactive' ? trans('Activate') : trans('Deactivate');
                    $activateDeactivateFunction = $row->status == 'inactive' ? 'activateAiCreditsPlan' : 'deactivateAiCreditsPlan';

                    return '
                        <a class="btn-action" href="#" data-bs-toggle="dropdown" data-bs-placement="bottom" data-bs-auto-close="outside" aria-expanded="false">
                            <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . $editUrl . '">' . __('Edit') . '</a>
                            <a class="dropdown-item" href="#" onclick="' . $activateDeactivateFunction . '(`' . $row->ai_credits_plan_id . '`); return false;">' . __($activateDeactivate) . '</a>
                            <a class="dropdown-item text-danger" href="#" onclick="deleteAiCreditsPlan(`' . $row->ai_credits_plan_id . '`); return false;">' . __('Delete') . '</a>
                        </div>';
                })
                ->rawColumns(['plan_name', 'plan_description', 'no_of_ai_credits', 'status', 'action'])
                ->make(true);
        }

        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        // Return view
        return view('admin.pages.ai-credits.index', compact('settings', 'config'));
    }

    // Create AI Credits Plan
    public function createPlan()
    {
        // Queries
        $config = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        return view('admin.pages.ai-credits.create', compact('settings', 'config'));
    }

    // Save AI Credits Plan
    public function savePlan(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required',
            'plan_description' => 'required',
            'plan_price' => 'required',
            'no_of_ai_credits' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        // Save
        $aiCreditsPlan = new AiCreditsPlan;
        $aiCreditsPlan->ai_credits_plan_id = uniqid('aicp_');
        $aiCreditsPlan->plan_name = $request->plan_name;
        $aiCreditsPlan->plan_description = $request->plan_description;
        $aiCreditsPlan->plan_price = $request->plan_price;
        $aiCreditsPlan->no_of_ai_credits = $request->no_of_ai_credits;
        $aiCreditsPlan->status = 'active';
        $aiCreditsPlan->save();

        return redirect()->route('admin.ai.credits.plans')->with('success', trans('Created!'));
    }

    // Edit AI Credits Plan
    public function editAiCreditsPlan($id)
    {
        // Get AI Credits Plan
        $aiCreditsPlan = AiCreditsPlan::where('ai_credits_plan_id', $id)->first();

        if ($aiCreditsPlan == null) {
            return redirect()->back()->with('failed', trans('Not Found!'));
        } else {
            // Queries
            $settings = Setting::where('status', 1)->first();

            return view('admin.pages.ai-credits.edit', compact('aiCreditsPlan', 'settings'));
        }
    }

    // Update AI Credits Plan
    public function updateAiCreditsPlan(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'plan_name' => 'required',
            'plan_description' => 'required',
            'plan_price' => 'required',
            'no_of_ai_credits' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('failed', $validator->messages()->first())->withInput();
        }

        // Update
        AiCreditsPlan::where('ai_credits_plan_id', $request->id)->update([
            'plan_name' => $request->plan_name,
            'plan_description' => $request->plan_description,
            'plan_price' => $request->plan_price,
            'no_of_ai_credits' => $request->no_of_ai_credits,
            'status' => 'active',
        ]);

        return redirect()->route('admin.ai.credits.plans')->with('success', trans('Updated!'));
    }

    // Status AI Credits Plan
    public function statusAiCreditsPlan()
    {
        // Parameters
        $id = request('id');
        $status = request('action');

        // Check status
        if ($status == 'activate') {
            $status = 'active';
        } else {
            $status = 'inactive';
        }

        // Update status
        AiCreditsPlan::where('ai_credits_plan_id', $id)->update(['status' => $status]);

        return redirect()->route('admin.ai.credits.plans')->with('success', trans('Updated!'));
    }

    // Delete AI Credits Plan
    public function deleteAiCreditsPlan()
    {
        // Parameters
        $id = request('id');

        // Delete
        AiCreditsPlan::where('ai_credits_plan_id', $id)->update(['status' => 'deleted']);

        return redirect()->route('admin.ai.credits.plans')->with('success', trans('Deleted!'));
    }
}
