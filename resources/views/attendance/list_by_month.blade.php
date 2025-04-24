@extends('adminlte::page')

@section('title', 'Danh sách chấm công')

@section('content_header')
    <h1>Danh sách chấm công</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('attendance.listByEmployeeAndMonth') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="month">Tháng</label>
                        <select name="month" id="month" class="form-control">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>Tháng {{ $m }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year">Năm</label>
                        <select name="year" id="year" class="form-control">
                            @for ($y = now()->year - 5; $y <= now()->year; $y++)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="user_id">Nhân viên</label>
                        <select name="user_id" id="user_id" class="form-control">
                            <option value="">Tất cả</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $employee->id == $employeeId ? 'selected' : '' }}>
                                    {{ $employee->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            {{-- Hiển thị tổng số ngày công và tổng tiền phạt --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <h4>Tổng số ngày công: {{ $workDays['workDays']  }} ngày</h4>
                </div>
                <div class="col-md-6">
                    <h4>Tổng tiền phạt: {{ $workDays['fines'] }} VND</h4>
                </div>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Nhân viên</th>
                        <th>Ngày check-in</th>
                        <th>Giờ check-in</th>
                        <th>Giờ check-out</th>
                        <th>Tính Công</th>
                        <th>Phạt</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $key => $attendance)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $attendance->user->fullname }}</td>
                            <td>{{ $attendance->check_in->format('Y-m-d') }}</td>
                            <td>{{ $attendance->check_in->format('H:i') }}</td>
                            <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : 'Chưa check-out' }}</td>
                            <td>{{ $attendance->shift }}</td>
                            <td>{{ is_numeric($attendance->fine) ? number_format($attendance->fine) . ' VND' : 'Không' }}</td>
                            <td>{{ $attendance->check_in_note }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Không có dữ liệu chấm công.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
