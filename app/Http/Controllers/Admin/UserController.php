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

use App\User;
use App\Setting;
use App\Classes\CreateUser;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
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

    // All Users
    public function users(Request $request)
    {
        if ($request->ajax()) {
            $data = User::where('id', '!=', '1')->where('role_id', '!=', '2')->get();

            return DataTables::of($data)
                ->addIndexColumn('id')
                ->addColumn('role', function ($row) {
                    if ($row->role_id == 3) {
                        return __('Administrator');
                    } else {
                        return __('Manager');
                    }
                })
                ->addColumn('name', function ($row) {
                    $viewUrl = route('admin.view.user', $row->user_id);
                    return '<a href="' . $viewUrl . '">' . $row->name . '</a>';
                })
                ->addColumn('email', function ($row) {
                    return $row->email;
                })
                ->addColumn('created_at', function ($row) {
                    return formatDateForUser($row->created_at);
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 0
                        ? '<span class="badge bg-red text-white">' . __('Deactive') . '</span>'
                        : '<span class="badge bg-green text-white">' . __('Active') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('admin.edit.user', $row->user_id);
                    $activateDeactivate = $row->status == 0 ? trans('Activate') : trans('Deactivate');
                    $activateDeactivateFunction = $row->status == 0 ? 'activateUser' : 'deactivateUser';
                
                    return '
                        <a class="btn-action" href="#" role="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false">
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
                            <a class="dropdown-item" href="#" onclick="' . $activateDeactivateFunction . '(`' . $row->user_id . '`); return false;">' . __($activateDeactivate) . '</a>
                            <a class="dropdown-item text-danger" href="#" onclick="deleteUser(`' . $row->user_id . '`); return false;">' . __('Delete') . '</a>
                        </div>';
                })                
                ->rawColumns(['name', 'plan', 'status', 'action'])
                ->make(true);
        }

        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.users.index', compact('settings', 'config'));
    }

    // Create User
    public function createUser()
    {
        // Queries
        $config = DB::table('config')->get();
        $settings = Setting::where('status', 1)->first();

        return view('admin.pages.users.create', compact('settings', 'config'));
    }

    // Save User
    public function saveUser(Request $request)
    {
        // Queries
        $emailExists = User::where('email', $request->email)->count();

        if ($emailExists != 1) {
            // Save
            $user = new CreateUser;
            $user->create($request);

            // Check result
            if ($user == true) {
                return redirect()->route('admin.create.user')->with('success', trans('Created!'));
            } else {
                return redirect()->route('admin.create.user')->with('failed', trans('There is an error in the create.'));
            }
        } else {
            return redirect()->route('admin.create.user')->with('failed', trans('This email address already registered. Try to another email address.'));
        }
    }

    // View User
    public function viewUser(Request $request, $id)
    {
        // Queries
        $user_details = User::where('user_id', $id)->first();

        if ($user_details == null) {
            return redirect()->route('admin.users')->with('failed', trans('Not Found!'));
        } else {
            $settings = Setting::where('status', 1)->first();

            return view('admin.pages.users.view', compact('user_details', 'settings'));
        }
    }

    // Edit User
    public function editUser(Request $request, $id)
    {
        // Queries
        $user_details = User::where('user_id', $id)->first();
        $settings = Setting::where('status', 1)->first();

        if ($user_details == null) {
            return redirect()->route('admin.users')->with('failed', trans('Not Found!'));
        } else {
            return view('admin.pages.users.edit', compact('user_details', 'settings'));
        }
    }

    // Update User
    public function updateUser(Request $request)
    {
        // Queries
        $emailExists = User::where('email', $request->email)->count();
        $user_details = User::where('user_id', $request->user_id)->first();

        if ($emailExists != 1 || $request->email == $user_details->email) {

            if ($request->password == null) {
                // Validate
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'role' => 'required',
                    'name' => 'required',
                    'email' => 'required'
                ]);

                if ($validator->fails()) {
                    return back()->with('failed', $validator->messages()->all()[0])->withInput();
                }

                // Update
                User::where('user_id', $request->user_id)->update([
                    'role_id' => $request->role,
                    'name' => $request->name,
                    'email' => $request->email
                ]);
            } else {
                // Validate
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'role' => 'required',
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required'
                ]);

                if ($validator->fails()) {
                    return back()->with('failed', $validator->messages()->all()[0])->withInput();
                }

                // Update
                User::where('user_id', $request->user_id)->update([
                    'role_id' => $request->role,
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password)
                ]);
            }

            // List of permissions to check
            $fields = [
                'themes', 'plans', 'customers', 'payment_methods', 'coupons', 'currencies', 'transactions',
                'web_templates', 'blogs', 'users', 'custom_domain', 'backup', 'general_settings', 'translations',
                'marketing', 'sitemap', 'invoice_tax', 'maintenance_mode', 'demo_mode', 'software_update',
                'nfc_card_design', 'nfc_card_orders', 'nfc_card_order_transactions', 'nfc_card_key_generations', 'referral_system', 'email_templates', 'plugins', 'in_app_purchases', 'vcard_store',
                'business_card_intros', 'ai_credits'
            ];

            // Initialize an empty array for permissions
            $permissions = [];

            // Loop through each field and set the corresponding value in the permissions array
            foreach ($fields as $field) {
                // Check if the field exists in the request and handle its value
                $permissions[$field] = $request->has($field) && $request->$field === 'on' ? 1 : 0;
            }

            // Convert the permissions array to a JSON string
            $permissionsJson = json_encode($permissions);

            // Update
            User::where('user_id', $request->user_id)->update([
                'permissions' => $permissionsJson
            ]);

            return redirect()->route('admin.edit.user', $request->user_id)->with('success', trans('Updated!'));
        } else {
            return redirect()->route('admin.edit.user', $request->user_id)->with('failed', trans('This email address already registered. Try to another email address.'));
        }
    }

    // Update user status
    public function updateUserStatus(Request $request)
    {
        // Queries
        $user_details = User::where('user_id', $request->query('id'))->first();

        if ($user_details->status == 0) {
            $status = 1;
        } else {
            $status = 0;
        }

        // Update
        User::where('user_id', $request->query('id'))->update(['status' => $status]);

        return redirect()->route('admin.users')->with('success', trans('Updated!'));
    }

    // Delete User
    public function deleteUser(Request $request)
    {
        // Delete
        User::where('user_id', $request->query('id'))->delete();

        return redirect()->route('admin.users')->with('success', trans('Removed!'));
    }

    // Login As User
    public function authAsUser(Request $request, $id)
    {
        // Queries
        $user_details = User::where('user_id', $id)->where('status', 1)->first();

        if (isset($user_details)) {
            // Login
            Auth::loginUsingId($user_details->id);

            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('admin.users')->with('failed', trans('Unable to find user account!'));
        }
    }
}
