@extends('adminlte::page')

@section('title', 'Quản Lý Người Dùng')

@section('content_header')
    <h1>Quản Lý Người Dùng</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mb-3">Thêm Người Dùng</a>
@stop

@section('content')


    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh Sách Người Dùng</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Vai Trò</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}
                                <br>
                                {{ $user->fullname }}
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Không có người dùng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
@section('js')
<script>
    // Hiển thị thông báo từ server
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            toastr.error('{{ $error }}');
        @endforeach
    @endif

    @if (session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if (session('warning'))
        toastr.warning('{{ session('warning') }}');
    @endif
</script>
@stop