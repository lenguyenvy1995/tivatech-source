@extends('adminlte::page')

@section('title', 'Danh Sách Quote Domain')

@section('content_header')
    <h1>Danh Sách Quote Domain</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Quote Domain</th>
                <th>Số lượng báo giá</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quoteDomains as $domain)
            <tr>
                <td>{{ $domain->id }}</td>
                <td>{{ $domain->name }}</td>
                <td>{{ $domain->quotes_count }}</td>
                <td>
                    {{-- <a href="{{ route('quote-domains.show', $domain->id) }}" class="btn btn-info">Xem chi tiết</a> --}}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@stop
@section('js')
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
