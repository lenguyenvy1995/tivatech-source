@extends('adminlte::page')

@section('title', 'Danh sách Website')

@section('content_header')
    <h1>Danh sách Website</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="mr-2">Sắp xếp theo:</span>
                    <a href="#" class="status-filter font-weight-bold text-primary" data-status="">Tất cả</a> |
                    <a href="#" class="status-filter text-dark" data-status="1">Hoạt động</a> |
                    <a href="#" class="status-filter text-dark" data-status="2">Backuped</a> |
                    <a href="#" class="status-filter text-dark" data-status="3">Tạm ngưng</a> |
                    <a href="#" class="status-filter text-dark" data-status="4">Hết hạn</a> |
                    <a href="#" class="status-filter text-dark" data-status="soon">Sắp hết hạn</a>
                </div>
                <div>
                    <a href="{{ route('design-websites.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Thêm Website
                    </a>
                </div>
            </div>
            <table class="table table-bordered table-striped" id="websites-table">
                <thead>
                    <tr>
                        <th class="text-center">STT</th>
                        <th>Website</th>
                        <th class="text-center">Ngày hết hạn</th>
                        <th>Phí gia hạn</th>
                        <th>Hosting</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

<div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="noteForm">
      @csrf
      <input type="hidden" name="website_id" id="note-website-id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ghi chú</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <textarea name="note" id="note-content" class="form-control" rows="4" placeholder="Nhập ghi chú..."></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Lưu</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
        </div>
      </div>
    </form>
  </div>
</div>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .tooltip-inner {
            max-width: 400px;
            text-align: left;
            font-size: 14px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        let selectedStatus = '';

        $(function() {
            $('#websites-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("design-websites.data") }}',
                        data: function (d) {
                            d.status = selectedStatus;
                            if (selectedStatus === 'soon') {
                                d.expiration_filter = 'soon';
                            }
                        }
                    },
                    pageLength: 100,
                    lengthMenu: [[100, 200, 500, -1], [100, 200, 500, "Tất cả"]],
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'domain_column', name: 'domain.domain', title: 'Website' },
                        { data: 'username_customer', name: 'username_customer', title: 'Khách hàng' },
                        { data: 'expiration_date', name: 'expiration_date', title: 'Ngày hết hạn' },
                        { data: 'prices', name: 'prices', title: 'Phí gia hạn' },
                        { data: 'status_control', name: 'status', title: 'Trạng thái', orderable: false, searchable: false },
                        { data: 'action', name: 'action', title: 'Thao tác', orderable: false, searchable: false }
                    ]
                });
        });

        $(document).on('click', '.status-filter', function (e) {
            e.preventDefault();
            selectedStatus = $(this).data('status');
            $('#websites-table').DataTable().ajax.reload();

            $('.status-filter').removeClass('text-primary font-weight-bold');
            $(this).addClass('text-primary font-weight-bold');
        });
        
    </script>
   <script>
    $(function () {
        // Kích hoạt tooltip mỗi khi bảng được draw lại
        $('#websites-table').on('draw.dt', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });

    $(document).on('change', '.status-select', function () {
        var id = $(this).data('id');
        var status = $(this).val();
        $.ajax({
            url: '/design-websites/' + id + '/update-status',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function (res) {
                toastr.success('Cập nhật trạng thái thành công!');
            },
            error: function () {
                toastr.error('Cập nhật thất bại!');
            }
        });
    });

    $(document).on('click', '.note-btn', function () {
        const note = $(this).data('note') || '';
        const id = $(this).data('id');
        $('#note-website-id').val(id);
        $('#note-content').val(note);
        $('#noteModal').modal('show');
    });

    $('#noteForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#note-website-id').val();
        const note = $('#note-content').val();
        $.ajax({
            url: '/design-websites/' + id + '/update-note',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                note: note
            },
            success: function () {
                toastr.success('Đã lưu ghi chú!');
                $('#noteModal').modal('hide');
                $('#websites-table').DataTable().ajax.reload(null, false);
            },
            error: function () {
                toastr.error('Lỗi khi lưu ghi chú!');
            }
        });
    });
</script>
@stop