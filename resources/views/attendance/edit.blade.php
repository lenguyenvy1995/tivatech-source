@extends('adminlte::page')

@section('content')
    <h1>Chỉnh sửa chấm công</h1>

    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="check_in">Check-in:</label>
            <input type="datetime-local" name="check_in" id="check_in" class="form-control"
                   value="{{ $attendance->check_in ? $attendance->check_in->format('Y-m-d\TH:i') : '' }}">
        </div>
        <div class="form-group">
            <label for="note">Ghi chú Check-in:</label>
            <textarea name="check_in_note" id="check_in_note" rows="3" class="form-control">{{ $attendance->check_in_note }}</textarea>
        </div>

        <div class="form-group">
            <label for="check_out">Check-out:</label>
            <input type="datetime-local" name="check_out" id="check_out" class="form-control"
                   value="{{ $attendance->check_out ? $attendance->check_out->format('Y-m-d\TH:i') : '' }}">
        </div>

        <div class="form-group">
            <label for="note">Ghi chú Check-out:</label>
            <textarea name="check_out_note" id="check_out_note" rows="3" class="form-control">{{ $attendance->check_out_note }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('attendance.showlist') }}" class="btn btn-secondary">Quay lại</a>
    </form>
@endsection
