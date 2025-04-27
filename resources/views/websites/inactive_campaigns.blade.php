@extends('adminlte::page')

@section('title', 'Domains with Inactive Campaigns')

@section('content_header')
    <h1>Website Ngừng Hoạt Động 3 Tháng Trước</h1>
@stop

@section('content')
    <table class="table table-success table-striped table-bordered table-hover" id="inactive-campaigns-table">
        <thead class="text-center">
            <tr>
                <th>STT</th>
                <th>Domain</th>
                <th>Kết Thúc</th>
                <th>Nhân Viên Sales</th>
            </tr>
        </thead>
    </table>
@stop

@section('js')
<script>
$(function () {
    $('#inactive-campaigns-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('websites.inactive-campaigns') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'latestCampaign.end', name: 'latestCampaign.end' },
            { data: 'user.fullname', name: 'user.fullname' }
        ],
        lengthMenu: [[100, 200, 500, -1], [100, 200, 500, "Tất cả"]],
        pageLength: 100,
        language: {
            search: "Tìm kiếm:",
            paginate: {
                previous: "Trước",
                next: "Tiếp"
            },
            zeroRecords: "Không tìm thấy dữ liệu",
            info: "Hiển thị _PAGE_/_PAGES_",
            lengthMenu: "Hiển thị _MENU_ dòng mỗi trang"
        }
    });
});
</script>
@stop
