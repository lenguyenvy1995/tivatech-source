@extends('adminlte::page')

@section('title', 'Xem Lương')

@section('content_header')
    <h1>{{ $salary->user->fullname.' - '.Carbon\Carbon::parse($salary->month_salary)->format('m-Y') }} </h1>
@stop

@section('content')
    @includeIf('salaries.partials.salary_slip')
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">CHI PHÍ PHÁT SINH</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="tbl_other_cost_table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Dịch vụ</th>
                        <th>Doanh thu</th>
                        <th>Hoa hồng</th>
                    </tr>
                </thead>
                <tbody id="other_cost_table_body">
                    @foreach ($OrtherCost as $value)
                        <tr>
                            <td>
                                {{ $value['khach_hang'] }}
                            </td>
                            <td>
                                {{ $value['dich_vu'] }}
                            </td>
                            <td class="text-right">
                                {{  number_format($value['doanh_thu']) }}
                            </td>
                            <td class="text-right">
                                {{ number_format($value['hoa_hong']) }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
@stop
