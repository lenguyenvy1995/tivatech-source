<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        // Áp dụng middleware 'role:admin' cho toàn bộ Controller
        $this->middleware('role:admin');
    }

    /**
     * Hiển thị danh sách người dùng.
     */
    public function index()
    {
        // Lấy tất cả người dùng cùng với Roles
        $users = User::with('roles')->get();

        // Trả về view 'admin.users.index' với dữ liệu Users
        return view('admin.users.index', compact('users'));
    }

    /**
     * Hiển thị form tạo người dùng mới.
     */
    public function create()
    {
        // Lấy tất cả Roles để hiển thị trong form
        $roles = Role::all();

        // Trả về view 'admin.users.create' với dữ liệu Roles
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Lưu người dùng mới vào cơ sở dữ liệu.
     */
    public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        // Tạo người dùng mới
        $user = User::create([
            'name' => $request->name,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Gán Roles cho người dùng
        $user->assignRole($request->roles);

        // Chuyển hướng về danh sách Users với thông báo thành công
        return redirect()->route('admin.users.index')->with('success', 'Người dùng đã được tạo thành công.');
    }

    /**
     * Hiển thị người dùng cụ thể.
     */
    public function show(string $id)
    {
        // Không sử dụng trong quản lý Users, bạn có thể bỏ qua hoặc tùy chỉnh
        abort(404);
    }

    /**
     * Hiển thị form sửa người dùng.
     */
    public function edit(string $id)
    {
        // Tìm người dùng theo ID
        $user = User::findOrFail($id);

        // Lấy tất cả Roles để hiển thị trong form
        $roles = Role::all();

        // Lấy tên các Roles hiện tại của người dùng
        $userRoles = $user->roles->pluck('name')->toArray();

        // Trả về view 'admin.users.edit' với dữ liệu User và Roles
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Cập nhật người dùng trong cơ sở dữ liệu.
     */
    public function update(Request $request, string $id)
    {
        // Tìm người dùng theo ID
        $user = User::findOrFail($id);

        // Xác thực dữ liệu đầu vào
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
        ]);

        // Cập nhật thông tin người dùng
        $user->name = $request->name;
        $user->fullname = $request->fullname;
        $user->email = $request->email;
        $user->ngan_hang = $request->ngan_hang;
        $user->base_salary = $request->base_salary;
        $user->zalo_user_id = $request->zalo_user_id;
        $user->bhxh = $request->bhxh;
        $user->salary = $request->salary;
        $user->phone_allowance = $request->phone_allowance;
        $user->attendance_bonus = $request->attendance_bonus;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        // Đồng bộ Roles cho người dùng
        $user->syncRoles($request->roles);

        // Chuyển hướng về danh sách Users với thông báo thành công
        return redirect()->route('admin.users.index')->with('success', 'Người dùng đã được cập nhật thành công.');
    }

    /**
     * Xóa người dùng khỏi cơ sở dữ liệu.
     */
    public function destroy(string $id)
    {
        // Tìm người dùng theo ID
        $user = User::findOrFail($id);

        // Xóa người dùng
        $user->delete();

        // Chuyển hướng về danh sách Users với thông báo thành công
        return redirect()->route('admin.users.index')->with('success', 'Người dùng đã được xóa thành công.');
    }
}
