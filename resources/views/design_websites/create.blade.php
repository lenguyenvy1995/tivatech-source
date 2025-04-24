@extends('adminlte::page')

@section('title', 'Thêm Website')

@section('content_header')
    <h1>Thêm Website mới</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <strong>Thông tin Website</strong>
                </div>
                <div class="card-body">

                    <form action="{{ route('design-websites.store') }}" method="POST">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h5 class="mb-3"><strong>Thông tin chung</strong></h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Khách hàng</label>
                                    <input type="text" name="username_customer" class="form-control" value="TIVATECH" required>
                                    @error('username_customer')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Ngày đăng ký</label>
                                    <input type="date" name="registration_date" class="form-control" value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" required>
                                    @error('registration_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control">
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Nhân viên sales</label>
                                    <select name="sales_staff" class="form-control">
                                        <option value="">-- Chọn nhân viên --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->name }}">{{ $user->fullname ?? $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('sales_staff')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên miền</label>
                                    <input type="text" name="domain_name" class="form-control" placeholder="Nhập tên miền..." required>
                                    @error('domain_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Ngày hết hạn</label>
                                    <input type="date" name="expiration_date" class="form-control" value="{{ \Carbon\Carbon::today()->addYear()->format('Y-m-d') }}" required>
                                    @error('expiration_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="customer_phone" class="form-control">
                                    @error('customer_phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Giá</label>
                                    <input type="text" name="prices" class="form-control">
                                    @error('prices')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">

                            <div class="col-md-6">
                                <h5 class="mb-3"><strong>Thông tin đăng nhập domain</strong></h5>

                                <div class="form-group">
                                    <label>Link đăng nhập domain</label>
                                    <input type="text" name="domain_login_link" class="form-control" value="https://id.matbao.net/">
                                    @error('domain_login_link')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Tài khoản domain</label>
                                    <input type="text" name="domain_account" class="form-control" value="MB15666557">
                                    @error('domain_account')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Mật khẩu domain</label>
                                    <input type="text" name="domain_password" class="form-control" value="@Tivatech2020">
                                    @error('domain_password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3"><strong>Thông tin Hosting</strong></h5>

                                <div class="form-group">
                                    <label>NCC Hosting</label>
                                    <input type="text" name="hosting_supplier" class="form-control" value="TIVATECH">
                                    @error('hosting_supplier')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Link đăng nhập hosting</label>
                                    <input type="text" name="hosting_login_link" class="form-control">
                                    @error('hosting_login_link')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Tài khoản hosting</label>
                                    <input type="text" name="hosting_account" class="form-control">
                                    @error('hosting_account')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Mật khẩu hosting</label>
                                    <input type="text" name="hosting_password" class="form-control">
                                    @error('hosting_password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Dung lượng</label>
                                    <input type="number" name="hosting_capacity" class="form-control" min="0" step="1">
                                    @error('hosting_capacity')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-3"><strong>Ghi chú</strong></h5>
                                <div class="form-group">
                                    <textarea name="note" class="form-control" rows="3" placeholder="Nhập ghi chú (nếu có)..."></textarea>
                                    @error('note')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Lưu</button>
                            <a href="{{ route('design-websites.index') }}" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
@endpush

@section('js')
<script>
    $(document).ready(function () {
        $('select[name="sales_staff"]').select2({
            placeholder: "Chọn nhân viên...",
            width: '100%'
        });
    });
</script>
@stop