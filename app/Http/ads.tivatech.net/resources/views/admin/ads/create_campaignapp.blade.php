@extends('adminlte::page')

@section('title', 'Tạo App Campaign mới')

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
    </style>
@stop

@section('content_header')
    <h1 class="text-center text-primary">Tạo App Campaign Google Ads mới</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-custom">
                    <div class="card-header bg-info text-white">
                        <h3 class="text-center">Thông tin App Campaign</h3>
                    </div>
                    <div class="card-body">
                        <!-- Hiển thị lỗi validation -->
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

                        <!-- Hiển thị thông báo thành công -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Thành công!</h4>
                                <p>{{ session('success') }}</p>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Form bắt đầu -->
                        <form id="create-app-campaign-form" action="{{ route('admin.ads.app-campaign.create.post') }}"
                            method="post">
                            @csrf

                            <!-- Thông tin App Campaign -->
                            {{-- <div class="form-group">
                                <label for="customer_id" class="form-label"><i class="fas fa-id-badge"></i> ID Tài khoản</label>
                                <input type="text" class="form-control" id="customer_id" name="customer_id" value="{{ old('customer_id') }}" required placeholder="Nhập ID tài khoản Google Ads">
                            </div> --}}
                            <label for="account_ids" class="form-label"><i class="fas fa-id-badge"></i> ID Tài khoản
                                (Mỗi dòng một ID)</label>
                            <textarea class="form-control" id="account_ids" name="account_ids" rows="4" required
                                placeholder="Nhập các ID tài khoản Google Ads, mỗi ID trên một dòng">{{ old('account_ids') }}</textarea>
                            @error('account_ids')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                            <div class="form-group">
                                <label for="name" class="form-label"><i class="fas fa-flag"></i> Tên App
                                    Campaign</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name') }}" required placeholder="Nhập tên App Campaign">
                            </div>

                            <div class="form-group">
                                <label for="budget_amount_micros" class="form-label"><i class="fas fa-dollar-sign"></i> Ngân
                                    sách (USD)</label>
                                <input type="text" class="form-control" id="budget_amount_micros"
                                    name="budget_amount_micros" value="{{ old('budget_amount_micros') }}" required
                                    placeholder="Ngân sách cho chiến dịch">
                            </div>
                            <div class="form-group">
                                <label for="start_date" class="form-label">Ngày Bắt Đầu</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="{{ old('start_date', \Carbon\Carbon::today()->toDateString()) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="app_id" class="form-label">App ID</label>
                                <input type="text" class="form-control" id="app_id" name="app_id"
                                    value="{{ old('app_id') }}" required>
                            </div>

                            <div class="form-group">
                                <label for="app_store" class="form-label">App Store</label>
                                <select class="form-control" id="app_store" name="app_store" required>
                                    <option value="GOOGLE_APP_STORE"
                                        {{ old('app_store') == 'GOOGLE_APP_STORE' ? 'selected' : '' }}>Google Play Store
                                    </option>
                                    <option value="APPLE_APP_STORE"
                                        {{ old('app_store') == 'APPLE_APP_STORE' ? 'selected' : '' }}>Apple App Store
                                    </option>
                                </select>
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-custom btn-block">Tạo Chiến Dịch</button>
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
