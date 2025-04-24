@extends('adminlte::page')

@section('title', 'Tạo tài khoản')
@section('css')
    <style>
        /* Lớp phủ toàn màn hình */
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9998;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .spinner-border {
            z-index: 9999;
        }

        #overlay.show {
            display: flex;
        }

        .form-label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 0.5rem;
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
    </style>
@stop

@section('content_header')
    <h1 class="text-center text-primary">Tạo tài khoản Google Ads mới</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header bg-info text-white">
                        <h4 class="text-center">Thông tin tài khoản</h4>
                    </div>
                    <div class="card-body">
                        <!-- Form bắt đầu -->
                        <form id="create-account-form" action="{{ route('admin.ads.create-account-post') }}" method="post">
                            @csrf

                            <!-- Số lượng -->
                            <div class="form-group">
                                <label for="quantity" class="form-label"><i class="fas fa-sort-numeric-up"></i> Số lượng (Quantity)</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                    id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tên tài khoản -->
                            <div class="form-group">
                                <label for="account_name" class="form-label"><i class="fas fa-user"></i> Tên tài khoản (Account Name)</label>
                                <input type="text" class="form-control @error('account_name') is-invalid @enderror"
                                    id="account_name" name="account_name" required value="{{ old('account_name') }}">
                                @error('account_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Loại tiền tệ -->
                            <div class="form-group">
                                <label for="currency" class="form-label"><i class="fas fa-dollar-sign"></i> Loại tiền tệ (Currency)</label>
                                <select class="form-control @error('currency') is-invalid @enderror" id="currency"
                                    name="currency" required>
                                    <option value="USD" selected>USD - Đô la Mỹ</option>
                                    <option value="VND">VND - Việt Nam Đồng</option>
                                    <option value="JPY">JPY - Yên Nhật</option>
                                    <option value="ILS">ILS - Shekel Israel (ILS)</option>
                                    <option value="HKD">HKD - HỒNG KÔNG KUN (HKD)</option>
                                    <option value="INR">INR - ẤN ĐỘ RUPEE KUN (HKD)</option>
                                    <option value="BRL">BRL - Brazilian Real (BRL)</option>
                                    <option value="ARS">ARS - Argentine Peso (ARS)</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- https://developers.google.com/google-ads/api/data/codes-formats --}}
                            <!-- Múi giờ -->
                            <div class="form-group">
                                <label for="time_zone" class="form-label"><i class="fas fa-clock"></i> Múi giờ (Time Zone)</label>
                                <select class="form-control @error('time_zone') is-invalid @enderror" id="time_zone"
                                    name="time_zone" required>
                                    <option value="America/Los_Angeles">America/Los_Angeles (GMT-7)</option>
                                    <option value="America/New_York" selected>America/New_York (GMT-5)</option>
                                    <option value="America/Sao_Paulo">America/Sao_Paul (GMT -3)</option>
                                    <option value="America/Los_Angeles">America/Los_Angeles (GMT -8)</option>
                                    <option value="Asia/Dubai">DuBai(GMT+4:00) </option>
                                    <option value="Asia/Kolkata">ẤN ĐỘ Asia/Kolkata(GMT+5:30) </option>
                                    <option value="Antarctica/Davis">Antarctica/Davis(GMT+7:00) </option>
                                    <option value="Asia/Ho_Chi_Minh">Asia/Ho_Chi_Minh (GMT+7:00)</option>
                                    <option value="Asia/Hong_Kong">HK (GMT+8:00) </option>
                                    <option value="Asia/Tokyo">Asia/Tokyo (GMT+9)</option>
                                    <option value="Pacific/Guadalcanal">Đảo Solomon (GMT+11:00) </option>

                                </select>
                                @error('time_zone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nút gửi -->
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-check-circle"></i> Gửi thông tin</button>
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

                @if (session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop