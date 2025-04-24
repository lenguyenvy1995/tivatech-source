@extends('adminlte::page')

@section('title', 'Tất Cả Yêu Cầu Báo Giá')

@section('content_header')
    <h1>Tất Cả Yêu Cầu Báo Giá</h1>
@stop
@section('css')
    <!-- DataTables CSS từ CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @routes
    <style>
        .keywords-truncate {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            /* Giới hạn 3 dòng */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@stop
@section('content')
    <form id="filterForm" class="mb-4">
        <div class="row">
            <!-- Lọc theo Trạng thái -->
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter_status">Trạng thái</label>
                    <select id="filter_status" class="form-control ">
                        <option value="">Tất cả Trạng thái</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="quoted">Đã báo giá</option>
                        <option value="rejected">Đã từ chối</option>
                    </select>
                </div>
            </div>

            <!-- Lọc theo Quote Domain -->
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter_quoteDomain">Quote Domain</label>
                    <select id="filter_quoteDomain" class="form-control select2">
                        <option value="">Tất cả Domain</option>
                        @foreach ($quoteDomains as $domain)
                            <option value="{{ $domain->id }}">{{ $domain->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Lọc theo User -->
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter_user">Người yêu cầu</label>
                    <select id="filter_user" class="form-control select2">
                        <option value="">Tất cả Người dùng</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Nút lọc -->
            <div class="col-md-3 d-flex align-items-center">
                <button type="button" id="filterBtn" class="btn btn-primary">Lọc</button>
                <button type="button" id="resetBtn" class="btn btn-secondary ml-2">Làm mới</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">

        <table id="quoteRequestsTable" class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Người yêu cầu</th>
                    <th>Domain</th>
                    <th>Từ khóa</th>
                    <th  class="text-center" width="120px">Ngày Tạo</th>
                    <th class="text-center" width="75px">Trạng thái</th>
                    <th width="140px" class="text-center">Hành động <i class="fa fa-pencil" aria-hidden="true"></i></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
@stop
@section('js')
    <script>
  
        table =$('#quoteRequestsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: route('quote-requests.all'),
                data: function(d) {
                    d.status = $('#filter_status').val(); // Lấy giá trị trạng thái từ form
                    d.user_id = $('#filter_user').val(); // Lấy giá trị user từ form
                    d.quoteDomain = $('#filter_quoteDomain').val(); // Lấy giá trị user từ form
                },
            },
            columns: [{ data: 'id',
            name: 'id',
            },
                {
                    data: 'user.fullname',
                    name: 'user.fullname',
                    defaultContent: 'N/A',
                },
                {
                    data: 'quote_domain',
                    name: 'quote_domain',
                    defaultContent: 'N/A',
                    searchable: false

                },
                {
                    data: 'keywords',
                    name: 'keywords',
                    render: function(data, type, row) {
                        if (type === 'display' && data.length > 100) {
                            return '<span class="keywords-truncate">' + data.substring(0, 100) +
                                '...</span>';
                        }
                        return data;
                    },
                    searchable: false

                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data, type, row) {
                        return moment(data).format(' HH:mm DD-MM-YYYY ');
                    },
                    className: 'text-center',

                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        if (data == 'pending') {
                            return '<span class="badge badge-warning">Chờ xử lý</span>';
                        } else if (data == 'quoted') {
                            return '<span class="badge badge-success">Đã báo giá</span>';
                        } else if (data == 'rejected') {
                            return '<span class="badge badge-secondary">Đã từ chối</span>';
                        } else {
                            return '<span class="badge badge-secondary"> N/A </span>';
                        }
                    },
                    className: 'text-center',

                },

                {
                    data: 'action',
                    name: 'action',
                      className: 'text-center',
                    orderable: false,
                    searchable: false
                },
            ],
            language: {
                "decimal": "",
                "emptyTable": "Không có dữ liệu",
                "info": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                "infoEmpty": "Hiển thị 0 đến 0 của 0 mục",
                "infoFiltered": "(được lọc từ _MAX_ mục)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Hiển thị _MENU_ mục",
                "loadingRecords": "Đang tải...",
                "processing": "Đang xử lý...",
                "search": "Tìm kiếm:",
                "zeroRecords": "Không tìm thấy kết quả nào",
                "paginate": {
                    "first": "Đầu tiên",
                    "last": "Cuối cùng",
                    "next": "Tiếp theo",
                    "previous": "Trước"
                },
                "aria": {
                    "sortAscending": ": kích hoạt để sắp xếp cột theo thứ tự tăng dần",
                    "sortDescending": ": kích hoạt để sắp xếp cột theo thứ tự giảm dần"
                }
            }
        });

        // Khi nhấn nút "Lọc"
        $('#filterBtn').on('click', function() {
            table.draw(); // Gọi lại DataTables với các giá trị mới
        });

        // Khi nhấn nút "Làm mới"
        $('#resetBtn').on('click', function() {
            $('#filterForm select').val('').trigger('change'); // Đặt lại form lọc
            table.draw(); // Gọi lại DataTables mà không có giá trị lọc
        });
        $(function() {
            // Khởi tạo Popover cho các liên kết có data-toggle="popover"
            $('[data-toggle="popover"]').popover({
                trigger: 'hover',
                placement: 'top',
                html: true,
                sanitize: false, // Bảo mật: nếu bạn không cần hiển thị HTML, hãy giữ mặc định là true
            });

            // Đóng popover khi nhấp vào bất kỳ đâu trên trang
            $('body').on('click', function(e) {
                $('[data-toggle="popover"]').each(function() {
                    // the 'is' for buttons that trigger popups
                    // the 'has' for icons within a button that triggers a popup
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover')
                        .has(e.target).length === 0) {
                        $(this).popover('hide');
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Khởi tạo Select2 cho các dropdown
            $('.select2').select2({
                placeholder: '-- Chọn một tùy chọn --',
                allowClear: true
            });

            // Hiển thị modal nếu có lỗi khi thêm Quote Domain mới
            @if ($errors->has('name'))
                $('#addQuoteDomainModal').modal('show');
            @endif

            // Cấu hình Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000",
            };

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
        });
    </script>
@stop
