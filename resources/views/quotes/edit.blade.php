{{-- resources/views/quote_requests/edit.blade.php --}}

@extends('adminlte::page')

@section('title', 'Chỉnh sửa Báo Giá')

@section('content_header')
    <h1>Chỉnh sửa Báo Giá cho Yêu Cầu {{ $quoteRequest->quoteDomain->name }}</h1>
@stop

@section('content')
    <form action="{{ route('quotes.update', $quoteRequest->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- Sử dụng phương thức PUT để cập nhật -->

        <!-- Chi phí ước tính -->
        <div class="form-group">
            <label for="estimated_cost">Chi phí ước tính</label>
            <textarea name="estimated_cost" id="estimated_cost" rows="10" class="form-control">{{ old('estimated_cost', $quote->estimated_cost) }}</textarea>
            @error('estimated_cost')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Chi tiết -->
        <div class="form-group">
            <label for="details">Chi tiết</label>
            <textarea name="details" id="details" class="form-control">{{ old('details', $quote->details) }}</textarea>
            @error('details')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Nút gửi -->
        <button type="submit" class="btn btn-primary">Cập nhật Báo Giá</button>
        <a href="{{ route('quote-requests.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
@stop
