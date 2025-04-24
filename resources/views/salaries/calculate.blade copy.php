@extends('adminlte::page') <!-- Sử dụng layout của AdminLTE -->

@section('title', 'Tính lương nhân viên')

@section('content_header')
    <h1>Tính lương nhân viên</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card card-danger">
            <div class="card-header ">
                <h3 class="card-title">Thông tin tính lương</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('salaries.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">

                            <div class="card card-info">
                                <div class="card-header ">
                                    <h3 class="card-title">LƯƠNG CƠ BẢN</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
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
                                    <div class="form-group">
                                        <label for="month">Chọn tháng:</label>
                                        <input type="text" id="month" name="month" class="form-control"
                                            value="{{ now()->format('Y-m') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="base_salary">Lương cơ bản:</label>
                                        <input type="text" id="base_salary" name="base_salary" class="form-control"
                                            readonly placeholder="Lương cơ bản sẽ tự động hiển thị">
                                    </div>
                                    <!-- Ngày công làm -->
                                    <div class="form-group">
                                        <label for="work_days">Ngày công làm:</label>
                                        <input type="text" id="work_days" name="work_days" class="form-control"
                                            value="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="actual_work_days">Thực làm:</label>
                                        <input type="text" id="actual_work_days" name="actual_work_days"
                                            class="form-control" value="0" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="absent_days">Ngày nghỉ:</label>
                                        <input type="text" id="absent_days" name="absent_days" class="form-control"
                                            value="0" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="bhxh">BHXH</label>
                                        <input type="text" id="bhxh" name="bhxh" class="form-control"
                                            placeholder="Nhập bhxh" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone_allowance">Phụ cấp điện thoại:</label>
                                        <input type="text" id="phone_allowance" name="phone_allowance"
                                            class="form-control" placeholder="Nhập phụ cấp điện thoại" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="attendance_bonus">Chuyên cần:</label>
                                        <input type="text" id="attendance_bonus" name="attendance_bonus"
                                            class="form-control" placeholder="Nhập thưởng chuyên cần">
                                    </div>
                                    <div class="form-group">
                                        <label for="bonus">Thưởng:</label>
                                        <input type="text" id="bonus" name="bonus" class="form-control"
                                            placeholder="Nhập thưởng">
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="form-group">
                                        <label for="real_salary">Lương thực tế</label>
                                        <input type="text" id="real_salary" name="real_salary" class="form-control"
                                            value="0" readonly>

                                        <input type="hidden" id="t_total_salary" name="t_total_salary" class="form-control"
                                            value="">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            @includeIf('salaries.partials.salary_slip')
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <div class="card-header ">
                                    <h3 class="card-title">HÌNH THỨC LƯƠNG</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group form-inline">
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input custom-control-input-danger"
                                                        type="radio" id="fixed_salary" name="salary_type"
                                                        checked="" value="0">
                                                    <label for="fixed_salary" class="custom-control-label">Cố định</label>
                                                </div>
                                                <div class="custom-control custom-radio ml-2">
                                                    <input
                                                        class="custom-control-input custom-control-input-danger custom-control-input-outline"
                                                        type="radio" id="kpi_salary" name="salary_type"
                                                        value="1">
                                                    <label for="kpi_salary" class="custom-control-label">KPI</label>
                                                </div>
                                            </div>
                                            <div id="type_sa" style="display: none;">
                                                <div class="form-group">
                                                    <label for="doanhSo">Doanh số:</label>
                                                    <input type="number" id="doanhSo" name="doanhSo"
                                                        class="form-control" placeholder="Nhập Doanh số (nếu có)">
                                                </div>
                                                <div class="form-group">
                                                    <label for="kpi">KPI:</label>
                                                    <input type="number" id="kpi" name="kpi"
                                                        class="form-control" placeholder="Nhập KPI (nếu có)" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Bảng KPI -->
                                            <div id="kpi_table" class="card" style="display: none;">
                                                <div class="card-header">
                                                    <h3 class="card-title">Thông tin KPI</h3>
                                                    <button type="button" class="btn btn-success float-right"
                                                        id="addNewKpi">Thêm mới</button>

                                                </div>
                                                <div class="card-body">
                                                    <table id="tbl_kpi_table" class="display table table-border"
                                                        style="width: 100%">
                                                        <thead>
                                                            <tr>
                                                                <th>Doanh Thu</th>
                                                                <th>Hoa Hồng</th>
                                                                <th>Hành Động</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Dữ liệu sẽ được thêm tự động từ DataTables -->
                                                        </tbody>
                                                    </table>

                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <!-- Chi phí khác -->
                        <div class="col-md-12">
                            <div class="card card-info">
                                <div class="card-header ">
                                    <h3 class="card-title">CHI PHÍ PHÁT SINH</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group form-inline">
                                        <label for="other_expenses">Tổng Chi Phí #</label>
                                        <input type="number" id="other_expenses" name="other_expenses"
                                            class="form-control ml-3" placeholder="Nhập chi phí khác">

                                    </div>
                                    <!-- Bảng Chi Phí Khác -->
                                    <div id="other_cost_table" class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Chi tiết chi phí khác</h3>
                                            <button type="button" class="btn btn-success float-right"
                                                id="addNewRow">Thêm mới</button>

                                        </div>
                                        <div class="card-body">
                                            <table class="table table-bordered" id="tbl_other_cost_table">
                                                <thead>
                                                    <tr>
                                                        <th>Khách hàng</th>
                                                        <th>Dịch vụ</th>
                                                        <th>Doanh thu</th>
                                                        <th>Hoa hồng</th>
                                                        <th>Ngày</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="other_cost_table_body">
                                                    <!-- Dữ liệu chi phí khác sẽ được thêm động qua AJAX -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Tính lương</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
    @include('salaries.other_costs.edit')
    @include('salaries.other_costs.destroy')
    @include('salaries.other_costs.create')
    @include('salaries.kpi.edit')
@stop

@section('css')

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .form-group label {
            font-weight: bold;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.dataTables.min.css">
    @routes()
@stop

@section('js')

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>


    <script>
        // Khởi tạo Select2
        $('.select2').select2({
            placeholder: '-- Chọn một nhân viên --',
            allowClear: true
        });

        // Khởi tạo Flatpickr cho chọn tháng
        $('#month').flatpickr({
            dateFormat: "Y-m", // Định dạng tháng/năm
            altInput: true, // Hiển thị input thay thế
            altFormat: "F Y", // Hiển thị tháng/năm dạng chữ
            locale: "vn", // Ngôn ngữ tiếng Việt
            shorthand: true, // Hiển thị tháng ngắn gọn
            longhand: false,
            plugins: [
                new monthSelectPlugin({
                    shorthand: true, // Hiển thị tháng ngắn gọn
                    longhand: false,
                    dateFormat: "Y-m", // Định dạng lưu trữ tháng/năm
                    altFormat: "F Y" // Hiển thị tháng/năm dạng chữ
                })
            ]
        });
        // Ẩn/hiện bảng KPI khi chọn radio button
        $('input[name="salary_type"]').on('change', toggleKpiTable);

        //ẩn hiện khi radio check kpi
        function toggleKpiTable() {
            if ($('#kpi_salary').is(':checked')) {
                $('#kpi_table').slideDown(); // Hiển thị bảng KPI
                $('#type_sa').slideDown(); // Hiển thị bảng KPI

            } else {
                $('#kpi_table').slideUp(); // Ẩn bảng KPI
                $('#type_sa').slideUp(); // Ẩn bảng KPI

            }
        }

        //bonus thưởng thay đổi phiếu lương cũng thay đổi
        $('#bonus').on('keyup', function() {
            $('#rs_bonus').text($(this).val())

        });
        //bonus thưởng thay đổi phiếu lương cũng thay đổi
        $('#attendance_bonus').on('keyup', function() {
            $('#rs-attendance_bonus').text(new Intl.NumberFormat().format($(this)
                .val())); // Format theo định dạng số            )
            cost_tong_hop()
        });
        $('#month').on('change', function() {
            let month = $(this).val();
            let userId = $('#user_id').val();

            // Nếu không có nhân viên được chọn, ẩn bảng KPI
            if (!userId) {
                $('#kpi_table').slideUp();
                $('#kpi_table_body').empty(); // Xóa dữ liệu KPI cũ
                return;
            }
            // Gửi yêu cầu AJAX
            $.ajax({
                url: route('salaries.expected'), // Đường dẫn xử lý lấy KPI
                method: 'GET',
                data: {
                    user_id: userId,
                    date: month,
                },
                success: function(response) {
                    $('#base_salary').val(response.base_salary) //lương cơ bản
                    $('#bhxh').val(-1 * response.bhxh) //lương cơ bản
                    $('#rs-bhxh').text(parseInt(-1 * response.bhxh).toLocaleString(
                        'vi-VN')) //lương cơ bản
                    $('#work_days').val(response.work_days) //ngày công 
                    $('#actual_work_days').val(response.actual_work_days.workDays) // ngày công thực làm
                    $('#doanhSo').val(response.doanhSo)
                    if (response.work_days == response.actual_work_days.workDays) {
                        $('#attendance_bonus').val('300000')
                        $('#rs-attendance_bonus').text($('#attendance_bonus').val())
                    }
                    $('#phone_allowance').val(response.phone_allowance)

                    absent_days() //lấy ngày nghỉ của tháng
                    real_salary() // lấy ngày công thực làm
                    $('#rs-phone_allowance').text(parseInt(response.phone_allowance).toLocaleString(
                        'vi-VN'))
                    if (response.salary_type == 1) {
                        loadKpis(userId) //lấy danh sách kpi
                    } else {
                        $('#fixed_salary').prop('checked', true);
                        toggleKpiTable()
                    }
                    refreshOtherCostTable(userId, month)
                    calculateAndDisplayKpi().then(() => {
                        total_salary();
                    });
                },
                error: function() {
                    alert('Lỗi khi tải dữ liệu KPI.');
                }

            });



        });
        // Khi chọn nhân viên, gửi AJAX để lấy dữ liệu KPI
        $('#user_id').on('change', function() {
            let userId = $(this).val();
            let month = $('#month').val();

            // Nếu không có nhân viên được chọn, ẩn bảng KPI
            if (!userId) {
                $('#kpi_table').slideUp();
                $('#kpi_table_body').empty(); // Xóa dữ liệu KPI cũ
                $('#type_sa').slideUp(); // ẩn bảng KPI
                $('#other_cost_table').slideUp();
                $('#other_cost_table_body').empty(); // Xóa dữ liệu cũ
                return;
            }
            // Gửi yêu cầu AJAX
            $.ajax({
                url: route('salaries.expected'), // Đường dẫn xử lý lấy KPI
                method: 'GET',
                data: {
                    user_id: userId,
                    date: month,
                },
                success: function(response) {
                    $('#base_salary').val(response.base_salary) //lương cơ bản
                    $('#bhxh').val(-1 * response.bhxh) //lương cơ bản
                    $('#rs-bhxh').text(parseInt(-1 * response.bhxh).toLocaleString(
                        'vi-VN')) //lương cơ bản
                    $('#work_days').val(response.work_days) //ngày công 
                    $('#actual_work_days').val(response.actual_work_days.workDays) // ngày công thực làm
                    $('#doanhSo').val(response.doanhSo)
                    if (response.work_days == response.actual_work_days.workDays) {
                        $('#attendance_bonus').val('300000')
                        $('#rs-attendance_bonus').text($('#attendance_bonus').val())
                    }
                    $('#phone_allowance').val(response.phone_allowance)

                    absent_days() //lấy ngày nghỉ của tháng
                    real_salary() // lấy ngày công thực làm
                    $('#rs-phone_allowance').text(parseInt(response.phone_allowance).toLocaleString(
                        'vi-VN'))
                    if (response.salary_type == 1) {
                        $('#kpi_salary').prop('checked', true);
                        $('#kpi_table').slideDown();

                        $('#type_sa').slideDown(); // Hiển thị bảng KPI
                        loadKpis(userId) //lấy danh sách kpi
                    } else {
                        $('#fixed_salary').prop('checked', true);
                        toggleKpiTable()
                    }
                    refreshOtherCostTable(userId, month)
                    calculateAndDisplayKpi().then(() => {
                        total_salary();
                    });
                },
                error: function() {
                    alert('Lỗi khi tải dữ liệu KPI.');
                }

            });
        });
    </script>
    @includeIf('salaries.refreshOtherCostTable')
    <script>
        // Hàm tính tổng cột "hoa_hong"
        function calculateTotalCommission() {
            let totalCommission = 0;

            // Duyệt qua tất cả các dòng trong bảng
            $('#tbl_other_cost_table tbody tr').each(function() {
                const commission = parseFloat($(this).find('td:eq(3)').text().replace(/,/g, '')) ||
                    0; // Lấy giá trị cột hoa hồng
                totalCommission += commission; // Cộng dồn vào tổng
            });

            // Hiển thị tổng vào input #other_expenses
            $('#other_expenses').val(totalCommission); // Hiển thị định dạng số
            $('#rs-orthercost').text(parseInt(totalCommission).toLocaleString('vi-VN')); // Hiển thị định dạng số
            cost_tong_hop()
        }

        // Gọi hàm mỗi khi DataTable được làm mới
        $('#tbl_other_cost_table').on('draw.dt', function() {
            calculateTotalCommission();
        });


        //thêm chi phí
        $('#addNewRow').on('click', function() {
            $('#addForm')[0].reset(); // Reset form
            $('#addModal').modal('show'); // Hiển thị modal
        });
        $('#addForm').on('submit', function(e) {
            e.preventDefault(); // Ngăn form submit thông thường
            let month = $('#month').val();
            let userId = $('#user_id').val();

            let data = {
                user_id: userId,
                khach_hang: $('#newKhachHang').val(),
                dich_vu: $('#newDichVu').val(),
                doanh_thu: $('#newDoanhThu').val(),
                hoa_hong: $('#newHoaHong').val(),
                date: month,
                _token: '{{ csrf_token() }}', // Laravel CSRF token
            };

            $.ajax({
                url: route('other-costs.store'),
                type: 'POST',
                data: data,
                success: function(response) {
                    $('#addModal').modal('hide'); // Ẩn modal
                    table.ajax.reload(); // Làm mới bảng
                    toastr.success('Thêm mới thành công!');
                    calculateAndDisplayKpi().then(() => {
                        total_salary();
                    });
                    calculateTotalCommission();

                },
                error: function(xhr) {
                    toastr.error('Lỗi khi thêm mới: ' + xhr.responseText);
                }
            });
        });

        // Sự kiện khi bấm nút "Sửa"
        $('#tbl_other_cost_table').on('click', '.edit-btn', function() {
            var rowData = table.row($(this).parents('tr')).data();

            // Đưa dữ liệu vào modal
            $('#rowId').val(rowData.id);
            $('#khach_hang').val(rowData.khach_hang);
            $('#dich_vu').val(rowData.dich_vu);
            $('#doanh_thu').val(rowData.doanh_thu);
            $('#hoa_hong').val(rowData.hoa_hong);

            // Hiển thị modal
            $('#editModal').modal('show');
        });
        // Gửi AJAX để lưu thay đổi
        $('#editForm').on('submit', function(e) {
            e.preventDefault();

            var id = $('#rowId').val();
            var data = {
                user_id: $('#user_id').val(),
                khach_hang: $('#khach_hang').val(),
                dich_vu: $('#dich_vu').val(),
                doanh_thu: $('#doanh_thu').val(),
                hoa_hong: $('#hoa_hong').val(),
                _token: '{{ csrf_token() }}', // Laravel CSRF token

            };

            $.ajax({
                url: route('other-costs.update', id),
                type: 'PUT',
                data: data,
                success: function(response) {
                    $('#editModal').modal('hide'); // Ẩn modal
                    table.ajax.reload(); // Làm mới bảng
                    calculateAndDisplayKpi().then(() => {
                        total_salary();
                    });
                    toastr.success('Cập nhật thành công!');
                },
                error: function(xhr) {
                    toastr.error('Lỗi khi cập nhật: ' + xhr.responseText);
                }
            });
        });
        //sự kiện khi bấm nút xoá
        $('#tbl_other_cost_table').on('click', '.delete-btn', function() {
            const rowData = table.row($(this).parents('tr')).data(); // Lấy dữ liệu dòng
            $('#deleteRowId').val(rowData.id); // Đặt ID dòng vào input ẩn
            $('#deleteModal').modal('show'); // Hiển thị modal xác nhận
        });

        // Xử lý khi xác nhận xóa
        $('#confirmDelete').on('click', function() {
            const id = $('#deleteRowId').val(); // Lấy ID từ input ẩn

            // Gửi AJAX yêu cầu xóa
            $.ajax({
                url: route('other-costs.destroy', id),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}', // Laravel CSRF token
                },
                success: function(response) {
                    $('#deleteModal').modal('hide'); // Ẩn modal
                    table.ajax.reload(); // Làm mới bảng
                    calculateAndDisplayKpi().then(() => {
                        total_salary();
                    });
                    toastr.success('Xóa thành công!');
                },
                error: function(xhr) {
                    toastr.error('Lỗi khi xóa: ' + xhr.responseText);
                }
            });
        });

        //modal table
        $('#editModal').on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove(); // Xóa backdrop
            $('body').removeClass('modal-open'); // Xóa class modal-open khỏi body
        });

        function closeModal() {
            $('#editModal').modal('hide');
            $('.modal-backdrop').remove(); // Xóa backdrop
            $('body').removeClass('modal-open'); // Xóa class modal-open khỏi body
        }
    </script>
    <!-- //tính lương và công  -->
    <script>
        $('#work_days').keyup(function(e) {
            absent_days()
            real_salary()
        });
        $('#actual_work_days').keyup(function(e) {
            absent_days()
            real_salary()
        });

        function real_salary() {
            var base_salary = parseInt($('#base_salary').val()) || 0;
            var work_days = parseInt($('#work_days').val()) || 0;
            var actual_work_days = parseInt($('#actual_work_days').val()) || 0;

            if (work_days > 0) {
                var real_salary = Math.round((base_salary / work_days) * actual_work_days);
                $('#real_salary').val(real_salary);
                $('#rs-real-salary').text(real_salary.toLocaleString('vi-VN'));
                $('#rs_actual_work_days').text(actual_work_days);
                $('#rs-work-days').text(work_days);
                $('#rs-base-salary').text(base_salary.toLocaleString('vi-VN'));
                $('#rs-rice').text((actual_work_days * 20000).toLocaleString('vi-VN'));
            } else {
                $('#real_salary').val(0);
                $('#rs-real-salary').text('0');
                $('#rs_actual_work_days').text('0');
                $('#rs-work-days').text('0');
                $('#rs-base-salary').text('0');
                $('#rs-rice').text('0');
            }
        }

        function absent_days() {
            var work_days = $('#work_days').val()
            var actual_work_days = $('#actual_work_days').val()
            $('#absent_days').val(work_days - actual_work_days)
        }
    </script>
    <!-- tính hoa hồng kpi -->
    <script>
        $('#addNewKpi').on('click', function() {
            $('#kpiForm')[0].reset(); // Reset form
            $('#kpiRowId').val(''); // Xóa ID dòng
            $('#kpiModal').modal('show'); // Hiển thị modal
        });

        $('#kpiForm').on('submit', function(e) {
            e.preventDefault();
            if (!$('#user_id').val()) {
                toastr.error('Vui lòng chọn Nhân Viên!');
                return;
            }

            const data = {
                user_id: $('#user_id').val(),
                doanh_thu: $('#kpiDoanhThu').val(),
                hoa_hong: $('#kpiHoaHong').val(),
                _token: '{{ csrf_token() }}',
            };

            const kpiId = $('#kpiRowId').val();
            console.log(kpiId)
            const url = kpiId ? route('kpis.update', kpiId) : route('kpis.store');
            const method = kpiId ? 'PUT' : 'POST';
            $.ajax({
                url: url,
                type: method,
                data: data,
                success: function(response) {
                    $('#kpiModal').modal('hide'); // Ẩn modal
                    loadKpis(user_id); // Làm mới bảng
                    toastr.success(kpiId ? 'Cập nhật thành công!' : 'Thêm mới thành công!');
                },
                error: function(xhr) {
                    toastr.error('Lỗi: ' + xhr.responseText);
                }
            });
        });

        //xoá kpi 
        $('#tbl_kpi_table').on('click', '.delete-kpi-btn', function() {
            const kpiId = $(this).data('id'); // Lấy ID của KPI từ nút
            let userId = $('#user_id').val();

            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                $.ajax({
                    url: route('kpis.destroy', kpiId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        loadKpis(userId); // Làm mới bảng
                        toastr.success('Xóa thành công!');
                    },
                    error: function(xhr) {
                        toastr.error('Lỗi: ' + xhr.responseText);
                    }
                });
            }
        });

        //sửa kpi
        $('#tbl_kpi_table').on('click', '.edit-kpi-btn', function() {
            const kpiId = $(this).data('id'); // Lấy ID của KPI
            // Gửi AJAX để lấy thông tin KPI
            $.ajax({
                url: route('kpis.show', kpiId),
                type: 'GET',
                success: function(response) {
                    // Điền dữ liệu vào form modal
                    $('#kpiDoanhThu').val(response.doanh_thu);
                    $('#kpiHoaHong').val(response.hoa_hong);
                    $('#kpiRowId').val(response.id);

                    // Hiển thị modal
                    $('#kpiModal').modal('show');
                },
                error: function(xhr) {
                    toastr.error('Lỗi khi lấy dữ liệu KPI: ' + xhr.responseText);
                },
            });
        });

        // khởi tạo DataTable khi có thay đổi
        function loadKpis(userId) {
            if ($.fn.DataTable.isDataTable('#tbl_kpi_table')) {
                $('#tbl_kpi_table').DataTable().destroy(); // Hủy DataTable cũ
            }
            $('#tbl_kpi_table').DataTable({
                processing: true,
                serverSide: true,
                // Tắt tính năng tìm kiếm
                searching: false,
                // Tắt tính năng hiển thị thông tin số bản ghi
                info: false,

                // Tắt tính năng phân trang
                paging: false,
                // Tắt tính năng chọn số dòng hiển thị
                lengthChange: false,
                ajax: {
                    url: route('kpis.data'), // Đường dẫn API trả về dữ liệu JSON
                    type: 'GET',
                    data: {
                        user_id: $('#user_id').val(),
                    },
                },

                columns: [{
                        data: 'doanh_thu',
                        name: 'doanh_thu'
                    },
                    {
                        data: 'hoa_hong',
                        name: 'hoa_hong'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json', // Đổi ngôn ngữ sang tiếng Việt
                },
                initComplete: function() {
                    // Hiển thị bảng KPI
                    calculateAndDisplayKpi().then(() => {
                        total_salary();
                    });
                    toggleKpiTable()
                },
            })
        }
    </script>
    @includeIf('salaries.kpi.js_calculateKpi')
    @includeIf('salaries.js_cost_tong_hop')
    <script>
        // Hiển thị thông báo lỗi từ server
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
            @endforeach
        @endif

        // Hiển thị thông báo thành công (nếu có)
        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @endif
    </script>
@stop
