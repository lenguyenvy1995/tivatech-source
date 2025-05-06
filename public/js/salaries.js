$(document).ready(function () {
    // Khởi tạo Select2
    $('.select2').select2({
        placeholder: '-- Chọn nhân viên --',
        allowClear: true,
    });

    // Khởi tạo Flatpickr
    $('#month').flatpickr({
        dateFormat: "Y-m",
        altInput: true,
        altFormat: "F Y",
        locale: "vn",
        shorthand: true,
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y"
            })
        ]
    });
    function updateAttendanceLink() {
        const userId = $('#user_id').val();
        const yearMonth = $('#month').val();
        let [year, month] = yearMonth.toString().split('-'); // Tách year và month từ chuỗi 'YYYY-M'
        let formattedMonth = parseInt(month, 10); // Chuyển month thành số nguyên (loại bỏ số 0 nếu có)

        let newHref = `/attendance/list?month=${formattedMonth}&year=${year}&user_id=${userId}`;
        $('#attendance').attr('href', newHref);

    }

    // Sự kiện
    $('#user_id, #month').on('change', function () {
        refreshOtherCostTable(); // Gọi lại hàm khởi tạo lại bảng
        fetchSalaryDetails()
        updateAttendanceLink()
    });
    $('#doanhSo').on('keyup', handleKpiCalculation);
    $('#bonus, #actual_salary,#actual_work_days, #kpi, #phone_allowance, #attendance_bonus, #bhxh').on('input', calculateSalary);
    $('input[name="salary_type"]').on('change', toggleKpiTable);
    $('#actual_work_days').on('input', function () {
        calculateSalary();
    });
    function toggleKpiTable() {
        if ($('#kpi_salary').is(':checked')) {
            $('#kpi_table, #type_sa').slideDown();
        } else {
            $('#kpi_table, #type_sa').slideUp();
        }
    }

    function fetchSalaryDetails() {
        const userId = $('#user_id').val();
        const month = $('#month').val();

        if (!userId) {
            clearSalaryFields();
            return;
        }

        $.ajax({
            url: route('salaries.expected'),
            method: 'GET',
            data: { user_id: userId, date: month },
            success: function (response) {
                // Kiểm tra loại lương (salary_type)
                if (response.salary_type == 1) {
                    $('#kpi_salary').prop('checked', true); // Chọn lương cố định
                    loadKpis(userId); // Gọi danh sách KPI

                } else {
                    $('#fixed_salary').prop('checked', true); // Chọn lương cố định
                }
                toggleKpiTable()
                // Cập nhật các trường lương
                populateSalaryFields(response);
            },
            error: function () {
                toastr.error('Lỗi khi tải dữ liệu.');
            }
        });
    }

    function populateSalaryFields(response) {
        $('#base_salary').val(response.base_salary);
        $('#bhxh').val(-1 * response.bhxh);
        $('#work_days').val(response.work_days);
        $('#actual_work_days').val(response.actual_work_days.workDays);
        $('#phone_allowance').val(response.phone_allowance);
        $('#attendance_bonus, #bonus').val(response.attendance_bonus || 0);
        $('#doanhSo').val(response.doanhSo);
        calculateSalary();
    }

    function clearSalaryFields() {
        $('#base_salary, #bonus, #bhxh, #work_days, #actual_work_days, #phone_allowance, #attendance_bonus').val('');
        $('#kpi_table_body, #other_cost_table_body').empty();
        toggleKpiTable();
    }

    function calculateSalary() {
        calculateAndDisplayKpi().then(() => {
            const data = {
                luongCoBan: parseFloat($('#base_salary').val()) || 0,
                ngayCongLam: parseInt($('#work_days').val()) || 26,
                thucLam: parseFloat($('#actual_work_days').val()) || 0,
                phuCap: parseFloat($('#phone_allowance').val()) || 0,
                bhxh: parseFloat($('#bhxh').val()) || 0,
                chuyenCan: parseFloat($('#attendance_bonus').val()) || 0,
                thuong: parseFloat($('#bonus').val()) || 0,
            };

            let luongThuclam = data.thucLam ? Math.round((data.luongCoBan / data.ngayCongLam) * data.thucLam) : 0;
            let luongThucTe = luongThuclam + data.phuCap + data.bhxh + data.chuyenCan + data.thuong;

            $('#actual_salary').val(luongThucTe.toFixed(0));
            updateSalarySlip(data, luongThucTe, luongThuclam);
            total_salary();
        }).catch(error => console.error('Lỗi khi tính KPI:', error));
    }

    function updateSalarySlip(data, luongThucTe, luongThuclam) {
        $('#rs-base-salary').text(formatNumber(data.luongCoBan));
        $('#rs-work-days').text(data.ngayCongLam);
        $('#rs_actual_work_days').text(data.thucLam);
        $('#rs-real-salary').text(formatNumber(luongThuclam));
        $('#rs-phone_allowance').text(formatNumber(data.phuCap));
        $('#rs-bhxh').text(formatNumber(data.bhxh));
        $('#rs-attendance_bonus').text(formatNumber(data.chuyenCan));
        $('#rs_rice').text(formatNumber(data.thucLam * 20000)); // Thưởng
        $('#rs_bonus').text(formatNumber(data.thuong));
        $('#rs-tong-hop').text(formatNumber(cost_tong_hop()));
        $('#rs-total-salary').text(formatNumber(luongThucTe));
    }
    function cost_tong_hop() {
        let bhxh = parseFloat($('#bhxh').val()) || 0;
        let attendance_bonus = parseFloat($('#attendance_bonus').val()) || 0;
        let pc_rice = parseFloat($('#actual_work_days').val()) * 20000 || 0;
        let pc_phone = parseFloat($('#phone_allowance').val()) || 0;
        let bonus = parseFloat($('#bonus').val()) || 0;
        let other_expenses = parseFloat($('#other_orther').val()) || 0;
        let other_cost = parseFloat($('#rs-othercost').text().replace(/,/g, '')) || 0; // Tổng chi phí phát sinh

        let total = bhxh + attendance_bonus + pc_rice + pc_phone + bonus + other_expenses + other_cost;
        $('#rs-tong-hop').text(formatNumber(total));
        return total;
    }
    function total_salary() {
        let real_salary = parseFloat($('#rs-real-salary').text().replace(/[,.]/g, '')) || 0; // Lương thực tế       
        let kpi = parseFloat($('#rs-kpi').text().replace(/[,.]/g, '')) || 0; // Tổng chi phí phát sinh        
        let other_cost = parseFloat($('#rs-tong-hop').text().replace(/[,.]/g, '')) || 0; // Tổng chi phí phát sinh        
        let total_salary = real_salary + kpi + other_cost;
        console.log(real_salary);
        console.log(kpi);
        console.log(other_cost);
        console.log(total_salary);
        
        $('#t_total_salary').val(total_salary);
        $('#rs-total-salary').text(formatNumber(total_salary));
    }

    function handleKpiCalculation() {
        calculateAndDisplayKpi().then(() => total_salary());
    }

    function calculateAndDisplayKpi() {
        return new Promise((resolve, reject) => {
            if ($('#kpi_salary').is(':checked')) {
                const doanhSo = parseFloat($('#doanhSo').val());
                if (isNaN(doanhSo) || doanhSo <= 0) {
                    toastr.warning('Vui lòng nhập doanh số hợp lệ!');
                    reject('Doanh số không hợp lệ');
                    return;
                }
                const kpi = calculateKpi(doanhSo, getKpiTableFromHtml());
                if (isNaN(kpi)) {
                    toastr.error('Có lỗi trong tính toán KPI.');
                    reject('KPI không hợp lệ');
                    return;
                }
                $('#kpi').val(kpi);
                $('#rs-doanhSo').text(doanhSo.toLocaleString('vi-VN'));
                $('#rs-kpi').text(kpi.toLocaleString('vi-VN'));
                resolve();
            } else {
                $('#kpi').val(0);
                $('#rs-doanhSo, #rs-kpi').text(0);
                resolve();
            }
        });
    }

    function getKpiTableFromHtml() {
        let kpiTable = [];
        $('#tbl_kpi_table tbody tr').each(function () {
            const doanhThu = parseInt($(this).find('td').eq(0).text().replace(/[.,]/g, ''));
            const hoaHong = parseFloat($(this).find('td').eq(1).text());
            if (!isNaN(doanhThu) && !isNaN(hoaHong)) kpiTable.push({ doanhThu, hoaHong });
        });
        kpiTable.push({ doanhThu: Infinity, hoaHong: kpiTable[kpiTable.length - 1]?.hoaHong || 0 });
        return kpiTable;
    }

    function calculateKpi(doanhSo, kpiTable) {
        let hoaHong = 0, remainingDoanhSo = doanhSo;
        for (let i = 0; i < kpiTable.length; i++) {
            const currentMoc = kpiTable[i];
            const nextMoc = kpiTable[i + 1] || { doanhThu: Infinity };
            if (isNaN(currentMoc.doanhThu) || isNaN(currentMoc.hoaHong)) continue;
            const range = Math.min(remainingDoanhSo, nextMoc.doanhThu - currentMoc.doanhThu);
            if (range > 0) {
                hoaHong += range * (currentMoc.hoaHong / 100);
                remainingDoanhSo -= range;
            }
            if (remainingDoanhSo <= 0) break;
        }
        return parseFloat(hoaHong.toFixed(0));
    }

    function formatNumber(value) {
        if (!value) return '0';

        const isNegative = value < 0;
        let numberStr = String(Math.abs(value)).replace(/\D/g, '');
        numberStr = numberStr.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        return isNegative ? `-${numberStr}` : numberStr;
    }
    function loadKpis(userId) {
        if ($.fn.DataTable.isDataTable('#tbl_kpi_table')) $('#tbl_kpi_table').DataTable().destroy();
        $('#tbl_kpi_table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            info: false,
            paging: false,
            lengthChange: false,
            ajax: {
                url: route('kpis.data'),
                type: 'GET',
                data: { user_id: userId },
            },
            columns: [{ data: 'doanh_thu' }, { data: 'hoa_hong' }, { data: 'actions', orderable: false, searchable: false }],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' },
        }).on('draw', () => {
            calculateAndDisplayKpi().then(() => total_salary());
        });
    }
    // Hiển thị modal thêm mới KPI
    $('#addNewKpi').on('click', function () {
        $('#kpiForm')[0].reset(); // Reset form
        $('#kpiRowId').val(''); // Xóa ID dòng
        $('#kpiModal').modal('show'); // Hiển thị modal
    });


    // Xử lý form thêm/sửa KPI
    $('#kpiForm').on('submit', function (e) {
        e.preventDefault();

        const userId = $('#user_id').val();
        if (!userId) {
            toastr.error('Vui lòng chọn nhân viên trước khi thêm/sửa KPI.');
            return;
        }

        const kpiId = $('#kpiRowId').val();
        const data = {
            user_id: userId,
            id: kpiId || null,
            doanh_thu: $('#kpiDoanhThu').val(),
            hoa_hong: $('#kpiHoaHong').val(),
            _token: $('input[name="_token"]').val(),
        };

        // Gửi yêu cầu AJAX
        $.ajax({
            url: kpiId ? route('kpis.update', kpiId) : route('kpis.store'),
            method: kpiId ? 'PUT' : 'POST',
            data: data,
            success: function (response) {
                toastr.success(response.message);
                $('#kpiModal').modal('hide'); // Ẩn modal
                $('#kpiForm')[0].reset(); // Reset form
                refreshKpiTable(); // Làm mới bảng KPI
            },
            error: function (xhr) {
                toastr.error('Có lỗi xảy ra, vui lòng thử lại!');
                console.error(xhr.responseJSON);
            },
        });
    });

    function refreshKpiTable() {
        if ($.fn.DataTable.isDataTable('#tbl_kpi_table')) {
            $('#tbl_kpi_table').DataTable().ajax.reload(null, false); // Làm mới dữ liệu
        }

        // Tính lại KPI và tổng lương khi dữ liệu được tải lại
        calculateAndDisplayKpi().then(() => {
            total_salary();
        });
    }

    $('#tbl_kpi_table').on('click', '.edit-kpi-btn', function () {
        const id = $(this).data('id');
        const doanhThu = $(this).data('doanh-thu');
        const hoaHong = $(this).data('hoa-hong');

        // Gán dữ liệu vào modal
        $('#kpiRowId').val(id);
        $('#kpiDoanhThu').val(doanhThu);
        $('#kpiHoaHong').val(hoaHong);

        // Cập nhật tiêu đề modal
        $('#kpiModalLabel').text('Sửa KPI');
        $('#kpiModal').modal('show');
    });

    // Xử lý khi nhấn nút "Xóa KPI"
    $('#tbl_kpi_table').on('click', '.delete-kpi-btn', function () {
        const id = $(this).data('id');
        if (confirm('Bạn có chắc chắn muốn xóa KPI này?')) {
            const data = {
                id: id,
                _token: $('input[name="_token"]').val(),
            };
            $.ajax({
                url: route('kpis.destroy', id),
                type: 'DELETE',
                data: data,
                success: function (response) {
                    toastr.success(response.message);
                    $('#tbl_kpi_table').DataTable().ajax.reload(null, false); // Làm mới dữ liệu

                },
                error: function () {
                    toastr.error('Có lỗi xảy ra khi xóa KPI!');
                },
            });
        }
    });
    $('#tbl_kpi_table').on('click', '.delete-kpi-btn, .edit-kpi-btn', function () {
        setTimeout(() => {
            calculateAndDisplayKpi().then(() => {
                total_salary();
            });
        }, 500);
    });
    $('#tbl_kpi_table').on('input change', 'input, select, textarea', function () {
        calculateAndDisplayKpi().then(() => {
            total_salary();
        }).catch(error => {
            console.error('Lỗi khi tính KPI:', error);
        });
    });
    // Khởi tạo DataTable cho chi phí phát sinh
    function loadOtherCosts() {
        const userId = $('#user_id').val();
        const month = $('#month').val();

        if ($.fn.DataTable.isDataTable('#tbl_other_cost_table')) {
            $('#tbl_other_cost_table').DataTable().destroy();
            $('#other_cost_table_body').empty();
        }

        $('#tbl_other_cost_table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            info: false,
            paging: false,
            lengthChange: false,
            ajax: {
                url: route('other-costs.getOtherCosts'),
                type: 'GET',
                data: { user_id: userId, month: month },
                dataSrc: 'data'
            },
            columns: [
                { data: 'khach_hang', name: 'khach_hang' },
                { data: 'dich_vu', name: 'dich_vu' },
                {
                    data: 'doanh_thu',
                    name: 'doanh_thu',
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',', '.', 0)
                },
                {
                    data: 'hoa_hong',
                    name: 'hoa_hong',
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',', '.', 0)
                },
                { data: 'date', name: 'date', className: 'text-center' },
                {
                    data: null,
                    className: 'text-center',
                    defaultContent: `
                            <button class="btn btn-sm btn-primary edit-cost-btn" type="button">Sửa</button>
                            <button class="btn btn-sm btn-danger delete-cost-btn"  type="button">Xóa</button>
                        `
                }
            ],
            language: {
                processing: "Đang xử lý...",
                zeroRecords: "Không tìm thấy dữ liệu",
                paginate: { first: "Đầu", last: "Cuối", next: "Tiếp", previous: "Trước" }
            }
        });
    }

    // Hiển thị modal thêm mới chi phí phát sinh
    $('#addNewRow').on('click', function () {
        $('#addForm')[0].reset();
        $('#addModal').modal('show');
    });

    // Xử lý form thêm mới chi phí
    $('#addForm').on('submit', function (e) {
        e.preventDefault();

        const data = {
            user_id: $('#user_id').val(),
            khach_hang: $('#newKhachHang').val(),
            date: $('#costDate').val(),
            dich_vu: $('#newDichVu').val(),
            doanh_thu: $('#newDoanhThu').val(),
            hoa_hong: $('#newHoaHong').val(),
            _token: $('input[name="_token"]').val(),
        };

        $.ajax({
            url: route('other-costs.store'),
            method: 'POST',
            data: data,
            success: function (response) {
                toastr.success(response.message);
                $('#addModal').modal('hide');
                refreshOtherCostTable();
            },
            error: function (xhr) {
                console.error(xhr.responseJSON);
                toastr.error('Có lỗi xảy ra!');
            }
        });
    });

    let tableCost; // Khai báo biến toàn cục

    // Hiển thị modal sửa chi phí phát sinh
    $('#tbl_other_cost_table').on('click', '.edit-cost-btn', function () {
        const row = $(this).closest('tr');
        const rowData = $('#tbl_other_cost_table').DataTable().row(row).data();
        $('#editCostId').val(rowData.id);
        $('#editKhachHang').val(rowData.khach_hang);
        $('#editDichVu').val(rowData.dich_vu);
        $('#editDoanhThu').val(rowData.doanh_thu);
        $('#editHoaHong').val(rowData.hoa_hong);

        $('#editModal').modal('show');
    });

    // Xử lý form sửa chi phí
    $('#editForm').on('submit', function (e) {
        e.preventDefault();

        const costId = $('#editCostId').val();
        const data = {
            id: costId,
            user_id: $('#user_id').val(),

            khach_hang: $('#editKhachHang').val(),
            dich_vu: $('#editDichVu').val(),
            doanh_thu: $('#editDoanhThu').val(),
            hoa_hong: $('#editHoaHong').val(),
            _token: $('input[name="_token"]').val(),
        };

        $.ajax({
            url: route('other-costs.update', costId),
            method: 'PUT',
            data: data,
            success: function (response) {
                toastr.success(response.message);
                $('#editModal').modal('hide');
                refreshOtherCostTable();
            },
            error: function (xhr) {
                toastr.error('Có lỗi xảy ra khi cập nhật chi phí:' + xhr.responseText);
            }
        });
    });

    // Sự kiện khi bấm nút xóa
    $('#tbl_other_cost_table').on('click', '.delete-cost-btn', function () {
        const rowData = tableCost.row($(this).closest('tr')).data(); // Lấy dữ liệu dòng
        $('#deleteRowId').val(rowData.id); // Đặt ID dòng vào input ẩn
        $('#deleteModal').modal('show'); // Hiển thị modal xác nhận
    });

    // Xử lý khi xác nhận xóa
    $('#confirmDelete').on('click', function () {
        const id = $('#deleteRowId').val(); // Lấy ID từ input ẩn

        $.ajax({
            url: route('other-costs.destroy', id),
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') // Lấy CSRF token từ meta tag
            },
            success: function (response) {
                $('#deleteModal').modal('hide'); // Ẩn modal
                tableCost.ajax.reload(null, false); // Làm mới bảng
                calculateAndDisplayKpi().then(() => {
                    total_salary();
                });
                toastr.success(response.message || 'Xóa thành công!');
            },
            error: function (xhr) {
                toastr.error('Lỗi khi xóa: ' + xhr.responseText);
            }
        });
    });
    function refreshOtherCostTable() {
        const userId = $('#user_id').val();
        const month = $('#month').val();

        if (!userId || !month) {
            $('#tbl_other_cost_table tbody').empty();
            return;
        }

        if ($.fn.DataTable.isDataTable('#tbl_other_cost_table')) {
            $('#tbl_other_cost_table').DataTable().destroy(); // Hủy DataTable hiện tại
            $('#other_cost_table_body').empty(); // Xóa dữ liệu cũ trong tbody
        }

        tableCost = $('#tbl_other_cost_table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            info: false,
            paging: false,
            lengthChange: false,
            ajax: {
                url: route('other-costs.getOtherCosts'),
                type: 'GET',
                data: {
                    user_id: userId,
                    month: month
                },
                dataSrc: 'data'
            },
            columns: [
                { data: 'khach_hang', name: 'khach_hang' },
                { data: 'dich_vu', name: 'dich_vu' },
                {
                    data: 'doanh_thu',
                    name: 'doanh_thu',
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',', '.', 0)
                },
                {
                    data: 'hoa_hong',
                    name: 'hoa_hong',
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',', '.', 0)
                },
                { data: 'date', name: 'date', className: 'text-center' },
                {
                    data: null,
                    className: 'text-center',
                    defaultContent: `
                        <button class="btn btn-sm btn-primary edit-cost-btn" type="button">Sửa</button>
                        <button class="btn btn-sm btn-danger delete-cost-btn" type="button">Xóa</button>
                    `
                }
            ],
            language: {
                processing: "Đang xử lý...",
                zeroRecords: "Không tìm thấy dữ liệu",
                paginate: {
                    first: "Đầu",
                    last: "Cuối",
                    next: "Tiếp",
                    previous: "Trước"
                }
            },
            drawCallback: function () {
                updateTotalHoaHong();
            }
        });
    }
    // Hàm cập nhật tổng hoa hồng
    function updateTotalHoaHong() {
        let totalHoaHong = 0;
        tableCost.rows().every(function () {
            let data = this.data();
            totalHoaHong += parseFloat(data.hoa_hong) || 0;
        });
        $('#rs-othercost').text(formatNumber(totalHoaHong));
        $('#other_cost').val(totalHoaHong);
        $('#rs-tong-hop').text(formatNumber(cost_tong_hop()));

        total_salary();
    }
    // Sự kiện khi bấm nút sửa
    $('#tbl_other_cost_table').on('click', '.edit-cost-btn', function () {
        const rowData = tableCost.row($(this).closest('tr')).data(); // Lấy dữ liệu từ dòng
        $('#editCostId').val(rowData.id);
        $('#editKhachHang').val(rowData.khach_hang);
        $('#editDichVu').val(rowData.dich_vu);
        $('#editDoanhThu').val(rowData.doanh_thu);
        $('#editHoaHong').val(rowData.hoa_hong);
        $('#editModal').modal('show'); // Hiển thị modal sửa
    });
    // Xử lý khi xác nhận sửa
    $('#editForm').on('submit', function (e) {
        e.preventDefault();

        const costId = $('#editCostId').val(); // Lấy ID từ input ẩn
        const data = {
            id: costId,
            user_id: $('#user_id').val(),
            khach_hang: $('#editKhachHang').val(),
            dich_vu: $('#editDichVu').val(),
            doanh_thu: $('#editDoanhThu').val(),
            hoa_hong: $('#editHoaHong').val(),
            _token: $('meta[name="csrf-token"]').attr('content') // Lấy CSRF token từ meta tag
        };

        $.ajax({
            url: route('other-costs.update', costId),
            method: 'PUT',
            data: data,
            success: function (response) {
                $('#editModal').modal('hide'); // Ẩn modal
                tableCost.ajax.reload(null, false); // Làm mới bảng
                toastr.success(response.message || 'Cập nhật thành công!');
                updateTotalHoaHong();
            },
            error: function (xhr) {
                toastr.error('Lỗi khi cập nhật chi phí: ' + xhr.responseText);
            }
        });
    });
}); 