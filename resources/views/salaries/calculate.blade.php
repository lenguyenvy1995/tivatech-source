@extends('adminlte::page')

@section('title', 'Tính lương nhân viên')

@section('content_header')
    <h1>Tính lương nhân viên</h1>
@stop

@section('content')
<div class="container-fluid">
    <form method="POST" action="{{ route('salaries.store') }}" id="salaryForm">
        @csrf
        <div class="row">
            <div class="col-md-6">
                @include('salaries.partials.basic_salary') <!-- Tách phần lương cơ bản -->
            </div>
            <div class="col-md-6">
                @include('salaries.partials.salary_slip') <!-- Tách phần phiếu lương -->
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @include('salaries.partials.salary_type') <!-- Tách phần hình thức lương -->
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @include('salaries.partials.other_costs') <!-- Tách phần chi phí phát sinh -->
            </div>
        </div>

        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Tính lương</button>
        </div>
    </form>
</div>

@include('salaries.modals.add_cost') <!-- Modal thêm chi phí -->
@include('salaries.modals.edit_cost') <!-- Modal sửa chi phí -->
@include('salaries.modals.delete_cost') <!-- Modal xóa chddi phí -->
@include('salaries.modals.add_kpi') <!-- Modal thêm KPI -->
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
@routes()
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
<script src="{{ asset('js/salaries.js') }}"></script> <!-- Chuyển các script vào file riêng -->
@stop   