@extends('adminlte::page')

@section('title', 'Tạo nhóm quảng cáo mới')

@section('css')
<style>
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
    <h1 class="text-center text-primary">Tạo nhóm quảng cáo mới</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header bg-info text-white">
                        <h3 class="text-center">Thông tin nhóm quảng cáo</h3>
                    </div>
                    <div class="card-body">
                        <!-- Form bắt đầu -->
                        <form id="create-adgroup-form" action="{{ route('admin.ads.create-adgroup-post') }}" method="post">
                            @csrf
                            <!-- Tên nhóm quảng cáo -->
                            <div class="form-group">
                                <label for="adgroup_name" class="form-label"><i class="fas fa-layer-group"></i> Tên nhóm quảng cáo</label>
                                <input type="text" class="form-control @error('adgroup_name') is-invalid @enderror"
                                    id="adgroup_name" name="adgroup_name" required value="{{ old('adgroup_name') }}">
                                @error('adgroup_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Giá thầu CPC -->
                            <div class="form-group">
                                <label for="cpc_bid" class="form-label"><i class="fas fa-dollar-sign"></i> Giá thầu CPC (USD)</label>
                                <input type="number" class="form-control @error('cpc_bid') is-invalid @enderror"
                                    id="cpc_bid" name="cpc_bid" value="{{ old('cpc_bid') }}" required>
                                @error('cpc_bid')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Chiến dịch liên kết -->
                            <div class="form-group">
                                <label for="campaign_id" class="form-label"><i class="fas fa-bullhorn"></i> Chiến dịch liên kết</label>
                                <select class="form-control @error('campaign_id') is-invalid @enderror" id="campaign_id"
                                    name="campaign_id" required>
                                    <!-- Lấy danh sách các chiến dịch từ cơ sở dữ liệu hoặc API -->
                                    {{-- @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                    @endforeach --}}
                                </select>
                                @error('campaign_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Trạng thái nhóm quảng cáo -->
                            <div class="form-group d-none">
                                <label for="status" class="form-label"><i class="fas fa-toggle-on"></i> Trạng thái</label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="ENABLED" selected>Enabled</option>
                                    <option value="PAUSED">Paused</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nút Gửi -->
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-check"></i> Tạo nhóm quảng cáo</button>
                            </div>
                        </form>

                        <!-- Hiệu ứng loading spinner -->
                        <div id="overlay">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <!-- Form kết thúc -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hiển thị thông báo thành công nếu có -->
    @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif
@stop