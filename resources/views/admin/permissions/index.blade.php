@extends('adminlte::page')

@section('title', 'Quản Lý Permissions')

@section('content_header')
    <h1>Quản Lý Permissions</h1>
    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm mb-3">Thêm Permission</a>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh Sách Permissions</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Permission</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td>{{ $permission->id }}</td>
                            <td>{{ ucfirst($permission->name) }}</td>
                            <td>
                                <a href="{{ route('admin.permissions.edit', $permission->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                                <form action="{{ route('admin.permissions.destroy', $permission->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa Permission này?')">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Không có Permission nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
