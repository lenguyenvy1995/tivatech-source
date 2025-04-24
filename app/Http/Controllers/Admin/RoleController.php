<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        // Áp dụng middleware 'role:admin' cho toàn bộ Controller
        $this->middleware('role:admin');
    }

    /**
     * Hiển thị danh sách Roles.
     */
    public function index()
    {
        // Lấy tất cả Roles
        $roles = Role::all();
        $permissions = Permission::all();
        // Trả về view 'admin.roles.index' với dữ liệu Roles
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Hiển thị form tạo Role mới.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Lưu Role mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        // Tạo Role mới
        Role::create(['name' => $request->name]);

        // Chuyển hướng về danh sách Roles với thông báo thành công
        return response()->json(['success' => 'Role created successfully']);
    }

    /**
     * Hiển thị Role cụ thể.
     */
    public function show(string $id)
    {
        // Không sử dụng trong quản lý Roles, bạn có thể bỏ qua hoặc tùy chỉnh
        abort(404);
    }

    /**
     * Hiển thị form sửa Role.
     */
    // public function edit(string $id)
    // {
    //     // Tìm Role theo ID
    //     $role = Role::findOrFail($id);

    //     // Trả về view 'admin.roles.edit' với dữ liệu Role
    //     return view('admin.roles.edit', compact('role'));
    // }
    public function edit(Role $role)
    {
        $permissions = Permission::all(); // Lấy danh sách tất cả quyền
        $rolePermissions = $role->permissions->pluck('name')->toArray(); // Lấy danh sách quyền của Role hiện tại

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Cập nhật Role trong cơ sở dữ liệu.
     */
    // public function update(Request $request, string $id)
    // {
    //     // Tìm Role theo ID
    //     $role = Role::findOrFail($id);

    //     // Xác thực dữ liệu đầu vào
    //     $request->validate([
    //         'name' => 'required|unique:roles,name,' . $role->id,
    //     ]);

    //     // Cập nhật tên Role
    //     $role->update(['name' => $request->name]);

    //     // Chuyển hướng về danh sách Roles với thông báo thành công
    //     return response()->json(['success' => 'Role đã được cập nhật thành công.']);
    // }
    public function update(Request $request, Role $role)
    {
        $request->validate(['permissions' => 'array', 'permissions.*' => 'exists:permissions,name']);
        
        // Đồng bộ các quyền với Role
        $role->syncPermissions($request->permissions);
    
        return redirect()->route('admin.roles.index')->with('success', 'Cập nhật Role thành công.');
    }
    
    /**
     * Xóa Role khỏi cơ sở dữ liệu.
     */
    public function destroy(string $id)
    {
        // Tìm Role theo ID
        $role = Role::findOrFail($id);

        // Đảm bảo không xóa Role 'admin' để tránh mất quyền quản trị
        if ($role->name === 'admin') {
            return response()->json(['success' => 'Role đã được xoá thành công']);
        }

        // Xóa Role
        $role->delete();

        // Chuyển hướng về danh sách Roles với thông báo thành công
        return response()->json(['success' => 'Role đã được xoá thành công']);
    }
}
