@extends('adminlte::page')

@section('title', 'Tạo chiến dịch mới')

@section('css')
    <style>
        .form-label {
            font-weight: bold;
            font-size: 1rem;
        }

        .form-control {
            border-radius: 0.25rem;
        }

        .btn-custom {
            background-color: #28a745;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .card-custom {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Custom style for the form */
        .form-label {
            font-weight: bold;
            font-size: 1rem;
        }

        .form-control {
            border-radius: 0.25rem;
        }

        .btn-custom {
            background-color: #28a745;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .card-custom {
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }

        /* Lớp phủ toàn màn hình */
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            /* Màu nền mờ */
            z-index: 9998;
            /* Đảm bảo lớp phủ nằm phía trên nội dung */
            display: none;
            /* Ẩn lớp phủ khi không cần */
            align-items: center;
            justify-content: center;
        }

        /* Hiển thị spinner ở giữa màn hình */
        .spinner-border {
            z-index: 9999;
            /* Đảm bảo spinner nằm trên cùng */
        }

        #overlay {
            transition: opacity 0.3s ease-in-out;
            opacity: 0;
            /* Bắt đầu ẩn */
        }

        #overlay.show {
            opacity: 1;
            /* Hiển thị khi thêm class 'show' */
        }
    </style>
@stop

@section('content_header')
    <h1 class="text-center text-primary">Tạo chiến dịch Google Ads mới</h1>
@stop

