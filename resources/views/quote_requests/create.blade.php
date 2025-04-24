@extends('adminlte::page')

@section('title', 'Tạo Yêu Cầu Báo Giá')

@section('content_header')
    <h1>Tạo Yêu Cầu Báo Giá</h1>
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <form action="{{ route('quote-requests.store') }}" method="POST">
                    @csrf
                    <!-- Quote Domain -->
                    <div class="form-group">
                        <label for="quote_domain_id">Quote Domain</label>
                        <div class="input-group">
                            <select name="quote_domain_id" class="form-control select2 @error('quote_domain_id') is-invalid @enderror" required>
                                <option value="">-- Chọn Quote Domain --</option>
                                @foreach ($quoteDomains as $domain)
                                    <option value="{{ $domain->id }}" {{ old('quote_domain_id') == $domain->id ? 'selected' : '' }}>
                                        {{ $domain->name }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#addQuoteDomainModal">
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
                        <textarea name="keywords" class="form-control @error('keywords') is-invalid @enderror" required>{{ old('keywords') }}</textarea>
                        @error('keywords')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
            
                    <!-- Vị trí top -->
                    <div class="form-group">
                        <label for="top_position">Vị trí top trên Google</label>
                        <select name="top_position[]" multiple="multiple" class="form-control select2 @error('top_position') is-invalid @enderror" required>
                            <option value="">-- Chọn vị trí top --</option>
                            <option value="1-2" {{ old('top_position') == '1-2' ? 'selected' : '' }}>1-2</option>
                            <option value="1-3" {{ old('top_position') == '1-3' ? 'selected' : '' }}>1-3</option>
                            <option value="1-4" {{ old('top_position') == '1-4' ? 'selected' : '' }}>1-4</option>
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
                        <input type="text" name="region" class="form-control @error('region') is-invalid @enderror"
                            value="{{ old('region') }}" required>
                        @error('region')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
            
                    <!-- Loại từ khóa -->
                    <div class="form-group">
                        <label for="keyword_type">Loại từ khóa</label>
                        <select name="keyword_type[]" multiple="multiple" class="form-control select2 @error('keyword_type') is-invalid @enderror" required>
                            <option value="">-- Chọn loại từ khóa --</option>
                            <option value="Đối sánh cụm từ" {{ old('keyword_type') == 'Đối sánh cụm từ' ? 'selected' : '' }}>Đối sánh
                                cụm từ</option>
                            <option value="Đối sánh chính xác" {{ old('keyword_type') == 'Đối sánh chính xác' ? 'selected' : '' }}>Đối
                                sánh chính xác</option>
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
                        <select name="campaign_type[]" multiple="multiple" class="form-control select2 @error('campaign_type') is-invalid @enderror" required>
                            <option value="">-- Chọn hình thức chiến dịch --</option>
                            <option value="Trọn gói" {{ old('campaign_type') == 'Trọn gói' ? 'selected' : '' }}>Trọn gói</option>
                            <option value="Ngân sách" {{ old('campaign_type') == 'Ngân sách' ? 'selected' : '' }}>Ngân sách</option>
                        </select>
                        @error('campaign_type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
            
                    <!-- Nút gửi -->
                    <button type="submit" class="btn btn-primary">Gửi Yêu Cầu</button>
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
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                            required>
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
@stop

@section('js')

    <script>
          // Khởi tạo Select2
          $('.select2').select2({
                placeholder: '-- Chọn Domain ( Tên Miền ) --',
                allowClear: false
            });
        @if ($errors->has('name'))
            $('#addQuoteDomainModal').modal('show');
        @endif
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
@stop
