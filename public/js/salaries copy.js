$(document).ready(function () {
    // Khởi tạo Select2
    $('.select2').select2({
        placeholder: '-- Chọn nhân viên --',
        allowClear: true,
    });
    // Hàm format số sử dụng regex
    function formatNumber(value) {
        if (!value) return '0'; // Trả về '0' nếu value là null, undefined, hoặc rỗng
        return String(value)
            .replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    // Khởi tạo Flatpickr cho tháng
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

    // Bật/tắt bảng KPI
    $('input[name="salary_type"]').on('change', toggleKpiTable);
    function toggleKpiTable() {
        if ($('#kpi_salary').is(':checked')) {
            $('#kpi_table, #type_sa').slideDown();
        } else {
            $('#kpi_table, #type_sa').slideUp();
        }
    }

    // Xử lý khi chọn nhân viên
    $('#user_id').on('change', fetchSalaryDetails);
    //xử lý khi thay đổi doanh số
    $('#doanhSo').on('keyup', function () {
        calculateAndDisplayKpi().then(() => {
            total_salary(); // Gọi tính tổng lương sau khi tính KPI xong
        }).catch(error => {
            console.error('Lỗi khi tính KPI:', error);
        });
    });
    // Xử lý khi chọn tháng
    $('#month').on('change', fetchSalaryDetails);

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
                    toggleKpiTable(); // Ẩn bảng KPI
                }

                // Cập nhật các trường lương
                populateSalaryFields(response);
            },
            error: function () {
                toastr.error('Lỗi khi tải dữ liệu.');
            },
        });
    }

    function populateSalaryFields(response) {
        $('#base_salary').val(response.base_salary);
        $('#bhxh').val(-1 * response.bhxh);
        $('#work_days').val(response.work_days);
        $('#actual_work_days').val(response.actual_work_days.workDays);
        $('#phone_allowance').val(response.phone_allowance);
        $('#attendance_bonus').val(response.attendance_bonus || 0);
        $('#bonus').val(response.attendance_bonus || 0);
        $('#doanhSo').val(response.doanhSo)

        toggleKpiTable();
        refreshOtherCostTable(response.user_id, $('#month').val());
        calculateSalary()
    }

    function clearSalaryFields() {
        $('#base_salary,#bonus, #bhxh, #work_days, #actual_work_days, #phone_allowance, #attendance_bonus').val('');
        $('#kpi_table_body, #other_cost_table_body').empty();
        toggleKpiTable();
    }
    // Hàm tính lương thực tế
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
    
            let luongThuclam = 0;
    
            // Nếu số ngày thực làm là 0 hoặc null thì lương thực làm bằng 0
            if (data.thucLam !== 0 && data.thucLam !== null) {
                luongThuclam = Math.round((data.luongCoBan / data.ngayCongLam) * data.thucLam);
            }
    
            const luongThucTe = luongThuclam + data.phuCap + data.bhxh + data.chuyenCan + data.thuong;
            $('#actual_salary').val(luongThucTe.toFixed(0));
            updateSalarySlip(data, luongThucTe, luongThuclam);
        }).catch(error => {
            console.error('Lỗi khi tính KPI:', error);
        });
    }

    // Lắng nghe sự kiện thay đổi trên các input
    $('#basic_salary, #work_days, #actual_work_days, #allowance, #bhxh, #attendance_bonus, #bonus').on('input', function () {
        calculateSalary(); // Gọi hàm tính lương khi có thay đổi
    });
    // Hàm cập nhật PHIẾU LƯƠNG
    function updateSalarySlip(data, luongThucTe, luongThuclam) {
        $('#rs-base-salary').text(formatNumber(data.luongCoBan)); // Lương cơ bản
        $('#rs-work-days').text(data.ngayCongLam); // Số ngày công chuẩn
        $('#rs_actual_work_days').text(data.thucLam); // Ngày thực làm
        $('#rs-real-salary').text(formatNumber(luongThuclam.toFixed(0))); // Lương công làm
        $('#rs-phone_allowance').text(formatNumber(data.phuCap)); // Phụ cấp điện thoại
        $('#rs-bhxh').text(formatNumber(data.bhxh)); // BHXH
        $('#rs-attendance_bonus').text(formatNumber(data.chuyenCan)); // Chuyên cần
        $('#rs_bonus').text(formatNumber(data.thuong)); // Thưởng
        $('#rs_rice').text(formatNumber(data.thucLam * 20000)); // Thưởng
        $('#rs-total-salary').text(formatNumber(luongThucTe)); // Tổng lương thực lãnh
    }
    // Gọi hàm tính lương lần đầu khi trang được load
    calculateSalary();
    // khởi tạo DataTable khi có thay đổi
    function loadKpis(userId) {
        // Kiểm tra nếu DataTable đã tồn tại
        if ($.fn.DataTable.isDataTable('#tbl_kpi_table')) {
            $('#tbl_kpi_table').DataTable().destroy(); // Hủy DataTable cũ
            $('#tbl_kpi_table').find('tbody').empty(); // Chỉ xóa nội dung tbody, giữ nguyên thead
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
            initComplete: function () {
                // // Hiển thị bảng KPI
                // calculateAndDisplayKpi().then(() => {
                //     total_salary();
                // });
                toggleKpiTable()
            },
        })
    }
    // Hàm khởi tạo hoặc làm mới DataTable
    function refreshOtherCostTable(userId, month) {

        // Kiểm tra nếu DataTable đã tồn tại
        if ($.fn.DataTable.isDataTable('#tbl_other_cost_table')) {
            $('#tbl_other_cost_table').DataTable().destroy(); // Phá hủy bảng cũ
        }

        // Khởi tạo lại DataTable
        table = $('#tbl_other_cost_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10, // Số lượng dòng hiển thị mặc định
            // Tắt tính năng tìm kiếm
            searching: false,
            // Tắt tính năng hiển thị thông tin số bản ghi
            info: false,

            // Tắt tính năng phân trang
            paging: false,
            // Tắt tính năng chọn số dòng hiển thị
            lengthChange: false,
            lengthMenu: [
                [10, 50, 100, 200, -1],
                [10, 50, 100, 200, 'All']
            ],
            ajax: {
                url: route('other-costs.getOtherCosts'), // Endpoint lấy dữ liệu từ server
                type: 'GET',
                data: {
                    user_id: userId,
                    month: month
                },
                dataSrc: 'data' // Đảm bảo ánh xạ đến đúng key trong JSON trả về
            },
            columns: [{
                data: 'khach_hang',
                name: 'khach_hang'
            },
            {
                data: 'dich_vu',
                name: 'dich_vu'
            },
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
            {
                data: 'date',
                name: 'date',
                className: 'text-center',

            },
            {
                data: null,
                className: 'text-center',
                defaultContent: '<button class="btn btn-sm btn-primary edit-btn" type="button">Sửa</button><button class="btn btn-sm btn-danger m-1 delete-btn" type="button">Xóa</button>'
            }
            ],
            language: {
                processing: "Đang xử lý...",
                search: "Tìm kiếm:",
                lengthMenu: "Hiển thị _MENU_ dòng",
                info: "Hiển thị _START_ đến _END_ trong tổng _TOTAL_ dòng",
                infoEmpty: "Không có dữ liệu",
                paginate: {
                    first: "Đầu",
                    last: "Cuối",
                    next: "Tiếp",
                    previous: "Trước"
                },
                zeroRecords: "Không tìm thấy dữ liệu"
            },
            select: true
        });
    }
    // Hàm tính và hiển thị KPI
    function calculateAndDisplayKpi() {
        return new Promise((resolve, reject) => {
            if ($('#kpi_salary').is(':checked')) {
                const doanhSo = parseFloat($('#doanhSo').val());
                if (isNaN(doanhSo) || doanhSo <= 0) {
                    toastr.warning('Vui lòng nhập doanh số hợp lệ!');
                    reject('Doanh số không hợp lệ');
                } else {
                    const kpiTable = getKpiTableFromHtml();
                    const kpi = calculateKpi(doanhSo, kpiTable);
                    if (isNaN(kpi) || typeof kpi !== 'number') {
                        console.error('KPI không hợp lệ:', kpi);
                        toastr.error('Có lỗi trong tính toán KPI.');
                        reject('KPI không hợp lệ');
                        return;
                    }
                    $('#kpi').val(kpi);
                    $('#rs-doanhSo').text(doanhSo.toLocaleString('vi-VN'));
                    $('#rs-kpi').text(kpi.toLocaleString('vi-VN'));

                    // Gọi tổng lương sau khi tính KPI xong
                    total_salary();
                    resolve();
                }
            } else {
                $('#kpi').val(0);
                $('#rs-doanhSo').text(0);
                $('#rs-kpi').text(0);

                // Gọi tổng lương nếu KPI không được áp dụng
                total_salary();
                resolve();
            }
        });
    }
    // Hàm tính tổng lương thực lãnh
    function total_salary() {
        let real_salary = parseFloat($('#actual_salary').val()) || 0; // Lương thực tế
        let kpi = parseFloat($('#kpi').val()) || 0; // Thưởng KPI
        // let total_cost = cost_tong_hop(); // Tổng chi phí khác
        let total_cost = 0; // Tổng chi phí khác

        let total_salary = real_salary + kpi + total_cost;
        console.log('KPI đã:'+kpi);

        // Hiển thị tổng lương đã tính lên giao diện
        $('#t_total_salary').val(total_salary);
        $('#rs-total-salary').text(new Intl.NumberFormat('vi-VN').format(total_salary));
    }
    function getKpiTableFromHtml() {
        const kpiTable = [];

        // Duyệt qua từng hàng trong bảng (trừ tiêu đề)
        $('#tbl_kpi_table tbody tr').each(function () {
            const doanhThu = parseInt($(this).find('td').eq(0).text().replace(/[.,]/g, '')); // Cột "Doanh Thu"
            const hoaHong = parseFloat($(this).find('td').eq(1).text()); // Cột "Hoa Hồng"

            if (!isNaN(doanhThu) && !isNaN(hoaHong)) {
                kpiTable.push({
                    doanhThu,
                    hoaHong,
                });
            }
        });

        // Thêm một mức Infinity để xử lý doanh thu lớn nhất
        kpiTable.push({
            doanhThu: Infinity,
            hoaHong: kpiTable[kpiTable.length - 1]?.hoaHong || 0, // Nếu không có mốc trước, hoa hồng là 0
        });

        return kpiTable;
    }
    function calculateKpi(doanhSo, kpiTable) {
        let hoaHong = 0;
        let remainingDoanhSo = doanhSo;

        if (!Array.isArray(kpiTable) || kpiTable.length === 0) {
            console.error('Bảng KPI không hợp lệ:', kpiTable);
            return 0;
        }

        for (let i = 0; i < kpiTable.length; i++) {
            const currentMoc = kpiTable[i];

            const nextMoc = kpiTable[i + 1] || { doanhThu: Infinity }; // Mốc tiếp theo hoặc vô cực

            // Kiểm tra giá trị hợp lệ
            if (isNaN(currentMoc.doanhThu) || isNaN(currentMoc.hoaHong)) {
                console.error('Mốc KPI không hợp lệ:', currentMoc);
                continue; // Bỏ qua mốc không hợp lệ
            }

            // Tính phần doanh số nằm trong khoảng hiện tại
            const range = Math.min(remainingDoanhSo, nextMoc.doanhThu - currentMoc.doanhThu);

            if (range > 0) {
                hoaHong += range * (currentMoc.hoaHong / 100);
                remainingDoanhSo -= range;
            }

            // Dừng nếu không còn doanh số để tính
            if (remainingDoanhSo <= 0) break;
        }
        return parseFloat(hoaHong.toFixed(0)); // Làm tròn hoa hồng về số nguyên
    }

});
$(document).ready(function () {
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
});