@extends('adminlte::page')

@section('content')
    <h1>Quản lý chấm công</h1>

    <!-- Form lọc theo ngày -->
    <form method="GET" action="{{ route('attendance.showlist') }}" class="mb-4">
        <div class="form-group row">
            <label for="date" class="col-sm-2 col-form-label">Chọn ngày:</label>
            <div class="col-sm-4">
                <input type="date" name="date" id="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-primary">Lọc</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nhân viên</th>
                <th>Check-in</th>
                <th>Ghi chú Check-in</th>
                <th>Check-out</th>
                <th>Ghi chú Check-out</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->fullname }}</td>
                    <td class="text-center">{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '' }}</td>
                    <td>{{ $attendance->check_in_note }}</td>
                    <td class="text-center">{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '' }}</td>
                    <td>{{ $attendance->check_out_note }}</td>
                    <td class="text-center">
                        @can('manager attendance')
                            <a href="{{ route('attendance.detail', $attendance->user->id) }}" class="btn btn-info">Chi tiết</a>

                            <a href="{{ route('attendance.edit', $attendance->id) }}" class="btn btn-primary">Chỉnh sửa</a>
                            <form action="{{ route('attendance.destroy', $attendance->id) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa chấm công này không?')">Xóa</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
