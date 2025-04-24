@extends('adminlte::page')

@section('title', 'Search Campaigns')

@section('content_header')
    <h1>Tìm kiếm từ khoá chứa campaign</h1>
@stop

@section('css')
    @routes()
@stop
@section('content')
    <div class="card">
        <div class="card-header">
            <form id="search-form">
                <div class="input-group">
                    <input type="text" id="keyword" name="keyword" class="form-control" placeholder="Enter keyword">
                    <div class="input-group-append">
                        <button type="button" id="search-button" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <table id="campaigns-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Website</th>
                        <th>Keywords</th>
                        <th>Thời gian</th>
                        <th>Ngân sách</th>

                        <th>Trạng thái</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            var table = $('#campaigns-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: route('campaigns.search'),
                    data: function(d) {
                        d.keyword = $('#keyword').val(); // Thêm từ khóa tìm kiếm vào request
                    }
                },
                columns: [{
                        data: 'website',
                        name: 'website'
                    },
                    {
                        data: 'keywords',
                        name: 'keywords'
                    },
                    {
                        data: 'time',
                        name: 'time'
                    },
                    {
                        data: 'budgetmonth',
                        name: 'budgetmonth',
                        class:'text-right'
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },
                 
                ]
            });
            // Ngăn form submit khi nhấn Enter
            $('#search-form').on('submit', function(e) {
                e.preventDefault(); // Ngăn sự kiện mặc định (submit form)

                if (keyword !== '') {
                    table.draw(); // Tải lại DataTable nếu có từ khóa
                } else {
                    alert('Please enter a keyword to search.'); // Thông báo nếu không có từ khóa
                    table.clear().draw(); // Xóa dữ liệu nếu không có từ khóa
                }
            });
            // Xử lý nút tìm kiếm
            $('#search-button').click(function() {
                if (keyword !== '') {
                    table.draw(); // Tải lại DataTable nếu có từ khóa
                } else {
                    alert('Please enter a keyword to search.'); // Thông báo nếu không có từ khóa
                    table.clear().draw(); // Xóa dữ liệu nếu không có từ khóa
                }
            });
        });
    </script>
@stop