@section('content')
    <!-- Hiển thị thông báo thành công nếu có -->
    @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header bg-info text-white">
                        <h3 class="text-center">Thông tin chiến dịch</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Đã xảy ra lỗi!</h4>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <!-- Form bắt đầu -->
                        <form id="create-all-form" action="{{ route('admin.ads.create-campaign-post') }}" method="post">
                            @csrf
                            <!-- Thông tin chiến dịch -->
                            <div class="form-group">
                                {{-- <label for="account_id" class="form-label"><i class="fas fa-id-badge"></i> ID Tài
                                    khoản</label> --}}
                                    <label for="account_ids" class="form-label"><i class="fas fa-id-badge"></i> ID Tài khoản
                                        (Mỗi dòng một ID)</label>
                                    <textarea class="form-control" id="account_ids" name="account_ids" rows="4" required
                                        placeholder="Nhập các ID tài khoản Google Ads, mỗi ID trên một dòng">{{ old('account_ids') }}</textarea>
                                 @error('account_ids')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                                {{-- <input type="text" class="form-control @error('account_id') is-invalid @enderror"
                                    id="account_id" name="account_id" value="{{ old('account_id') }}" required
                                    placeholder="Nhập ID tài khoản Google Ads"> --}}
                                {{-- @error('account_id')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror --}}
                            </div>

                            <div class="form-group">
                                <label for="campaign_name" class="form-label"><i class="fas fa-flag"></i> Tên chiến
                                    dịch</label>
                                <input type="text" class="form-control @error('campaign_name') is-invalid @enderror"
                                    id="campaign_name" name="campaign_name" value="{{ old('campaign_name') }}" required
                                    placeholder="Nhập tên chiến dịch">
                                @error('campaign_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="budget" class="form-label"><i class="fas fa-dollar-sign"></i> Ngân sách
                                    (USD)</label>
                                <input type="text" class="form-control @error('budget') is-invalid @enderror"
                                    id="budget" name="budget" value="{{ old('budget') }}" required
                                    placeholder="Ngân sách cho chiến dịch">
                                @error('budget')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="start_date" class="form-label">Ngày Bắt Đầu</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', \Carbon\Carbon::today()->toDateString()) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="advertising_channel_type" class="form-label"><i class="fas fa-bullhorn"></i>
                                    Loại kênh quảng cáo</label>
                                <select class="form-control @error('advertising_channel_type') is-invalid @enderror"
                                    id="advertising_channel_type" name="advertising_channel_type" required>
                                    <option value="SEARCH"
                                        {{ old('advertising_channel_type') == 'SEARCH' ? 'selected' : '' }}>Search - Tìm
                                        kiếm</option>
                                    <option value="DISPLAY"
                                        {{ old('advertising_channel_type') == 'DISPLAY' ? 'selected' : '' }}>Display - Hiển
                                        thị</option>
                                    <option value="SHOPPING"
                                        {{ old('advertising_channel_type') == 'SHOPPING' ? 'selected' : '' }}>Shopping -
                                        Mua sắm</option>
                                </select>
                                @error('advertising_channel_type')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <!-- Thông tin nhóm quảng cáo -->
                            <h4>Thông tin nhóm quảng cáo</h4>
                            <div class="form-group">
                                <label for="adgroup_name" class="form-label"><i class="fas fa-layer-group"></i> Tên nhóm
                                    quảng cáo</label>
                                <input type="text" class="form-control @error('adgroup_name') is-invalid @enderror"
                                    id="adgroup_name" name="adgroup_name" value="{{ old('adgroup_name') }}" required
                                    placeholder="Nhập tên nhóm quảng cáo">
                                @error('adgroup_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="cpc_bid" class="form-label"><i class="fas fa-dollar-sign"></i> Giá thầu CPC
                                    (USD)</label>
                                <input type="text" class="form-control @error('cpc_bid') is-invalid @enderror"
                                    id="cpc_bid" name="cpc_bid" value="{{ old('cpc_bid') }}" required
                                    placeholder="Nhập giá thầu CPC cho nhóm quảng cáo">
                                @error('cpc_bid')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label"><i class="fas fa-toggle-on"></i> Trạng
                                    thái</label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="ENABLED" {{ old('status') == 'ENABLED' ? 'selected' : '' }}>Enabled
                                    </option>
                                    <option value="PAUSED" {{ old('status') == 'PAUSED' ? 'selected' : '' }}>Paused
                                    </option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <!-- Thông tin mẫu quảng cáo -->
                            <h4>Thông tin mẫu quảng cáo</h4>
                            @foreach (range(1, 4) as $i)
                                <div class="form-group">
                                    <label for="ad_headline" class="form-label"><i class="fas fa-ad"></i> Tiêu đề quảng
                                        cáo
                                        {{ $i }}</label>
                                    <input type="text"
                                        class="form-control @error('ad_headline.' . ($i - 1)) is-invalid @enderror"
                                        id="ad_headline" name="ad_headline[]"
                                        value="{{ old('ad_headline.' . ($i - 1)) }}" ($i<=2) ?'required'?''
                                        placeholder="Nhập tiêu đề quảng cáo">
                                    @error('ad_headline.' . ($i - 1))
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            @endforeach

                            @foreach (range(1, 4) as $i)
                                <div class="form-group">
                                    <label for="ad_description" class="form-label"><i class="fas fa-align-left"></i> Mô
                                        tả quảng cáo {{ $i }}</label>
                                    <input type="text"
                                        class="form-control @error('ad_description.' . ($i - 1)) is-invalid @enderror"
                                        id="ad_description" name="ad_description[]"
                                        value="{{ old('ad_description.' . ($i - 1)) }}" ($i<=2)?'required'?''
                                        placeholder="Nhập mô tả quảng cáo">
                                    @error('ad_description.' . ($i - 1))
                                        <span class="invalid-feedback"
                                            role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            @endforeach

                            <div class="form-group">
                                <label for="final_url" class="form-label"><i class="fas fa-link"></i> URL đích</label>
                                <input type="url" class="form-control @error('final_url') is-invalid @enderror"
                                    id="final_url" name="final_url" value="{{ old('final_url') }}" required
                                    placeholder="Nhập URL đích">
                                @error('final_url')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <!-- Thông tin từ khóa -->
                            <h4>Thông tin từ khóa</h4>
                            <div class="form-group">
                                <label for="keywords" class="form-label"><i class="fas fa-key"></i> Từ khóa</label>
                                <textarea class="form-control @error('keywords') is-invalid @enderror" rows="5" id="keywords"
                                    name="keywords" placeholder="Nhập danh sách từ khóa, mỗi từ khóa một dòng">{{ old('keywords') }}</textarea>
                                @error('keywords')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <!-- Nút gửi -->
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-custom btn-block"><i
                                        class="fas fa-check-circle"></i> Tạo chiến dịch và các thành phần</button>
                            </div>
                        </form>
                        <!-- Hiệu ứng loading spinner -->
                        <div id="overlay">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
