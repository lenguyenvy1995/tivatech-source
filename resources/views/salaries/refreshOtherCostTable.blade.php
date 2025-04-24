<script>
    var table; // Định nghĩa biến table toàn cục

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
</script>
