<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RolePermissionController extends Controller
{
    public function index()
    {
        return view('admin.roles.index');
    }

    public function getData(Request $request)
    {
        $roles = Role::with('users'); // Lấy danh sách roles
        return DataTables::of($roles)
            ->addColumn('actions', function ($role) {
                return view('admin.roles.partials.actions', compact('role'))->render();
            })
            ->editColumn('created_at', function ($role) {
                return $role->created_at ? $role->created_at->format('Y-m-d') : '';
            })
            ->editColumn('users', function ($role) {
                return $role->users->pluck('name')->join(', ');
            })
            ->rawColumns(['actions']) // Cho phép render HTML trong cột actions
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles']);
        Role::create(['name' => $request->name]);

        return response()->json(['message' => 'Role created successfully']);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate(['name' => 'required|unique:roles,name,' . $role->id]);
        $role->update(['name' => $request->name]);

        return response()->json(['message' => 'Role updated successfully']);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }
}
