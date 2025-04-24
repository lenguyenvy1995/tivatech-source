@extends('adminlte::page')

@section('title', 'Sửa thông tin lương')
@section('content_header')
    <h1>Sửa thông tin lương</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('salaries.index') }}" class="btn btn-primary">Quay lại danh sách</a>
        </div>

        <div class="card-body">
            <form action="{{ route('salaries.update', $salary->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="user_id">{{ $salary->user->fullname }}</label>
                </div>

                <div class="form-group">
                    <label for="form_salary">Hình thức lương</label>
                    <div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="form_salary" id="salary_fixed" value="0" 
                                {{ $salary->form_salary == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="salary_fixed">Cố định</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="form_salary" id="salary_kpi" value="1" 
                                {{ $salary->form_salary == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="salary_kpi">KPI</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="worked">Số ngày làm việc</label>
                    <input type="number" class="form-control" name="worked" id="worked" value="{{ $salary->worked }}">
                </div>

                <div class="form-group">
                    <label for="thuc_lam">Số ngày thực làm</label>
                    <input type="number" class="form-control" name="thuc_lam" id="thuc_lam" value="{{ $salary->thuc_lam }}">
                </div>

                <div class="form-group">
                    <label for="diligence">Thưởng chuyên cần</label>
                    <input type="number" class="form-control" name="diligence" id="diligence" value="{{ $salary->diligence }}">
                </div>

                <div class="form-group">
                    <label for="base_salary">Lương Cơ bản</label>
                    <input type="number" class="form-control" name="base_salary" id="base_salary" value="{{ $salary->base_salary }}">
                </div>

                <div class="form-group">
                    <label for="doanh_thu">Doanh số</label>
                    <input type="number" class="form-control" name="doanh_thu" id="doanh_thu" value="{{ $salary->doanh_thu }}">
                </div>
                <div class="form-group">
                    <label for="hoa_hong">Lương KPI</label>
                    <input type="number" class="form-control" name="hoa_hong" id="hoa_hong" value="{{ $salary->hoa_hong }}">
                </div>
                <div class="form-group">
                    <label for="worked_salary">Lương công làm</label>
                    <input type="number" class="form-control" name="worked_salary" id="worked_salary" value="{{ $salary->worked_salary }}">
                </div>
                <div class="form-group">
                    <label for="phone_salary">Phụ cấp điện thoại</label>
                    <input type="number" class="form-control" name="phone_salary" id="phone_salary" value="{{ $salary->phone_salary }}">
                </div>
                <div class="form-group">
                    <label for="rice_salary">Phụ cấp cơm</label>
                    <input type="number" class="form-control" name="rice_salary" id="rice_salary" value="{{ $salary->rice_salary }}">
                </div>
                <div class="form-group">
                    <label for="bonus_salary">Thưởng</label>
                    <input type="number" class="form-control" name="bonus_salary" id="bonus_salary" value="{{ $salary->bonus_salary }}">
                </div>
                <div class="form-group">
                    <label for="other_cost">Chi phí khác</label>
                    <input type="number" class="form-control" name="other_cost" id="other_cost" value="{{ $salary->other_cost }}">
                </div>
                <div class="form-group">
                    <label for="total_salary">Thực Lãnh</label>
                    <input type="number" class="form-control" name="total_salary" id="total_salary" value="{{ $salary->total_salary }}">
                </div>

                <!-- Các trường khác -->
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </form>
        </div>
    </div>
@stop
