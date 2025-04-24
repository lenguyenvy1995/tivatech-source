<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ZaloService;
use Illuminate\Support\Facades\DB;
use ParagonIE\ConstantTime\Encoding;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:manager attendance'])->only(['showlist', 'edit']);
    }
    public function showlist(Request $request)
    {
        $today = now()->toDateString(); // Lấy ngày hôm nay

        $query = Attendance::query();
        // Lọc theo ngày (nếu có), nếu không có giá trị `date`, mặc định là ngày hôm nay
        $date = $request->get('date', $today);
        $query->whereDate('check_in', $date);
        $attendances = $query->with('user')->get();
        return view('attendance.list', compact('attendances', 'today', 'date'));
    }
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->fill($request->all());
        $attendance->save();

        return redirect()->route('attendance.showlist')->with('success', 'Đã cập nhật chấm công thành công');
    }
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return redirect()->route('attendance.showlist')->with('success', 'Xóa chấm công thành công.');
    }

    public function show($id)
    {
        $attendances = Attendance::where('user_id', $id)->get();
        // Kiểm tra xem có check-in hôm nay nhưng chưa check-out không
        $today = Carbon::today();
        $currentAttendance = Attendance::where('user_id', auth()->id())
            ->whereDate('check_in', $today)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        // Kiểm tra nếu đã hoàn thành cả check-in và check-out hôm nay
        $completedAttendanceToday = Attendance::where('user_id', auth()->id())
            ->whereDate('check_in', $today)
            ->whereNotNull('check_out')
            ->exists();
        // Quy định đi làm và phạt đi trễ
        $shiftRules = [
            'Ca sáng' => [
                'Thời gian' => '8:00 - 12:00 (T2 - T6), 9:00 - 12:00 (T7)',
                'Phạt' => [
                    ['Thời gian' => '08:31 - 08:35', 'Phạt' => '10,000 VND'],
                    ['Thời gian' => '08:36 - 08:45', 'Phạt' => '20,000 VND'],
                    ['Thời gian' => '08:46 - 09:00', 'Phạt' => '30,000 VND'],
                    ['Thời gian' => 'Sau 09:00', 'Phạt' => 'Không tính ca']
                ]
            ],
            'Ca chiều' => [
                'Thời gian' => '13:30 - 16:30 (T2 - T6), 13:30 - 15:00 (T7)',
                'Phạt' => [
                    ['Thời gian' => '13:31 - 13:35', 'Phạt' => '10,000 VND'],
                    ['Thời gian' => '13:36 - 13:45', 'Phạt' => '20,000 VND'],
                    ['Thời gian' => '13:46 - 14:00', 'Phạt' => '30,000 VND'],
                    ['Thời gian' => 'Sau 14:00', 'Phạt' => 'Không tính ca']
                ]
            ]
        ];

        // Chuẩn bị dữ liệu sự kiện cho FullCalendar
        $events = [];
        foreach ($attendances as $attendance) {
            $events[] = [
                'title' => 'Check-in: ' . $attendance->check_in->format('H:i') .
                    ($attendance->check_out ? ' - ' . $attendance->check_out->format('H:i') : ''),
                'start' => $attendance->check_in,
                'end' => $attendance->check_out ?: $attendance->check_in,
                'shift' => $attendance->shift ?: $attendance->shift,
                'check_in_note' => $attendance->check_in_note ?: $attendance->check_in_note,
                'check_out_note' => $attendance->check_out_note ?: $attendance->check_out_note,
                'backgroundColor' => $attendance->check_out ? 'green' : 'red', // Sửa lại thành 'backgroundColor'
                'textColor' => '#ffffff'
            ];
            // Sự kiện nền cho cả ngày
            $events[] = [
                'start' => $attendance->check_in->format('Y-m-d'), // Ngày check-in
                'end' => $attendance->check_in->format('Y-m-d'),   // Cùng ngày (1 ngày duy nhất)
                'rendering' => 'background',
                'backgroundColor' => $attendance->check_out ? 'green' : 'red', // Màu nền dựa trên check-out
            ];
        }
        return view('attendance.index', compact('attendances', 'events', 'currentAttendance', 'completedAttendanceToday', 'shiftRules',));
   

    }
    public function index()
    {
        $attendances = Attendance::where('user_id', auth()->id())->get();
        // Kiểm tra xem có check-in hôm nay nhưng chưa check-out không
        $today = Carbon::today();
        $currentAttendance = Attendance::where('user_id', auth()->id())
            ->whereDate('check_in', $today)
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        // Kiểm tra nếu đã hoàn thành cả check-in và check-out hôm nay
        $completedAttendanceToday = Attendance::where('user_id', auth()->id())
            ->whereDate('check_in', $today)
            ->whereNotNull('check_out')
            ->exists();
        // Quy định đi làm và phạt đi trễ
        $shiftRules = [
            'Ca sáng' => [
                'Thời gian' => '8:00 - 12:00 (T2 - T6), 9:00 - 12:00 (T7)',
                'Phạt' => [
                    ['Thời gian' => '08:31 - 08:35', 'Phạt' => '10,000 VND'],
                    ['Thời gian' => '08:36 - 08:45', 'Phạt' => '20,000 VND'],
                    ['Thời gian' => '08:46 - 09:00', 'Phạt' => '30,000 VND'],
                    ['Thời gian' => 'Sau 09:00', 'Phạt' => 'Không tính ca']
                ]
            ],
            'Ca chiều' => [
                'Thời gian' => '13:30 - 16:30 (T2 - T6), 13:30 - 15:00 (T7)',
                'Phạt' => [
                    ['Thời gian' => '13:31 - 13:35', 'Phạt' => '10,000 VND'],
                    ['Thời gian' => '13:36 - 13:45', 'Phạt' => '20,000 VND'],
                    ['Thời gian' => '13:46 - 14:00', 'Phạt' => '30,000 VND'],
                    ['Thời gian' => 'Sau 14:00', 'Phạt' => 'Không tính ca']
                ]
            ]
        ];

        // Chuẩn bị dữ liệu sự kiện cho FullCalendar
        $events = [];
        foreach ($attendances as $attendance) {
            $events[] = [
                'title' => 'Check-in: ' . $attendance->check_in->format('H:i') .
                    ($attendance->check_out ? ' - ' . $attendance->check_out->format('H:i') : ''),
                'start' => $attendance->check_in,
                'end' => $attendance->check_out ?: $attendance->check_in,
                'shift' => $attendance->shift ?: $attendance->shift,
                'check_in_note' => $attendance->check_in_note ?: $attendance->check_in_note,
                'check_out_note' => $attendance->check_out_note ?: $attendance->check_out_note,
                'backgroundColor' => $attendance->check_out ? 'green' : 'red', // Sửa lại thành 'backgroundColor'
                'textColor' => '#ffffff'
            ];
            // Sự kiện nền cho cả ngày
            $events[] = [
                'start' => $attendance->check_in->format('Y-m-d'), // Ngày check-in
                'end' => $attendance->check_in->format('Y-m-d'),   // Cùng ngày (1 ngày duy nhất)
                'rendering' => 'background',
                'backgroundColor' => $attendance->check_out ? 'green' : 'red', // Màu nền dựa trên check-out
            ];
        }
        return view('attendance.index', compact('attendances', 'events', 'currentAttendance', 'completedAttendanceToday', 'shiftRules',));
    }
    public function showDetail($userId)
    {
        // Lấy thông tin chấm công của nhân viên theo user_id
        $attendances = Attendance::where('user_id', $userId)->get();

        // Chuẩn bị dữ liệu cho FullCalendar
        $events = [];
        foreach ($attendances as $attendance) {
            // dd($attendance->check_in->format('Y-m-d'));
            $events[] = [
                'title' => 'Check-in: ' . $attendance->check_in->format('H:i') .
                    ($attendance->check_out ? ' - ' . $attendance->check_out->format('H:i') : ''),
                'start' => $attendance->check_in->format('Y-m-d'),
                'end' => $attendance->check_out ? $attendance->check_out->format('Y-m-d') : $attendance->check_in->format('Y-m-d'),
                'display' => 'background',
                'color' =>  $attendance->check_out ? 'green' : 'red',
                'textColor' => '#ffffff',
                'description' => "gagafag",
            ];
        }
        return view('attendance.detail', compact('events', 'userId'));
    }

    public function checkIn(Request $request)
    {
        $zaloService = new ZaloService;
        $checkInTime = Carbon::now();
        $fine = $this->calculateFine($checkInTime);
        if ($fine == 'early_no_shift') {
            return redirect()->route('attendance.index')->with('warning', ' Không chấm công được vì đã đến sớm quá 30 phút.');
        }
        if ($fine == 'no_shift') {
            Attendance::create([
                'user_id' => auth()->id(),
                'check_in' => $checkInTime,
                'fine' => null,
                'shift_status' => 'not_counted', // Trạng thái không tính ca
                'check_in_note' => $request->input('note'),
            ]);
            $message = str_replace('<br>', "\n", '<br>THÔNG BÁO CHẤM CÔNG CHECK-IN <br>Giờ vào: ' . $checkInTime->format('H:i d-m-Y') . '<br>Check-in thành công nhưng không được tính ca sáng vì đã trễ quá giờ quy định ca sáng.<br>Chúc Bạn Một Ngày Vui :))');
            $zaloService->sendMessage(Auth::user()->zalo_user_id, $message);
            $message2 = str_replace('<br>', "\n", Auth::user()->fullname . '<br>Giờ vào: ' . $checkInTime->format('H:i d-m-Y'));
            $zaloService->sendMessage('8825240549062391828', $message2);
            return redirect()->route('attendance.index')->with('warning', 'Check-in thành công nhưng không được tính ca sáng vì đã trễ quá giờ quy định ca sáng.');
        }

        if ($fine == 'after_shift_time') {
            return redirect()->route('attendance.index')->withErrors('Bạn đã trễ quá giờ quy định (sau 14:00), không thể chấm công.');
        }

        // Kiểm tra xem có trong khoảng thời gian chấm công hay không
        if ($fine == null) {
            return redirect()->route('attendance.index')->withErrors('Không nằm trong giờ chấm công, không thể chấm công.');
        }
        // Nếu không có phạt hoặc có phạt
        Attendance::create([
            'user_id' => auth()->id(),
            'check_in' => $checkInTime,
            'fine' => $fine,
            'shift_status' => 'counted', // Trạng thái tính ca
            'check_in_note' => $request->input('note'),
        ]);
        $message = str_replace('<br>', "\n", '<br>THÔNG BÁO CHẤM CÔNG CHECK-IN <br>Giờ vào: ' . $checkInTime->format('H:i d-m-Y') . '<br>Check-in thành công.<br>Chúc Bạn Một Ngày Vui :))');
        $zaloService->sendMessage(Auth::user()->zalo_user_id, $message);
        $message2 = str_replace('<br>', "\n", Auth::user()->fullname . '<br>Giờ vào: ' . $checkInTime->format('H:i d-m-Y'));
        $zaloService->sendMessage('8825240549062391828', $message2);
        return redirect()->route('attendance.index')->with('success', 'Check-in thành công.');
    }

    public function checkOut(Request $request, $id)
    {
        $zaloService = new ZaloService;
       
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('id', $id)
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Không tìm thấy bản ghi chấm công để check-out.');
        }
        $checkOutTime = Carbon::now();
        $dayOfWeek = $checkOutTime->dayOfWeek;
        // Quy định giờ kết thúc và bắt đầu ca
        $morningEnd = $dayOfWeek == Carbon::SATURDAY ? Carbon::createFromTimeString('12:00') : Carbon::createFromTimeString('12:00');
        $afternoonStart = Carbon::createFromTimeString('13:00');
        $afternoonEnd = $dayOfWeek == Carbon::SATURDAY ? Carbon::createFromTimeString('15:00') : Carbon::createFromTimeString('16:30');

        // Xác định ca làm việc dựa trên giờ check-in
        $checkInTime = Carbon::parse($attendance->check_in);

        if ($checkInTime->between(Carbon::createFromTimeString('08:00'), $morningEnd)) {
            // Ca sáng
            if ($attendance->shift_status == 'not_counted') {
                // Check-in không được tính ca sáng, không cho phép check-out trước giờ bắt đầu ca chiều
                if ($checkOutTime->lessThan($afternoonEnd)) {
                    return redirect()->route('attendance.index')->withErrors('Bạn không thể check-out trước giờ bắt đầu của ca chiều vì ca sáng không được tính.');
                }
            } elseif ($checkOutTime->lessThan($morningEnd)) {
                // Check-out sớm hơn giờ kết thúc ca sáng
                return redirect()->route('attendance.index')->withErrors('Check-out sớm cho ca sáng, không được tính ca.');
            }
        } elseif ($checkInTime->between($afternoonStart, $afternoonEnd)) {
            // Ca chiều
            if ($checkOutTime->lessThan($afternoonEnd)) {
                // Check-out sớm hơn giờ kết thúc ca chiều
                return redirect()->route('attendance.index')->withErrors('Check-out sớm cho ca chiều, không được tính ca.');
            }
        }

        // Cập nhật thời gian check-out
        $attendance->update([
            'check_out' => $checkOutTime,
            'check_out_note' => $request->input('note'),
        ]);
        $message = str_replace('<br>', "\n", '<br>THÔNG BÁO CHẤM CÔNG CHECK-OUT <br>Giờ Ra: ' . $checkOutTime->format('H:i d-m-Y') . '<br>Check-Out thành công <br>Bạn cảm thấy mệt mỏi rồi đúng không :))');
        $zaloService->sendMessage(Auth::user()->zalo_user_id, $message);
        $message2 = str_replace('<br>', "\n", Auth::user()->fullname . '<br>Giờ ra: ' . $checkOutTime->format('H:i d-m-Y'));
        $zaloService->sendMessage('8825240549062391828', $message2);
        return redirect()->back()->with('success', 'Bạn đã check-out thành công.');
    }
    public function calculateFine(Carbon $checkInTime)
    {
        $fineRatesMorning = [
            ['time' => '08:31', 'amount' => 10000],
            ['time' => '08:36', 'amount' => 20000],
            ['time' => '08:46', 'amount' => 30000],
        ];
        $fineRatesSATURDAY = [
            ['time' => '09:01', 'amount' => 10000],
            ['time' => '09:06', 'amount' => 20000],
            ['time' => '09:16', 'amount' => 30000],
        ];
        $fineRatesAfternoon = [
            ['time' => '13:31', 'amount' => 10000],
            ['time' => '13:36', 'amount' => 20000],
            ['time' => '13:46', 'amount' => 30000],
        ];

        $dayOfWeek = $checkInTime->dayOfWeek;
        $shiftStartMorning = $dayOfWeek == Carbon::SATURDAY ? Carbon::createFromTimeString('08:30') : Carbon::createFromTimeString('08:00');
        $shiftEndMorning = Carbon::createFromTimeString('12:00');

        $shiftStartAfternoon = Carbon::createFromTimeString('13:00');
        $shiftEndAfternoon = $dayOfWeek == Carbon::SATURDAY ? Carbon::createFromTimeString('15:00') : Carbon::createFromTimeString('17:00');

        // Kiểm tra nếu check-in quá sớm cho ca sáng

        if ($checkInTime->lessThan($shiftStartMorning->copy())) {
            return 'early_no_shift'; // Sớm quá 30 phút cho ca sáng
        }
        // Kiểm tra nếu check-in trong thời gian ca sáng
        if ($checkInTime->between($shiftStartMorning, $shiftEndMorning)) {
            if ($dayOfWeek == Carbon::SATURDAY) {
                foreach ($fineRatesSATURDAY as $rate) {
                    $startTime = $shiftStartMorning->copy()->setTimeFromTimeString($rate['time']);
                    // Thay đổi addMinutes từ 15 sang 4 để khớp với ví dụ của bạn
                    if ($checkInTime->between($startTime, $startTime->copy()->addMinutes(4))) {
                        return $rate['amount'];
                    }
                }
            } else {
                foreach ($fineRatesMorning as $rate) {
                    $startTime = $shiftStartMorning->copy()->setTimeFromTimeString($rate['time']);
                    // Thay đổi addMinutes từ 15 sang 4 để khớp với ví dụ của bạn
                    if ($checkInTime->between($startTime, $startTime->copy()->addMinutes(4))) {
                        return $rate['amount'];
                    }
                }
            }
            if ($checkInTime->greaterThanOrEqualTo($shiftStartMorning->copy()->setTimeFromTimeString('09:00')) && $checkInTime->lessThan($shiftEndMorning->copy())) {
                return 'no_shift'; // Trễ quá giờ của ca sáng, không tính ca
            }
            return 'ok';
        }

        // Kiểm tra nếu check-in quá sớm cho ca chiều
        if ($checkInTime->lessThan($shiftStartAfternoon->copy()->subMinutes(30))) {
            return 'early_no_shift'; // Sớm quá 30 phút cho ca chiều
        }

        // Kiểm tra nếu check-in trong thời gian ca chiều
        if ($checkInTime->between($shiftStartAfternoon, $shiftEndAfternoon)) {
            foreach ($fineRatesAfternoon as $rate) {
                $startTime = $shiftStartAfternoon->copy()->setTimeFromTimeString($rate['time']);
                // Thay đổi addMinutes từ 15 sang 4 để khớp với ví dụ của bạn
                if ($checkInTime->between($startTime, $startTime->copy()->addMinutes(4))) {
                    return $rate['amount'];
                }
            }
            if ($checkInTime->greaterThanOrEqualTo($shiftStartAfternoon->copy()->setTimeFromTimeString('14:00'))) {
                return 'after_shift_time'; // Trễ quá giờ của ca chiều, không cho chấm công
            }
            return 'ok';
        }

        return null;
    }
    //tính số công thức làm
    public function calculateMonthlyWorkDays(Request $request)
    {
        // return $request->all();
        // Lấy tháng và năm từ request hoặc mặc định là tháng hiện tại
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        // Lấy ID người dùng từ request hoặc người dùng đang đăng nhập
        $userId = $request->user_id ?? Auth::id();;
        // Tính ngày đầu và ngày cuối của tháng
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        // Lọc các bản ghi chấm công trong tháng
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('check_in', [$startOfMonth, $endOfMonth])
            ->orderBy('check_in')
            ->get();
        $workDays = 0;
        $fines = 0;
        foreach ($attendances as $key => $attendance) {
            $workDays += $attendance->shift;
            if (is_numeric($attendance->fine)) { // Kiểm tra xem fine có phải là số
                $fines += $attendance->fine;
            }
        }
        if ($request->ajax) {
            return response()->json([
                'workDays' => $workDays,
                'fines' => number_format($fines)
            ]);
        }
        $workDays = [
            'workDays' => $workDays,
            'fines' => number_format($fines)
        ];
        return $workDays;
    }
    public function listByEmployeeAndMonth(Request $request)
    {
        // Lấy tháng, năm, và nhân viên từ request hoặc mặc định
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $employeeId = $request->input('user_id', null);

        // Tính ngày đầu và ngày cuối của tháng
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Truy vấn chấm công
        $query = Attendance::with('user')->whereBetween('check_in', [$startOfMonth, $endOfMonth]);

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        $attendances = $query->orderBy('check_in')->get();

        // Tính tổng số ngày công và tổng tiền phạt
        $workDays = 0;
        $workDays = $this->calculateMonthlyWorkDays($request);
        // Lấy danh sách nhân viên
        $employees = \App\Models\User::all();

        // Trả về view với dữ liệu đã tính toán
        return view('attendance.list_by_month', compact('attendances', 'employees', 'month', 'year', 'employeeId', 'workDays'));
    }
    // tính số ngày trong tháng
    function getWorkingDaysInMonth( $month,$year)
    {
        // Tạo đối tượng Carbon cho ngày đầu tiên của tháng
        $startDate = Carbon::create($year, $month, 1);

        // Lấy ngày cuối cùng của tháng
        $endDate = $startDate->copy()->endOfMonth();

        // Khởi tạo số ngày làm việc
        $workingDays = 0;

        // Duyệt qua từng ngày trong tháng
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Kiểm tra nếu không phải Chủ Nhật (Chủ Nhật có dayOfWeek = 0)
            if ($date->dayOfWeek !== Carbon::SUNDAY) {
                $workingDays++;
            }
        }

        return $workingDays;
    }
}
