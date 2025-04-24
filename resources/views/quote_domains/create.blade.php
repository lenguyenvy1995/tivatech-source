@extends('adminlte::page')

@section('title', 'Thêm Quote Domain Mới')

@section('content_header')
    <h1>Thêm Quote Domain Mới</h1>
@stop

@section('content')
    <form action="{{ route('quote-domains.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Quote Domain</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Thêm Quote Domain</button>
    </form>
@stop
