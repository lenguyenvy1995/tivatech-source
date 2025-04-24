<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        // Áp dụng middleware 'role:admin' cho toàn bộ Controller
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Hiển thị danh sách Permissions.
     */
    public function index()
    {
        $permissions = Permission::all();
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Hiển thị form tạo Permission mới.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Lưu Permission mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|unique:permissions,name',
        ]);

        // Tạo Permission mới
        Permission::create(['name' => $request->name]);

        // Chuyển hướng về danh sách Permissions với thông báo thành công
        return redirect()->route('admin.permissions.index')->with('success', 'Permission đã được tạo thành công.');
    }

    /**
     * Hiển thị Permission cụ thể.
     */
    public function show($id)
    {
        // Không sử dụng trong quản lý Permissions, bạn có thể bỏ qua hoặc tùy chỉnh
        abort(404);
    }

    /**
     * Hiển thị form sửa Permission.
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Cập nhật Permission trong cơ sở dữ liệu.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        // Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        // Cập nhật tên Permission
        $permission->update(['name' => $request->name]);

        // Chuyển hướng về danh sách Permissions với thông báo thành công
        return redirect()->route('admin.permissions.index')->with('success', 'Permission đã được cập nhật thành công.');
    }

    /**
     * Xóa Permission khỏi cơ sở dữ liệu.
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission đã được xóa thành công.');
    }
}
