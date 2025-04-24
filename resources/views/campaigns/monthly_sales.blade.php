@extends('adminlte::page')

@section('title', 'Doanh số Chiến dịch')

@section('content_header')
    <h1>Doanh số Chiến dịch</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div id="filterContainer" class="row m-2">

                <div class="col-6 col-md-3">
                    <div class="info-box dark-mode">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-wallet"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Doanh số</span>
                            <span class="info-box-number">
                                <span id='total'>222</span>
                            </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>

                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box dark-mode">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-file-invoice-dollar"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Trọn Gói</span>
                            <span class="info-box-number">
                                <span id='totalType1'>222</span>
                            </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>

                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box dark-mode">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-coins"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Ngân sách</span>
                            <span class="info-box-number">
                                <span id='totalType2'>222</span>
                            </span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>

                </div>
              
            </div>
            <div class="row">
            @role('admin|manager')

                <div class="col-12 col-md-3">
                    <div class="form-group ">
                        <label for="user_id">Chọn nhân viên:</label>
                        <select id="user_id" name="user_id" class="form-control select2" required>
                            <option value="">Chọn nhân viên</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" data-salary="{{ $user->base_salary }}">
                                    {{ $user->fullname }}
                                </option>
                            @endforeach
                        </select>  
                    </div>
                </div>
            @endrole

                <div class="col-1ƒ2 col-md-3">
                    <div class="form-group ">
                        <label for="monthYearPicker">Chọn tháng và năm:</label>
                        <input type="text" id="monthYearPicker" class="form-control" placeholder="Chọn tháng và năm">
                    </div>
                </div>
            </div>
            <table id="campaigns-table" class="table table-bordered table-hover table-success">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên Website</th>
                        <th>Thời Gian</th>
                        <th>Ngân Sách</th>
                        <th>Giá Giảm  </th>
                        <th>Thanh Toán (payment)</th>
                        <th>Số Ngày</th>
                        <th>Doanh Số</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
    <!-- Thêm CSS và JavaScript Flatpickr cùng plugin Month Select -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vi.js"></script> <!-- Locale tiếng Việt -->

@stop
@section('js')
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.colVis.min.js"></script>
    <script>
        $(document).ready(function() {
            // Khởi tạo Select2 
            $('#user_id, #monthYearPicker').on('change', function () {
               
                $('#campaigns-table').DataTable().ajax.reload();
            });
            flatpickr("#monthYearPicker", {
                dateFormat: "m-Y", // Định dạng chỉ hiển thị tháng và năm
                defaultDate: new Date(), // Đặt mặc định là ngày hiện tại
                locale: {
                    firstDayOfWeek: 1,
                    weekdays: {
                        shorthand: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                        longhand: [
                            'Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'
                        ]
                    },
                    months: {
                        shorthand: [
                            'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                            'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11',
                            'Tháng 12'
                        ],
                        longhand: [
                            'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                            'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11',
                            'Tháng 12'
                        ]
                    }
                }, // Thiết lập ngôn ngữ là tiếng Việt
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true, // Hiển thị dạng ngắn gọn cho tháng
                        dateFormat: "m-Y", // Định dạng tháng-năm
                        altFormat: "F Y" // Định dạng thay thế: Tháng Tên Năm
                    })
                ]
            });
            $('#campaigns-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: -1,
                dom: 'Bfrtip',
                buttons: [
                    'colvis',
                    'excel',
                    'print'
                ],
                ajax: {
                    url: '{{ route('campaigns.monthlySales') }}',
                    data: function(d) {
                        // Lấy tháng và năm từ input hoặc sử dụng mặc định
                        d.monthYearPicker = $('#monthYearPicker').val() || new Date().getMonth() + 1;
                        d.user_id = $('#user_id').val();
                    },
                    dataSrc: function(json) {
                        // Cập nhật thông tin doanh số vào `info-box`, chỉ lấy phần nguyên
                        $('#total').text(Math.floor(json.totalSales).toLocaleString());
                        $('#totalType1').text(Math.floor(json.totalSalesType1).toLocaleString());
                        $('#totalType2').text(Math.floor(json.totalSalesType2).toLocaleString());
                        return json.data;
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        },
                        orderable: false,

                    },
                    {
                        data: 'website_name',
                        name: 'website_name'
                    },
                    {
                        data: 'duration',
                        name: 'duration',
                        orderable: false,
                        searchable: false,
                        class: 'text-center'

                    },
                    {
                        data: 'budgetmonth',
                        name: 'budgetmonth',
                        class: 'text-right'
                    },
                    {
                        data: 'promotion',
                        name: 'promotion',
                        class: 'text-right'

                    },
                    {
                        data: 'payment',
                        name: 'payment',
                        class: 'text-right'

                    },
                    {
                        data: 'daysInMonth',
                        name: 'daysInMonth',
                        class: 'text-center'

                    },
                    {
                        data: 'sales',
                        name: 'sales',
                        class: 'text-right'

                    },


                ]
            });
        });
    </script>
@stop
