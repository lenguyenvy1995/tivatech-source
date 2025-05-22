@extends('adminlte::page')

@section('title', 'Tất Cả Yêu Cầu Báo Giá')
@section('content_header')
    <h1>Yêu Cầu Báo Giá</h1>
@stop

@section('css')
    <!-- DataTables CSS từ CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @routes
    <style>
        .tooltip-inner {
            background-color: #343a40 !important;
            /* màu nền */
            color: #fff !important;
            /* màu chữ */
            padding: 6px 12px !important;
            /* padding */
            font-size: 13px;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .tooltip.bs-tooltip-top .arrow::before {
            border-top-color: #343a40 !important;
        }

        .tooltip.bs-tooltip-bottom .arrow::before {
            border-bottom-color: #343a40 !important;
        }

        .tooltip.bs-tooltip-left .arrow::before {
            border-left-color: #343a40 !important;
        }

        .tooltip.bs-tooltip-right .arrow::before {
            border-right-color: #343a40 !important;
        }
    </style>
@stop
@section('content')
    <!-- Form Lọc và Tìm Kiếm -->
    <form id="filterForm" class="mb-4">
        <div class="row">
            <!-- Lọc theo Trạng thái -->
            <div class="col-md-3">
                <label for="filter_status">Trạng thái</label>
                <select id="filter_status" class="form-control">
                    <option value="">Tất cả Trạng thái</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="quoted">Đã báo giá</option>
                    <option value="rejected">Đã từ chối</option>
                </select>
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
            <div class="col-md-3 d-flex align-items-center">
                <button type="button" id="filterBtn" class="btn btn-primary">Lọc</button>
                <button type="button" id="resetBtn" class="btn btn-secondary ml-2">Làm mới</button>
                <a href="{{ route('quote-requests.create') }}" class="btn btn-success ml-2"> <i class="fa fa-plus"
                        aria-hidden="true"></i> Tạo báo giá</a>
            </div>
        </div>
    </form>
    <div class="table-responsive">
        <table id="quoteRequestsTable" class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Domain</th>
                    <th>Từ khóa</th>
                    <th class="text-center" width="120px">Ngày Tạo</th>
                    <th class="text-center" width="75px">Trạng thái</th>
                    <th width="140px" class="text-center">Hành động <i class="fa fa-pencil" aria-hidden="true"></i></th>

                </tr>
            </thead>
        </table>

    </div>
@stop
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@stop
@section('js')
    <script>
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
        // Thêm "Xem thêm" nếu nội dung quá dài
        $('.keywords-truncate').each(function() {
            var content = $(this).text();
            var maxLength = 100; // Giới hạn ký tự
            if (content.length > maxLength) {
                var truncated = content.substring(0, maxLength) + '... ';
                $(this).html(truncated);
            }
        });
        $(document).ready(function() {
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
        });
        // Hiển thị thông báo thành công (nếu có)
        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @endif
    </script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $.ajax({
            type: "get",
            url: "{{ route('quote-requests.index') }}",
            success: function(response) {
                console.log(response)
            }
        });
        $(function() {
            $('[data-toggle="tooltip"]').tooltip({
                delay: {
                    "show": 50,
                    "hide": 50
                }, // mặc định là 200
                placement: 'top', // top, bottom, left, right
                trigger: 'hover focus'
            });
        });
        $(document).ready(function() {
            table = $('#quoteRequestsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('quote-requests.index') }}", // This will call your route that returns JSON data
                    data: function(d) {
                        d.status = $('#filter_status').val(); // Lấy giá trị trạng thái từ form
                        d.quoteDomain = $('#filter_quoteDomain').val(); // Lấy giá trị user từ form
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },

                    {
                        data: 'quote_domain',
                        name: 'quote_domain'
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
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            return moment(data).format('HH:mm DD-MM-YYYY');
                        },
                        className: 'text-center',
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            if (data == 'Pending') {
                                return '<span class="badge badge-warning">Chờ xử lý</span>';
                            } else if (data == 'Quoted') {
                                return '<span class="badge badge-success">Đã báo giá</span>';
                            } else if (data == 'Rejected') {
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
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                language: {
                    "lengthMenu": "Hiển thị _MENU_ mục",
                    "info": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                    "infoEmpty": "Không có mục nào",
                    "search": "Tìm kiếm:",
                    "zeroRecords": "Không tìm thấy kết quả",
                    "paginate": {
                        "first": "Đầu tiên",
                        "last": "Cuối cùng",
                        "next": "Tiếp theo",
                        "previous": "Trước"
                    }
                }
            });
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
