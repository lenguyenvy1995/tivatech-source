@extends('adminlte::page')

@section('title', 'Sửa Permission')

@section('content_header')
    <h1>Sửa Permission: {{ ucfirst($permission->name) }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông Tin Permission</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Tên Permission</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $permission->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Cập Nhật Permission</button>
            </form>
        </div>
    </div>
@stop
