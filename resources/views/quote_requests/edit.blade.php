{{-- resources/views/quote_requests/edit.blade.php --}}

@extends('adminlte::page')

@section('title', 'Chỉnh sửa Yêu Cầu Báo Giá')

@section('content_header')
    <h1>Chỉnh sửa Yêu Cầu Báo Giá</h1>
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <form
                    action="{{ $quoteRequest->status === 'pending' ? route('quote-requests.update', $quoteRequest->id) : route('quote-requests.store') }}"
                    method="POST">
                    @csrf
                    @if ($quoteRequest->status === 'pending')
                        @method('PUT')
                    @endif

                    <!-- Quote Domain -->
                    <div class="form-group">
                        <label for="quote_domain_id">Quote Domain</label>
                        <div class="input-group">
                            <select name="quote_domain_id" id="quote_domain_id"
                                class="form-control select2 @error('quote_domain_id') is-invalid @enderror" required>
                                <option value="">-- Chọn Quote Domain --</option>
                                @foreach ($quoteDomains as $domain)
                                    <option value="{{ $domain->id }}"
                                        {{ (old('quote_domain_id') ?? $quoteRequest->quote_domain_id) == $domain->id ? 'selected' : '' }}>
                                        {{ $domain->name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-secondary" data-toggle="modal"
                                    data-target="#addQuoteDomainModal">
                                    Thêm Quote Domain Mới
                                </button>
                            </div>
                        </div>
                        @error('quote_domain_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small>Nếu Quote Domain chưa có trong danh sách, vui lòng thêm mới.</small>
                    </div>

                    <!-- Từ khóa -->
                    <div class="form-group">
                        <label for="keywords">Từ khóa</label>
                        <textarea name="keywords" id="keywords" class="form-control @error('keywords') is-invalid @enderror" required>{{ old('keywords', $quoteRequest->keywords) }}</textarea>
                        @error('keywords')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Vị trí top -->
                    <div class="form-group">
                        <label for="top_position">Vị trí top trên Google</label>
                        <select name="top_position[]" id="top_position"
                            class="form-control select2 @error('top_position') is-invalid @enderror" multiple="multiple"
                            required>
                            <option value="">-- Chọn vị trí top --</option>
                            <option value="1-2"
                                {{ collect(old('top_position', $quoteRequest->top_position))->contains('1-2') ? 'selected' : '' }}>
                                1-2</option>
                            <option value="1-3"
                                {{ collect(old('top_position', $quoteRequest->top_position))->contains('1-3') ? 'selected' : '' }}>
                                1-3</option>
                            <option value="1-4"
                                {{ collect(old('top_position', $quoteRequest->top_position))->contains('1-4') ? 'selected' : '' }}>
                                1-4</option>
                        </select>
                        @error('top_position')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Khu vực chạy quảng cáo -->
                    <div class="form-group">
                        <label for="region">Khu vực chạy quảng cáo</label>
                        <input type="text" name="region" id="region"
                            class="form-control @error('region') is-invalid @enderror"
                            value="{{ old('region', $quoteRequest->region) }}" required>
                        @error('region')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Loại từ khóa -->
                    <div class="form-group">
                        <label for="keyword_type">Loại từ khóa</label>
                        <select name="keyword_type[]" id="keyword_type"
                            class="form-control select2 @error('keyword_type') is-invalid @enderror" multiple="multiple"
                            required>
                            <option value="">-- Chọn loại từ khóa --</option>
                            <option value="Đối sánh cụm từ"
                                {{ collect(old('keyword_type', $quoteRequest->keyword_type))->contains('Đối sánh cụm từ') ? 'selected' : '' }}>
                                Đối sánh cụm từ</option>
                            <option value="Đối sánh chính xác"
                                {{ collect(old('keyword_type', $quoteRequest->keyword_type))->contains('Đối sánh chính xác') ? 'selected' : '' }}>
                                Đối sánh chính xác</option>
                        </select>
                        @error('keyword_type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Hình thức chiến dịch -->
                    <div class="form-group">
                        <label for="campaign_type">Hình thức chiến dịch</label>
                        <select name="campaign_type[]" id="campaign_type"
                            class="form-control select2 @error('campaign_type') is-invalid @enderror" multiple="multiple"
                            required>
                            <option value="">-- Chọn hình thức chiến dịch --</option>
                            <option value="Trọn gói"
                                {{ collect(old('campaign_type', $quoteRequest->campaign_type))->contains('Trọn gói') ? 'selected' : '' }}>
                                Trọn gói</option>
                            <option value="Ngân sách"
                                {{ collect(old('campaign_type', $quoteRequest->campaign_type))->contains('Ngân sách') ? 'selected' : '' }}>
                                Ngân sách</option>
                        </select>
                        @error('campaign_type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Nút gửi -->
                    <button type="submit" class="btn btn-primary">Cập nhật Yêu Cầu</button>
                    <a href="{{ route('quote-requests.index') }}" class="btn btn-secondary">Quay lại</a>
                </form>

                <!-- Modal thêm Quote Domain mới -->
                <div class="modal fade" id="addQuoteDomainModal" tabindex="-1" role="dialog"
                    aria-labelledby="addQuoteDomainModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form action="{{ route('quote-domains.store') }}" method="POST">
                                @csrf
                                <!-- Truyền thêm thông tin để redirect back -->
                                <input type="hidden" name="redirect_back" value="1">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addQuoteDomainModalLabel">Thêm Quote Domain Mới</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Tên Quote Domain -->
                                    <div class="form-group">
                                        <label for="name">Domain (Tên Miền)</label>
                                        <input type="text" name="name" id="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-primary">Thêm Domain</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- Thêm CSS tùy chỉnh nếu cần -->
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
