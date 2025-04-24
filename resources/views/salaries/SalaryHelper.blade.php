<script>
    // Đối tượng lưu trữ các hàm dùng chung
    const SalaryHelper = {
        toggleKpiTable: function() {
            if ($('#kpi_salary').is(':checked')) {
                $('#kpi_table, #type_sa').slideDown();
            } else {
                $('#kpi_table, #type_sa').slideUp();
            }
        },
        absentDays: function() {
            const workDays = parseInt($('#work_days').val()) || 0;
            const actualWorkDays = parseInt($('#actual_work_days').val()) || 0;
            $('#absent_days').val(workDays - actualWorkDays);
        },
        realSalary: function() {
            const baseSalary = parseFloat($('#base_salary').val()) || 0;
            const workDays = parseInt($('#work_days').val()) || 1; // Tránh chia cho 0
            const actualWorkDays = parseInt($('#actual_work_days').val()) || 0;
            const realSalary = (baseSalary / workDays) * actualWorkDays;
            $('#real_salary').val(realSalary.toFixed(2));
            $('#rs-real-salary').text(realSalary.toLocaleString('vi-VN'));
        },
        calculateTotalCommission: function() {
            let totalCommission = 0;
            $('#tbl_other_cost_table tbody tr').each(function() {
                totalCommission += parseFloat($(this).find('td:eq(3)').text().replace(/,/g, '')) || 0;
            });
            $('#other_expenses').val(totalCommission);
            $('#rs-orthercost').text(totalCommission.toLocaleString('vi-VN'));
        },
        loadKpis: function(userId) {
            if ($.fn.DataTable.isDataTable('#tbl_kpi_table')) {
                $('#tbl_kpi_table').DataTable().destroy();
            }
            $('#tbl_kpi_table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: false,
                lengthChange: false,
                ajax: {
                    url: route('kpis.data'),
                    data: { user_id: userId },
                },
                columns: [
                    { data: 'doanh_thu', name: 'doanh_thu' },
                    { data: 'hoa_hong', name: 'hoa_hong' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' },
            });
        },
        refreshOtherCostTable: function(userId, month) {
            if ($.fn.DataTable.isDataTable('#tbl_other_cost_table')) {
                $('#tbl_other_cost_table').DataTable().destroy();
            }
            $('#tbl_other_cost_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: route('other-costs.getOtherCosts'),
                    data: { user_id: userId, month },
                },
                columns: [
                    { data: 'khach_hang', name: 'khach_hang' },
                    { data: 'dich_vu', name: 'dich_vu' },
                    { data: 'doanh_thu', name: 'doanh_thu', render: $.fn.dataTable.render.number(',', '.', 0) },
                    { data: 'hoa_hong', name: 'hoa_hong', render: $.fn.dataTable.render.number(',', '.', 0) },
                    { data: 'date', name: 'date' },
                    {
                        data: null,
                        defaultContent: '<button class="btn btn-sm btn-primary edit-btn">Sửa</button> <button class="btn btn-sm btn-danger delete-btn">Xóa</button>',
                    },
                ],
                drawCallback: SalaryHelper.calculateTotalCommission,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' },
            });
        },
    };

    // Bind các sự kiện
    function bindEvents() {
        $('input[name="salary_type"]').on('change', SalaryHelper.toggleKpiTable);

        $('#work_days, #actual_work_days').on('keyup', function() {
            SalaryHelper.absentDays();
            SalaryHelper.realSalary();
        });

        $('#user_id').on('change', function() {
            const userId = $(this).val();
            const month = $('#month').val();
            if (!userId) return;

            $.ajax({
                url: route('salaries.expected'),
                data: { user_id: userId, date: month },
                success: function(response) {
                    $('#base_salary').val(response.base_salary);
                    $('#bhxh').val(-response.bhxh);
                    $('#rs-bhxh').text((-response.bhxh).toLocaleString('vi-VN'));
                    $('#phone_allowance').val(response.phone_allowance);
                    $('#work_days').val(response.work_days);
                    $('#actual_work_days').val(response.actual_work_days.workDays);

                    SalaryHelper.absentDays();
                    SalaryHelper.realSalary();

                    if (response.salary_type === 1) {
                        SalaryHelper.loadKpis(userId);
                    }

                    SalaryHelper.refreshOtherCostTable(userId, month);
                },
            });
        });

        $('#tbl_other_cost_table').on('draw.dt', SalaryHelper.calculateTotalCommission);
    }

    // Khởi tạo các sự kiện và plugin
    $(document).ready(function() {
        $('.select2').select2({ placeholder: '-- Chọn một nhân viên --' });
        $('#month').flatpickr({
            dateFormat: 'Y-m',
            locale: 'vn',
            plugins: [new monthSelectPlugin({ dateFormat: 'Y-m', altFormat: 'F Y' })],
        });

        bindEvents();
    });
</script>
