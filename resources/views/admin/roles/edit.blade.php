@extends('adminlte::page')

@section('title', 'Chỉnh sửa Role')

@section('content_header')
    <h1>Chỉnh sửa Role: {{ $role->name }}</h1>
@stop

@section('content')
    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- Dùng POST hoặc PUT tùy cách bạn cấu hình --}}
        <div class="card">
            <div class="card-header">
                <h3>Thông tin Role</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Tên Role:</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ $role->name }}" readonly>
                </div>
                <div class="form-group">
                    <h4>Danh sách quyền:</h4>
                    <div class="row">
                        @foreach ($permissions as $permission)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                           class="form-check-input"
                                           id="permission-{{ $permission->id }}"
                                           {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                    <label for="permission-{{ $permission->id }}" class="form-check-label">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">Lưu thay đổi</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </div>
    </form>
@stop
