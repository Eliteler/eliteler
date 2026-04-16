<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Role::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('admin.edit.role', $row->role_id);
                    $deleteUrl = "#"; // Handled by JS
                    
                    return '
                        <a class="btn-action" href="#" role="button" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . $editUrl . '">' . __('Edit') . '</a>
                            <a class="dropdown-item text-danger" href="#" onclick="deleteRole(`' . $row->role_id . '`); return false;">' . __('Delete') . '</a>
                        </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.roles.index', compact('settings', 'config'));
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\Http\Response
     */
    public function createRole()
    {
        $settings = Setting::where('status', 1)->first();
        return view('admin.pages.roles.create', compact('settings'));
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveRole(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255',
        ]);

        $role = new Role();
        $role->role_name = $request->role_name;
        $role->role_slug = Str::slug($request->role_name);
        $role->save();

        return redirect()->route('admin.roles')->with('success', __('Role Created Successfully!'));
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editRole($id)
    {
        $role_details = Role::where('role_id', $id)->first();
        if (!$role_details) {
            return redirect()->route('admin.roles')->with('failed', __('Role Not Found!'));
        }
        $settings = Setting::where('status', 1)->first();
        return view('admin.pages.roles.edit', compact('role_details', 'settings'));
    }

    /**
     * Update the specified role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required',
            'role_name' => 'required|string|max:255',
        ]);

        Role::where('role_id', $request->role_id)->update([
            'role_name' => $request->role_name,
            'role_slug' => Str::slug($request->role_name),
        ]);

        return redirect()->route('admin.roles')->with('success', __('Role Updated Successfully!'));
    }

    /**
     * Remove the specified role from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteRole(Request $request)
    {
        $id = $request->query('id');
        
        // Don't allow deleting base roles (Administrator, Manager, User, Admin)
        if (in_array($id, [1, 2, 3, 4])) {
            return back()->with('failed', __('Default roles cannot be deleted!'));
        }

        Role::where('role_id', $id)->delete();

        return redirect()->route('admin.roles')->with('success', __('Role Deleted Successfully!'));
    }
}
