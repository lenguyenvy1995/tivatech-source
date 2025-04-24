@extends('adminlte::page')

@section('title', 'Danh sách khách hàng')

@section('content_header')
    <h1>Quản lý khách hàng</h1>
@stop
@section('css')
    <style>
        .mb-3 label {
            font-weight: bold;
        }
    </style>
@stop

@section('content')
    <button class="btn btn-primary mb-3" id="addNewBtn">Thêm mới</button>

    <div class="row mb-3">
        <!-- Trạng thái báo giá -->
        <div class="col-md-3">
            <label for="filter-status">Trạng thái báo giá</label>
            <select id="filter-status" class="form-control">
                <option value="">Tất cả</option>
                <option value="0">Chưa báo</option>
                <option value="1">Đã báo</option>
                <option value="2">Đã chốt</option>
            </select>
        </div>

        <!-- Nhân viên (chỉ admin) -->
        @if (Auth::user()->hasRole('admin'))
            <div class="col-md-3">
                <label for="filter-user">Nhân viên</label>
                <select id="filter-user" class="form-control">
                    <option value="">Tất cả</option>
                    @foreach (App\Models\User::all() as $user)
                        <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Ngày cập nhật -->
        <div class="col-md-3">
            <label for="filter-updated-at">Ngày cập nhật</label>
            <input type="date" id="filter-updated-at" class="form-control">
        </div>

        <!-- Từ khóa -->
        <div class="col-md-3">
            <label for="filter-keywords">Từ khóa</label>
            <input type="text" id="filter-keywords" class="form-control" placeholder="Nhập từ khóa">
        </div>
    </div>

    <!-- Table Data -->
    <table id="customersTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Khách hàng</th>
                <th>Số điện thoại</th>
                <th>Từ khóa</th>
                <th>Feedback</th>
                <th>Trạng thái báo giá</th>
                <th>Ngày tạo</th>
                <th>Ngày cập nhật</th>
                <th>Hành động</th>
            </tr>
        </thead>
    </table>

    <!-- Modal Form -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Thêm mới/Chỉnh sửa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="customerForm">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="customerId" name="id">
                        <div class="form-group">
                            <label for="domain">Tên miền</label>
                            <input type="text" class="form-control" id="domain" name="domain" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Số điện thoại</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="keywords">Từ khóa</label>
                            <textarea class="form-control" id="keywords" name="keywords" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="feedback">Phản hồi</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái báo giá</label>
                            <select class="form-control" id="quote_status" name="quote_status" required>
                                <option value="0">Chưa báo</option>
                                <option value="1">Đã báo</option>
                                <option value="2">Đã chốt</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            let table = $('#customersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('dataCustomers.index') }}',
                    data: function(d) {
                        d.quote_status = $('#filter-status').val();
                        d.user_id = $('#filter-user').val();
                        d.updated_at = $('#filter-updated-at').val();
                        d.keywords = $('#filter-keywords').val();
                    }
                },
                pageLength: 100,
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'domain',
                        name: 'domain'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'keywords',
                        name: 'keywords'
                    },
                    {
                        data: 'feedback',
                        name: 'feedback'
                    },
                    {
                        data: 'quote_status',
                        name: 'quote_status',
                        orderable: false,

                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            // Áp dụng bộ lọc
            $('#filter-status, #filter-user, #filter-updated-at, #filter-keywords').on('change keyup', function() {
                table.ajax.reload();
            });
            // Thêm mới
            $('#addNewBtn').click(function() {
                $('#customerForm')[0].reset();
                $('#customerId').val('');
                $('#customerModal').modal('show');
            });

            // Chỉnh sửa
            $(document).on('click', '.edit-btn', function() {
                let id = $(this).data('id');
                $.get('{{ route('dataCustomers.index') }}?id=' + id, function(data) {
                    $('#customerId').val(data.id);
                    $('#domain').val(data.domain);
                    $('#phone').val(data.phone);
                    $('#keywords').val(data.keywords);
                    $('#feedback').val(data.feedback);
                    $('#quote_status').val(data.quote_status);
                    $('#customerModal').modal('show');
                });
            });

            // Lưu dữ liệu
            $('#customerForm').submit(function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                $.post('{{ route('dataCustomers.store') }}', formData, function(response) {
                    $('#customerModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.success);
                });
            });

            // Xóa
            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                if (confirm('Bạn có chắc muốn xóa?')) {
                    $.ajax({
                        url: '{{ route('dataCustomers.destroy', '') }}/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            table.ajax.reload();
                            toastr.success(response.success);
                        }
                    });
                }
            });
        });

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
