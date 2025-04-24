@extends('adminlte::page')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="pt-3" id="accordianId" role="tablist" aria-multiselectable="true">
                    <div class="card collapsed-card">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title">Quy Định Chấm Công {{ now() }}</h3>
                            <div class="card-tools">
                                <!-- Collapse Button -->
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="card-body collapse">
                            <div class="table-responsive">
                                <table class="table table-bordered table-warning">
                                    <thead>
                                        <tr>
                                            <th>Ca</th>
                                            <th>Thời gian</th>
                                            <th>Phạt đi trễ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($shiftRules as $shiftName => $shiftInfo)
                                            <tr>
                                                <td>{{ $shiftName }}</td>
                                                <td>{!! $shiftInfo['Thời gian'] !!}</td>
                                                <td>
                                                    @foreach ($shiftInfo['Phạt'] as $index => $fineInfo)
                                                        {{ $fineInfo['Thời gian'] }} - {{ $fineInfo['Phạt'] }} <br>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-danger">
                                            <td colspan="3"> <strong> Không check-in sớm quá 30 phút mỗi ca làm <br>
                                                    Không check-out quá trễ 30p kết thúc ca làm
                                                </strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($completedAttendanceToday)
                    <!-- Thông báo đã hoàn thành chấm công hôm nay -->
                    <div class="alert alert-info mt-3">Bạn đã hoàn thành chấm công hôm nay.</div>
                @elseif ($currentAttendance)
                    <!-- Form Check-Out -->
                    <form action="{{ route('attendance.checkout', $currentAttendance->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="note">Ghi chú (Check-out):</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Nhập ghi chú khi check-out..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Check Out</button>
                    </form>
                @else
                    <!-- Form Check-In -->
                    <form action="{{ route('attendance.checkin') }}" method="POST">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <div class="form-group">
                            <label for="note">Ghi chú (Check-in):</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Nhập ghi chú..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Check In</button>
                    </form>
                @endif
                @hasrole('admin')
                    <div class="row">

                        <div class="col-sm-3 mt-2">
                            <!-- select -->
                            <div class="form-group">
                                <label>Chọn nhân viên</label>
                                <select class="custom-select" id="employee-select">
                                    <option value="" selected>Chọn nhân viên...</option>
                                    @foreach (App\Models\User::where('status', '1')->get() as $user)
                                        <option value="{{ route('attendance.show', $user->id) }}">{{ $user->fullname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3 mt-2">
                            <!-- select -->
                            <div class="form-group">
                                <label>Chọn tháng</label>
                                <select class="custom-select" id="employee-select">
                                    <option value="" selected>Chọn nhân viên...</option>
                                    @foreach (App\Models\User::where('status', '1')->get() as $user)
                                        <option value="{{ route('attendance.show', $user->id) }}">{{ $user->fullname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                @endhasrole
                <div id="attendance-calendar"></div>
            </div>
            <div class="col-md-4">
                <div class="box pt-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="workDay">0</h3>
                            <p>Số công thực làm</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="box pt-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="fine">0</h3>
                            <p>Số tiền đóng heo</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Chi tiết sự kiện</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="eventDetails">
                    <!-- Nội dung sự kiện sẽ được hiển thị ở đây -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <style>
        .fc-today {
            background-color: transparent !important;
            /* Loại bỏ nền cho ngày hôm nay */
        }
    </style>
@stop
@section('js')
    <!-- FullCalendar JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale-all.min.js"
        integrity="sha512-L0BJbEKoy0y4//RCPsfL3t/5Q/Ej5GJo8sx1sDr56XdI7UQMkpnXGYZ/CCmPTF+5YEJID78mRgdqRCo1GrdVKw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $('#attendance-calendar').fullCalendar({
            events: @json($events), // Truyền dữ liệu sự kiện từ controller
            themeSystem: 'bootstrap4',
            initialView: 'dayGridMonth',
            height: 'auto',
            firstDay: 1, // Bắt đầu tuần từ Thứ Hai (1)
            locale: 'vi', // Ngôn ngữ tiếng Việt
            selectable: true,
            displayEventTime: false,
            timeZone: 'Asia/Ho_Chi_Minh',
            headerToolbar: {
                start: 'dayGridMonth,timeGridWeek',
                center: 'title',
                end: 'prev,next,nextYear'
            },
            eventRender: function(event, element) {
                // Kiểm tra nếu là sự kiện nền (background event)
                if (event.rendering === 'background') {
                    element.css({
                        'background-color': event.backgroundColor,
                        'opacity': 0.5 // Điều chỉnh độ trong suốt nếu cần
                    });
                } else {
                    // Xóa title mặc định để tránh tooltip
                    element.removeAttr('title');

                    // Tùy chỉnh nội dung hiển thị trong ô sự kiện
                    element.html(`
                        <div>
                            <strong>${event.title}</strong><br>
                            Tính công:${event.shift??''} <br>
                            Ghi Chú: ${event.check_in_note??''} <br>
                            ${event.check_out_note??''}
                        </div>
                    `);

                    // Đảm bảo màu nền và màu chữ
                    element.css({
                        'background-color': event.backgroundColor,
                        'color': event.textColor
                    });
                }
            },
            eventClick: function(event) {
                // Hiển thị modal hoặc alert để hiển thị nội dung sự kiện khi nhấp vào
                // Điều chỉnh thời gian theo múi giờ Việt Nam (UTC+7)
                const checkInTime = moment(event.start).add(7, 'hours').format("HH:mm");
                const checkOutTime = event.end ? moment(event.end).add(7, 'hours').format("HH:mm") :
                    'Chưa check-out';

                $('#eventDetails').html(`
                        <p><strong>Tiêu đề:</strong> ${event.title}</p>
                        <p><strong>Ghi Chú Check-In:</strong> ${event.check_in_note ?? 'Không có ghi chú'}</p>
                        <p><strong>Ghi Chú Check-Out:</strong> ${event.check_out_note ?? 'Không có ghi chú'}</p>
                        <p><strong>Check-in:</strong> ${checkInTime}</p>
                        <p><strong>Check-out:</strong> ${checkOutTime}</p>
                    `);
                $('#eventModal').modal('show'); // Hiển thị modal
            },
            viewRender: function(view, element) {
                // Lấy tháng và năm đang hiển thị
                let startMonth = view.intervalStart.month() +
                    1; // Sử dụng month() để lấy tháng (0-11) và cộng 1 để thành 1-12
                let startYear = view.intervalStart.year();
                // Gọi AJAX để lấy số công của tháng hiện tại
                $.ajax({
                    url: "{{ route('attendance.getWorkDays') }}",
                    method: "GET",
                    data: {
                        month: startMonth,
                        year: startYear
                    },
                    success: function(response) {

                        $('#fine').text(response.fines);
                        $('#workDay').text(response.workDays);
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        toastr.error('Không thể tính số ngày công.');
                    }
                });
            },
        });
    </script>
    <script>
        $(document).ready(function() {
            // Lắng nghe sự kiện thay đổi trên dropdown
            $('#employee-select').on('change', function() {
                const selectedUrl = $(this).val(); // Lấy URL từ giá trị của option được chọn

                if (selectedUrl) {
                    // Thực hiện chuyển hướng
                    window.location.href = selectedUrl;
                }
            });
        });
    </script>
    <script>
        // Hiển thị thông báo từ server
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
            @endforeach
        @endif

        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        @if (session('warning'))
            toastr.warning('{{ session('warning') }}');
        @endif
    </script>
@stop
