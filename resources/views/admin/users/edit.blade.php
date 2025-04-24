@extends('adminlte::page')

@section('title', 'Sửa Người Dùng')

@section('content_header')
    <h1>Sửa Người Dùng: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông Tin Người Dùng</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Username</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="fullname">Họ Tên</label>
                    <input type="text" name="fullname" id="fullname" class="form-control @error('fullname') is-invalid @enderror" value="{{ old('fullname')??$user->fullname }}" required>
                    @error('fullname')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="ngan_hang">Ngân hàng</label>
                    <input type="text" name="ngan_hang" id="ngan_hang" class="form-control @error('ngan_hang') is-invalid @enderror" value="{{ old('ngan_hang')??$user->ngan_hang }}" >
                    @error('ngan_hang')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group form-inline">
                    <label for="" class="mr-3">Hình thức lương</label>
                
                    <!-- Radio: Cố định -->
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input custom-control-input-danger"
                               type="radio" id="fixed_salary" name="salary"
                               value="0" {{ $user->salary == 0 ? 'checked' : '' }}>
                        <label for="fixed_salary" class="custom-control-label">Cố định</label>
                    </div>
                
                    <!-- Radio: KPI -->
                    <div class="custom-control custom-radio ml-2">
                        <input class="custom-control-input custom-control-input-danger custom-control-input-outline"
                               type="radio" id="kpi_salary" name="salary"
                               value="1" {{ $user->salary == 1 ? 'checked' : '' }}>
                        <label for="kpi_salary" class="custom-control-label">KPI</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="base_salary">Lương cơ bản</label>
                    <input type="text" name="base_salary" id="base_salary" class="form-control @error('base_salary') is-invalid @enderror" value="{{ old('base_salary')??$user->base_salary }}" >
                    @error('base_salary')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="bhxh">Bảo hiểm xã hội</label>
                    <input type="text" name="bhxh" id="bhxh" class="form-control @error('bhxh') is-invalid @enderror" value="{{ old('bhxh')??$user->bhxh }}" >
                    @error('bhxh')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="phone_allowance">Phụ cấp điện thoại</label>
                    <input type="text" name="phone_allowance" id="phone_allowance" class="form-control @error('phone_allowance') is-invalid @enderror" value="{{ old('phone_allowance')??$user->phone_allowance }}" >
                    @error('phone_allowance')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="attendance_bonus">Chuyên cần</label>
                    <input type="text" name="attendance_bonus" id="attendance_bonus" class="form-control @error('attendance_bonus') is-invalid @enderror" value="{{ old('attendance_bonus')??$user->attendance_bonus }}" >
                    @error('attendance_bonus')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="zalo_user_id">zalo</label>
                    <input type="text" name="zalo_user_id" id="zalo_user_id" class="form-control @error('zalo_user_id') is-invalid @enderror" value="{{ old('zalo_user_id')??$user->zalo_user_id }}" >
                    @error('zalo_user_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                {{-- <div class="form-group">
                    <label for="status"></label>
                    <input type="checkbox" name="status" id="status" class="form-control @error('fullname') is-invalid @enderror" value="{{ old('status')??$user->status }}" required>
                    @error('status')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div> --}}
                <div class="form-group">
                    <label for="password">Mật Khẩu</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Nếu không muốn thay đổi mật khẩu, hãy để trống trường này.</small>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Xác Nhận Mật Khẩu</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <label for="roles">Vai Trò</label>
                    <select name="roles[]" id="roles" class="form-control @error('roles') is-invalid @enderror" multiple required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ in_array($role->name, $userRoles) ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('roles')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Cập Nhật Người Dùng</button>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#roles').select2({
                placeholder: "Chọn vai trò",
                allowClear: true
            });
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
@stop
