@extends('adminlte::page')

@section('title', 'Thêm Permission')

@section('content_header')
    <h1>Thêm Permission Mới</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông Tin Permission</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Tên Permission</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Thêm Permission</button>
            </form>
        </div>
    </div>
@stop
