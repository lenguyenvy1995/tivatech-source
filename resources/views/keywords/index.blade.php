@extends('adminlte::page')

@section('title', 'Quản lý Từ Khoá')

@section('content_header')
    <h1>Quản lý Từ Khoá</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addKeywordModal">
                <i class="fas fa-plus"></i> Thêm Từ Khoá Mới
            </button>
        </div>

        <div class="card-body">
            <!-- Modal Thêm Nhiều Từ Khoá -->
            <div class="modal fade" id="addKeywordModal" tabindex="-1" role="dialog" aria-labelledby="addKeywordModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <form id="add-keywords-form">
                  @csrf
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Thêm Nhiều Từ Khoá</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <div class="form-group">
                        <label>Nhập từ khoá (mỗi dòng 1 từ)</label>
                        <textarea name="keywords" class="form-control" rows="8" placeholder="Nhập mỗi từ khoá trên 1 dòng..."></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                      <button type="submit" class="btn btn-success">Lưu Từ Khoá</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered table-striped" id="keywords-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Từ Khoá</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
$(function () {
    $('#keywords-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('keywords.index') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 200,
        language: {
            "lengthMenu": "Hiển thị _MENU_ dòng mỗi trang",
            "zeroRecords": "Không tìm thấy từ khoá",
            "info": "Hiển thị trang _PAGE_ trên _PAGES_",
            "infoEmpty": "Không có dữ liệu",
            "infoFiltered": "(lọc từ _MAX_ từ khoá)",
            "search": "Tìm kiếm:",
            "paginate": {
                "first": "Đầu",
                "last": "Cuối",
                "next": "Tiếp",
                "previous": "Trước"
            }
        }
    });

    $('#add-keywords-form').submit(function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        $.ajax({
            url: '{{ route('keywords.store') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#addKeywordModal').modal('hide');
                $('#keywords-table').DataTable().ajax.reload();
                toastr.success('Thêm từ khoá thành công!');
            },
            error: function(xhr) {
                toastr.error('Đã có lỗi xảy ra!');
            }
        });
    });
});
</script>
@stop