@if (isset($salary))
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title text-center">PHIẾU LƯƠNG</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td colspan="2" class="bg-success text-center"><strong>MỨC LƯƠNG</strong></td>
                    </tr>
                    <tr>
                        <td>Lương cơ bản</td>
                        <td class="text-right" id="rs-base-salary">
                            {{ $salary->base_salary ? number_format($salary->base_salary, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td>Số ngày công</td>
                        <td class="text-right" id="rs-work-days">{{ $salary->worked ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Ngày thực làm</td>
                        <td class="text-right" id="rs_actual_work_days">{{ $salary->thuc_lam ?? '' }}</td>
                    </tr>
                    <tr class="bg-gray disabled">
                        <td>Lương công làm</td>
                        <td class="text-right" id="rs-real-salary">
                            {{ $salary->worked_salary ? number_format($salary->worked_salary, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bg-success text-center"><strong>KPI</strong></td>
                    </tr>
                    <tr>
                        <td>Doanh Số</td>
                        <td class="text-right" id="rs-doanhSo">
                            {{ $salary->doanh_thu ? number_format($salary->doanh_thu, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr class="bg-gray disabled">
                        <td>Lương KPI</td>
                        <td class="text-right" id="rs-kpi">
                            {{ $salary->hoa_hong ? number_format($salary->hoa_hong, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bg-success text-center"><strong>CÁC KHOẢN TỔNG HỢP</strong></td>
                    </tr>
                    <tr>
                        <td>BHXH</td>
                        <td class="text-right" id="rs-bhxh">
                           - {{ $salary->bhxh ? number_format($salary->bhxh, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td>Chuyên cần</td>
                        <td class="text-right" id="rs-attendance_bonus">
                            {{ $salary->diligence ? number_format($salary->diligence, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td>Phụ cấp cơm</td>
                        <td class="text-right" id="rs-rice">
                            {{ $salary->rice_salary ? number_format($salary->rice_salary, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td>Phụ cấp điện thoại</td>
                        <td class="text-right" id="rs-phone_allowance">
                            {{ $salary->phone_salary ? number_format($salary->phone_salary, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td>Thưởng</td>
                        <td class="text-right" id="rs_bonus">
                            {{ $salary->bonus_salary ? number_format($salary->bonus_salary, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td>Chi phí khác</td>
                        <td class="text-right" id="rs-orthercost">
                            {{ $salary->other_cost ? number_format($salary->other_cost, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr class="bg-gray disabled">
                        <td>Tổng các khoản</td>
                        <td class="text-right" id="rs-tong-hop">
                            {{ $salary->total_salary ? number_format($salary->total_salary, 0, ',', '.') : '' }}</td>
                    </tr>
                    <tr class="bg-pink p-2" style="font-weight: bold; font-size: 18px;">
                        <td>Tổng Lương Thực Lãnh</td>
                        <td class="text-right" id="rs-total-salary">
                            {{ $salary->total_salary ? number_format($salary->total_salary, 0, ',', '.') : '' }}</td>
                    </tr>
                </tbody>
            </table>
            <!-- Nút xác nhận -->
            @if ($salary->em_confirm == 0)
                <form action="{{ route('salaries.update_em_confirm') }}" method="POST" class="text-center mt-3">
                    @csrf
                    <input type="hidden" name="salary_id" value="{{ $salary->id }}">
                    <input type="hidden" name="em_confirm" value="1">
                    <button type="submit" class="btn bg-indigo">Xác nhận</button>
                </form>
            @endif
        </div>
    </div>
@else
    <div class="card card-success">
        <div class="card-header ">
            <h3 class="card-title text-center">PHIẾU LƯƠNG</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td colspan="2" class="bg-success text-center"><strong> MỨC LƯƠNG</strong></td>
                    </tr>
                    <tr>
                        <td>Lương cơ bản</td>
                        <td class='text-right'><span id='rs-base-salary'></span></td>
                    </tr>
                    <tr>
                        <td>Số ngày công</td>
                        <td class='text-right'><span id='rs-work-days'></span></td>
                    </tr>
                    <tr>
                        <td>Ngày thực làm</td>
                        <td class='text-right'><span id='rs_actual_work_days'></span></td>
                    </tr>
                    <tr class="bg-gray disabled">
                        <td>Lương công làm:</td>
                        <td class='text-right'><span id='rs-real-salary'></span></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bg-success text-center"><strong>KPI</strong></td>
                    </tr>
                    <tr>
                        <td>Doanh Số</td>
                        <td class='text-right'><span id='rs-doanhSo'></span></td>
                    </tr>
                    <tr class="bg-gray disabled">
                        <td>Lương KPI</td>
                        <td class='text-right'><span id='rs-kpi'></span></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="bg-success text-center"><strong>CÁC KHOẢN TỔNG HỢP</strong></td>
                    </tr>
                    <tr>
                        <td>BHXH</td>
                        <td class='text-right'><span id='rs-bhxh'></span></td>
                    </tr>
                    <tr>
                        <td>Chuyên cần</td>
                        <td class='text-right'><span id='rs-attendance_bonus'></span></td>
                    </tr>
                    <tr>
                        <td>Phụ cấp cơm </td>
                        <td class='text-right'><span id='rs_rice'></span></td>
                    </tr>
                    <tr>
                        <td>Phụ cấp điện thoại </td>
                        <td class='text-right'><span id='rs-phone_allowance'></span></td>
                    </tr>
                    <tr>
                        <td>Thưởng</td>
                        <td class='text-right'><span id='rs_bonus'></span></td>
                    </tr>
                    <tr>
                        <td>Chi phí khác</td>
                        <td class='text-right'> <span id='rs-othercost'></span></td>
                    </tr>
                    <tr class="bg-gray disabled">
                        <td>Tổng các khoản</td>
                        <td class='text-right'> <span id='rs-tong-hop'></span></td>
                    </tr>
                    <tr class="bg-pink p-2" style="font-weight: bold; font-size: 18px;">
                        <td>Tổng Lương Thực Lãnh:</td>
                        <td class='text-right'> <span id='rs-total-salary'></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif
