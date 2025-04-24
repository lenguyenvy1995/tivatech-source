@extends('adminlte::page')

@section('title', 'Chi tiết Website')

@section('content_header')
    <h1>Chi tiết Website</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <strong>Thông tin Website</strong>
                </div>
                <div class="card-body">

                    <h5 class="mb-3"><strong>Thông tin chung</strong></h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Khách hàng:</strong> {{ $designWebsite->username_customer }}</p>
                            <p><strong>Ngày đăng ký:</strong> {{ optional($designWebsite->registration_date)->format('d/m/Y') }}</p>
                            <p><strong>Email:</strong> {{ $designWebsite->email }}</p>
                            <p><strong>Nhân viên sales:</strong> {{ $designWebsite->sales_staff }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tên miền:</strong> {{ $designWebsite->domain->domain ?? '(chưa có)' }}</p>
                            <p><strong>Ngày hết hạn:</strong> {{ optional($designWebsite->expiration_date)->format('d/m/Y') }}</p>
                            <p><strong>Số điện thoại:</strong> {{ $designWebsite->customer_phone }}</p>
                            <p><strong>Giá:</strong> {{ number_format((int)$designWebsite->prices, 0, ',', '.') }} đ</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3"><strong>Thông tin đăng nhập domain</strong></h5>
                            <p><strong>Link đăng nhập:</strong> {{ $designWebsite->domain->login_link ?? '-' }}</p>
                            <p><strong>Tài khoản:</strong> {{ $designWebsite->domain->account ?? '-' }}</p>
                            <p><strong>Mật khẩu:</strong> {{ $designWebsite->domain->password ?? '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3"><strong>Thông tin Hosting</strong></h5>
                            <p><strong>Nhà cung cấp:</strong> {{ $designWebsite->hosting->supplier ?? '-' }}</p>
                            <p><strong>Link đăng nhập:</strong> {{ $designWebsite->hosting->login_link ?? '-' }}</p>
                            <p><strong>Tài khoản:</strong> {{ $designWebsite->hosting->account ?? '-' }}</p>
                            <p><strong>Mật khẩu:</strong> {{ $designWebsite->hosting->password ?? '-' }}</p>
                            <p><strong>Dung lượng:</strong> {{ $designWebsite->hosting->capacity ? $designWebsite->hosting->capacity . ' GB' : '-' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3"><strong>Ghi chú</strong></h5>
                    <p>{!! nl2br(e($designWebsite->note)) !!}</p>

                    <div class="text-center mt-4">
                        <a href="{{ route('design-websites.edit', $designWebsite->id) }}" class="btn btn-success mr-2">Chỉnh sửa</a>
                        <a href="{{ route('design-websites.index') }}" class="btn btn-secondary">Quay lại</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop
