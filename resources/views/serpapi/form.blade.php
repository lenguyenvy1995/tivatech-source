@extends('adminlte::page')

@section('title', 'Tìm kiếm từ khoá')
@section('content_header')
    <h1>Tìm kiếm từ khoá</h1>
@stop
@section('css')
    @routes
@stop
@section('content')
    <div class="container">
        <form id="searchForm">
            <div class="form-group">
                <input class="form-control" type="text" name="query" placeholder="Vui lòng nhập từ khoá" required>
            </div>
            <div class="form-group">
                  <select class="form-control" name="location" id="location">
                    <option value="Ho Chi Minh City, Ho Chi Minh City, Vietnam">TP. Hồ Chí Minh</option>
                    <option value="Hanoi,Hanoi,Vietnam">Hà Nội</option>
                    <option value="Da Nang,Da Nang,Vietnam">Đà Nẵng</option>
                    <option value="Can Tho,Can Tho,Vietnam">Cần Thơ</option>
                  </select>
            </div>
            <button type="submit" class="btn btn-primary">CHECK</button>
        </form>

        <div id="results"></div> <!-- Khu vực hiển thị kết quả -->
    </div>
@endsection

@section('js')
    @routes
    <script>
        $(document).ready(function() {
            $('#searchForm').on('submit', function(e) {
                e.preventDefault(); // Ngăn chặn việc gửi form mặc định

                // Lấy dữ liệu từ form
                var formData = $(this).serialize();

                // Gửi yêu cầu AJAX
                $.ajax({
                    url: route('serpapi'), // Đường dẫn đến route xử lý tìm kiếm
                    type: 'GET',
                    data: formData,
                    success: function(data) {
                        // Hiển thị kết quả
                        $('#results').html(data);
                    },
                    error: function(xhr, status, error) {
                        $('#results').html('<p>Đã xảy ra lỗi: ' + error + '</p>');
                    }
                });
            });
        });
    </script>
@stop
