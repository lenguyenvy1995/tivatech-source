@extends('adminlte::page')

@section('title', 'Chỉnh sửa Website')

@section('content_header')
    <h1>Chỉnh sửa Website</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <strong>Cập nhật thông tin Website</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('design-websites.update', $designWebsite->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3"><strong>Thông tin chung</strong></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Khách hàng</label>
                                    <input type="text" name="username_customer" class="form-control" value="{{ old('username_customer', $designWebsite->username_customer) }}" required>
                                    @error('username_customer')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Ngày đăng ký</label>
                                    <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date', $designWebsite->registration_date->format('Y-m-d')) }}" required>
                                    @error('registration_date')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $designWebsite->email) }}">
                                    @error('email')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Nhân viên sales</label>
                                    <select name="user_id" class="form-control select2">
                                        <option value="">-- Chọn nhân viên --</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', $designWebsite->user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->fullname ?? $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên miền</label>
                                    <input type="text" name="domain_name" class="form-control" value="{{ old('domain_name', $designWebsite->domain->domain ?? '') }}" required>
                                    @error('domain_name')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Ngày hết hạn</label>
                                    <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date', $designWebsite->expiration_date->format('Y-m-d')) }}" required>
                                    @error('expiration_date')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $designWebsite->customer_phone) }}">
                                    @error('customer_phone')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Giá</label>
                                    <input type="text" name="prices" class="form-control" value="{{ old('prices', $designWebsite->prices) }}">
                                    @error('prices')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3"><strong>Thông tin đăng nhập domain</strong></h5>
                                <div class="form-group">
                                    <label>Link đăng nhập</label>
                                    <input type="text" name="domain_login_link" class="form-control" value="{{ old('domain_login_link', $designWebsite->domain->login_link ?? '') }}">
                                    @error('domain_login_link')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Tài khoản</label>
                                    <input type="text" name="domain_account" class="form-control" value="{{ old('domain_account', $designWebsite->domain->account ?? '') }}">
                                    @error('domain_account')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Mật khẩu</label>
                                    <input type="text" name="domain_password" class="form-control" value="{{ old('domain_password', $designWebsite->domain->password ?? '') }}">
                                    @error('domain_password')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3"><strong>Thông tin Hosting</strong></h5>
                                <div class="form-group">
                                    <label>Nhà cung cấp</label>
                                    <input type="text" name="hosting_supplier" class="form-control" value="{{ old('hosting_supplier', $designWebsite->hosting->supplier ?? '') }}">
                                    @error('hosting_supplier')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Link đăng nhập</label>
                                    <input type="text" name="hosting_login_link" class="form-control" value="{{ old('hosting_login_link', $designWebsite->hosting->login_link ?? '') }}">
                                    @error('hosting_login_link')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Tài khoản</label>
                                    <input type="text" name="hosting_account" class="form-control" value="{{ old('hosting_account', $designWebsite->hosting->account ?? '') }}">
                                    @error('hosting_account')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Mật khẩu</label>
                                    <input type="text" name="hosting_password" class="form-control" value="{{ old('hosting_password', $designWebsite->hosting->password ?? '') }}">
                                    @error('hosting_password')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Dung lượng</label>
                                    <input type="number" name="hosting_capacity" class="form-control" value="{{ old('hosting_capacity', $designWebsite->hosting->capacity ?? '') }}">
                                    @error('hosting_capacity')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3">{{ old('note', $designWebsite->note) }}</textarea>
                            @error('note')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                            <a href="{{ route('design-websites.index') }}" class="btn btn-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: '-- Chọn nhân viên --',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
@stop