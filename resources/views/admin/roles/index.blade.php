@extends('adminlte::page')

@section('title', 'Roles Management')

@section('content_header')
    <h1>Quản lý nhóm và phân quyền</h1>
@stop
@section('css')
    @routes
@stop
@section('content')
    <div class="card">
        <div class="card-header">
            <button class="btn btn-primary" id="createRole">+ Tạo mới</button>
            <button class="btn btn-secondary" id="reloadTable">Tải lại</button>
        </div>
        <div class="card-body">
            <table id="rolesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Mô tả</th>
                        <th>Ngày tạo</th>
                        <th>Được tạo bởi</th>
                        <th>Tác vụ</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal Tạo/Sửa Role -->
    <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="roleForm" action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                @method('POST')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="roleModalLabel">Tạo Role</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="roleName">Tên</label>
                            <input type="text" name="name" id="roleName" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Khởi tạo DataTable
        const table = $('#rolesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('admin.roles.data') }}',
            pageLength: 100,
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'description',
                    name: 'description',
                    defaultContent: ''
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'users',
                    name: 'users',
                    defaultContent: ''
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Mở modal tạo role
        $('#createRole').click(function() {
            $('#roleModalLabel').text('Tạo Role');
            $('#roleForm').trigger('reset');
            $('#roleForm').attr('action', '{{ route('admin.roles.store') }}');
            $('#roleForm').find('input[name="_method"]').val('POST'); // Đặt method thành PUT

            $('#roleModal').modal('show');
        });
        // Gửi form tạo/sửa role
        $('#roleForm').submit(function(e) {
            e.preventDefault();
            const actionUrl = $(this).attr('action');
            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#roleModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.success);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.message);
                }
            });
        });

        // Xóa role
        $(document).on('click', '.btn-delete', function() {
            if (confirm('Bạn có chắc chắn muốn xóa?')) {
                const url = $(this).data('url');
                $.ajax({
                    url: url,
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    method: 'DELETE',
                    success: function(response) {
                        table.ajax.reload();
                        toastr.success(response.success);

                    }
                });
            }
        });
    </script>
@stop
