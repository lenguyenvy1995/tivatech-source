<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salary;
use App\Models\KpiSalary;
use App\Models\User;
use App\Models\Attendance;
use App\Models\OrtherCost;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CampaignController;
use App\Services\ZaloService;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        // Lấy dữ liệu từ request
        $month = str_pad($request->input('month', now()->month), 2, '0', STR_PAD_LEFT);
        $year = $request->input('year', now()->year);
        $employeeId = $request->input('user_id');
        // Tính ngày bắt đầu và kết thúc của tháng
        // Truy vấn dữ liệu lương
        $query = Salary::with('user')
            ->whereBetween('month_salary', ["{$year}-{$month}", "{$year}-{$month}"]);

        if ($employeeId && $employeeId != null) {
            $query->where('user_id', $employeeId);
        }

        $salaries = $query->get();
        // Kiểm tra nếu request là từ DataTables (AJAX)
        if ($request->ajax()) {
            // Tính toán các giá trị bổ sung
            $total_salary = $query->sum('total_salary');
            $atten = new AttendanceController;
            $atten = $atten->calculateMonthlyWorkDays($request);
            // Trả về JSON dữ liệu cho DataTables
            return datatables()->of($salaries)
                ->addIndexColumn() // Thêm cột STT
                ->addColumn('employee_name', function ($row) {
                    return $row->user->fullname;
                })
                ->addColumn('ngan_hang', function ($row) {
                    return $row->user->ngan_hang;
                })
                ->editColumn('total_salary', function ($row) {
                    return number_format($row->total_salary);
                })
                ->editColumn('status', function ($row) {
                    $checkboxId = 'customCheckbox_' . $row->id; // Giả sử `id` là khóa chính của dòng

                    if ($row->em_confirm == 0) {
                        return 'Chưa xác nhận';
                    } elseif ($row->em_confirm == 1) {
                        return '
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="' . $checkboxId . '">
                                <label for="' . $checkboxId . '" class="custom-control-label text-danger">Chưa chuyển khoản</label>
                            </div>';
                    } elseif ($row->em_confirm == 2) {
                        return '
                             <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="' . $checkboxId . '" checked>
                                <label for="' . $checkboxId . '" class="custom-control-label text-success" >Đã chuyển khoản</label>
                            </div>';
                    }

                    return '';
                })
                ->rawColumns(['status'])
                ->with([
                    'total_salary' => number_format($total_salary, 0, ',', '.'),
                    'atten' => $atten,
                ])
                ->make(true);
        }

        // Tính tổng số ngày công và tổng tiền phạt
        $total_salary = $salaries->sum('total_salary');
        $atten = new AttendanceController;
        $atten = $atten->calculateMonthlyWorkDays($request);
        // Dành cho trang view thông thường
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            // Admin: Hiển thị toàn bộ nhân viên
            $employees = User::where('status', 1)->get();
        } else {
            // User thông thường: Chỉ hiển thị chính họ
            $employees = User::where('id', $user->id)->get();
        }
        // dd($total_salary);
        return view('salaries.index', compact('total_salary', 'employees', 'month', 'year', 'employeeId', 'atten'));
    }

    // Hiển thị form tính lương
    public function showCalculateForm()
    {
        // Lấy danh sách KPI liên quan đến user
        $kpis = KpiSalary::with('user')->get();

        $users = User::where('status', '1')->get(); // Lấy danh sách nhân viên từ bảng users
        return view('salaries.calculate', compact('users', 'kpis'));
    }
    //tạo bảng lương nhân viên

    public function store(Request $request)
    {

        $salary = Salary::create([
            'user_id' => $request->input('user_id'),
            'form_salary' => $request->input('salary_type'),
            'worked' => $request->input('work_days'),
            'thuc_lam' => $request->input('actual_work_days'),
            'diligence' => $request->input('attendance_bonus'),
            'rice_salary' => $request->input('actual_work_days') * 20000,
            'phone_salary' => $request->input('phone_allowance'),
            'month_salary' => $request->input('month'),
            'base_salary' => $request->input('base_salary'),
            'doanh_thu' => $request->input('doanhSo'),
            'hoa_hong' => $request->input('kpi'),
            'worked_salary' => $request->input('real_salary'),
            'other_cost' => $request->input('other_expenses'),
            'total_salary' => $request->input('t_total_salary'),
            'bonus_salary' => $request->input('bonus'),
        ]);
        // $message = str_replace('<br>', "\n", 'Bảng lương tháng: ' . Carbon::parse($salary->month_salary)->format('m-Y') . '<br>' . route('salaries.show', $salary->id));
        // $zaloService = new ZaloService;
        // $zaloService->sendMessage('8825240549062391828', $message);
        // $zaloService->sendMessage($salary->user->zalo_user_id, $message);
        return redirect()->route('salaries.index')->with('success', 'Bảng lương' . $salary->user->fullname . ' đã lập thành công');
    }
    public function show($id)
    {
        $salary = Salary::findOrFail($id);
        $OrtherCost=OrtherCost::where('user_id',$salary->user_id)->where('date',$salary->month_salary)->get();
        return view('salaries.show', compact('salary','OrtherCost'));
    }
    public function edit($id)
    {
        $salary = Salary::findOrFail($id);
        return view('salaries.edit', compact('salary'));
    }
    public function destroy($id)
    {
        $salary = Salary::findOrFail($id);
        $salary->delete();

        return response()->json(['success' => 'Xóa thành công']);
    }
    public function update(Request $request, $id)
    {
        // Tìm bản ghi theo ID
        $salary = Salary::find($id);

        // Kiểm tra nếu không tìm thấy
        if (!$salary) {
            return redirect()->route('salaries.index')->with('error', 'Không tìm thấy thông tin lương để cập nhật');
        }
        $salary->fill(request()->all());
        $salary->save();
        // Trả về thông báo thành công
        return redirect()->route('salaries.index')->with('success', 'Cập nhật thông tin lương thành công');
    }

    // tính lương nhân viên
    public function calculateSalary(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m')); // Tháng cần tính
        $users = User::all(); // Lấy tất cả nhân viên

        foreach ($users as $user) {
            // Lấy dữ liệu chấm công
            $attendances = Attendance::where('user_id', $user->id)
                ->whereMonth('date', date('m', strtotime($month)))
                ->whereYear('date', date('Y', strtotime($month)))
                ->get();

            $work_days = $attendances->sum('shift'); // Tổng số ngày công
            $actual_days = $attendances->count();   // Tổng số ngày làm việc
            $absent_days = $actual_days - $work_days; // Ngày nghỉ
            $meal_allowance = $work_days * 20000;  // Phụ cấp cơm

            // Lấy các giá trị từ giao diện hoặc đặt mặc định
            $phone_allowance = $request->input('phone_allowance', 0);
            $attendance_bonus = $request->input('attendance_bonus', 0);
            $bonus = $request->input('bonus', 0);
            $kpi = $user->salary_type == 'kpi' ? $request->input('kpi', 0) : 0; // Chỉ áp dụng cho lương KPI
            $other_expenses = $request->input('other_expenses', 0);

            // Tính tổng lương
            $total_salary = $user->base_salary
                + $meal_allowance
                + $phone_allowance
                + $attendance_bonus
                + $bonus
                + $kpi
                + $user->bhxh
                - $other_expenses;

            // Lưu kết quả tính lương vào bảng `salaries`
            Salary::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'month' => $month,
                ],
                [
                    'salary_type' => $user->salary_type,
                    'base_salary' => $user->base_salary,
                    'work_days' => $work_days,
                    'bhxh' => $user->bhxh,
                    'absent_days' => $absent_days,
                    'meal_allowance' => $meal_allowance,
                    'phone_allowance' => $phone_allowance,
                    'attendance_bonus' => $attendance_bonus,
                    'bonus' => $bonus,
                    'kpi' => $kpi,
                    'other_expenses' => $other_expenses,
                    'total_salary' => $total_salary,
                ]
            );
        }

        return response()->json(['message' => 'Tính lương thành công!']);
    }
    public function getKpi(Request $request)
    {
        // Lấy dữ liệu KPI theo user_id
        $kpis = KpiSalary::where('user_id', $request->user_id)
            ->get(['doanh_thu', 'hoa_hong', 'created_at']);

        // Trả về dữ liệu KPI dưới dạng JSON
        return response()->json(['kpis' => $kpis]);
    }
    //lấy thông tin tính lương dự kiến cho từng nhân viên
    public function expected(Request $request)
    {
        // Lấy thông tin người dùng
        $user = User::find($request->user_id);
        // dd($user);
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }
        // Trích xuất tháng và năm từ 'date'
        try {
            $month = Carbon::parse($request->date)->month;
            $year = Carbon::parse($request->date)->year;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Định dạng ngày không hợp lệ'], 400);
        }
        // Gọi hàm tính số ngày làm việc từ AttendanceController
        $request->merge(['month' => $month]);
        $request->merge(['year' => $year]);
        $doanhSo = '';
        if ($user->salary == 1) {
            $ds = new CampaignController;
            $doanhSo = intVal($ds::getMonthlySales($request->month, $request->year, $request->user_id) + $ds::getMonthlySalesTypeCamp2($request->month, $request->year, $request->user_id));
        }
        //lấy doanh số theo thánh
        $attendanceController = new AttendanceController;
        $actual_work_days = $attendanceController->calculateMonthlyWorkDays($request);
        $work_days = $attendanceController->getWorkingDaysInMonth($request->month, $request->year);
        // Chuẩn bị dữ liệu trả về
        $data = [
            'base_salary' => $user->base_salary,
            'actual_work_days' => $actual_work_days,
            'work_days' => $work_days,
            'salary_type' => $user->salary,
            'doanhSo' => $doanhSo,
            'kpi' => $request->kpi,
            'bhxh' => $user->bhxh,
            'phone_allowance' => $user->phone_allowance,
            'attendance_bonus' => $user->attendance_bonus,
        ];

        return response()->json($data); // Trả về JSON
    }
    public function update_em_confirm(Request $request)
    {
        // Cập nhật `en_confirm` của Salary
        $salary = Salary::findOrFail($request->salary_id);
        $salary->em_confirm = $request->em_confirm;
        $salary->save();
        $zaloService = new ZaloService;
        if ($salary->em_confirm == '2') {
            $message = str_replace('<br>', "\n", 'Bảng lương tháng: ' . Carbon::parse($salary->month_salary)->format('m-Y') . ' đã được chuyển.<br>' . route('salaries.show', $salary->id));
            $zaloService->sendMessage('8825240549062391828', $message);
            $zaloService->sendMessage($salary->user->zalo_user_id, $message);
        }
        if ($request->ajax()) {
            return response()->json(['success' => 'Bạn đã xác nhận lương thành công']);
        } else {
            return back()->with('success', value: 'Bạn đã xác nhận lương thành công');
        }
    }
}
