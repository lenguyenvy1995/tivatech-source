@extends('adminlte::page')

@section('title', 'Tạo Báo Giá')

@section('content_header')
    <h1>Tạo Báo Giá cho Yêu Cầu {{ $quoteRequest->quoteDomain->name }}</h1>
@stop

@section('content')
    <form action="{{ route('quotes.store') }}" method="POST">
        @csrf
        <input type="hidden" name="quote_request_id" value="{{ $quoteRequest->id }}">

        <!-- Chi phí ước tính -->
        <div class="form-group">
            <label for="estimated_cost">Chi phí ước tính</label>
            <textarea name="estimated_cost" rows="10" class="form-control">{{ old('estimated_cost') }}</textarea>

        </div>

        <!-- Chi tiết -->
        <div class="form-group">
            <label for="details">Chi tiết</label>
            <textarea name="details" class="form-control">{{ old('details') }}</textarea>
        </div>

        <!-- Nút gửi -->
        <button type="submit" class="btn btn-primary">Tạo Báo Giá</button>
    </form>
@stop
