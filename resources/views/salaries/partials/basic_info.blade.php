<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">LƯƠNG CƠ BẢN</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="user_id">Chọn nhân viên:</label>
            <select id="user_id" name="user_id" class="form-control select2" required>
                <option value="">Chọn nhân viên</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" data-salary="{{ $user->base_salary }}">{{ $user->fullname }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="month">Chọn tháng:</label>
            <input type="text" id="month" name="month" class="form-control" value="{{ now()->format('Y-m') }}" required>
        </div>
        <div class="form-group">
            <label for="base_salary">Lương cơ bản:</label>
            <input type="text" id="base_salary" name="base_salary" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label for="work_days">Ngày công làm:</label>
            <input type="number" id="work_days" name="work_days" class="form-control" value="0">
        </div>
        <div class="form-group">
            <label for="actual_work_days">Thực làm:</label>
            <input type="number" id="actual_work_days" name="actual_work_days" class="form-control" value="0" required>
        </div>
        <div class="form-group">
            <label for="absent_days">Ngày nghỉ:</label>
            <input type="number" id="absent_days" name="absent_days" class="form-control" value="0" readonly>
        </div>
        <div class="form-group">
            <label for="bhxh">BHXH:</label>
            <input type="number" id="bhxh" name="bhxh" class="form-control" readonly>
        </div>
        <div class="form-group">
            <label for="phone_allowance">Phụ cấp điện thoại:</label>
            <input type="number" id="phone_allowance" name="phone_allowance" class="form-control">
        </div>
        <div class="form-group">
            <label for="attendance_bonus">Chuyên cần:</label>
            <input type="number" id="attendance_bonus" name="attendance_bonus" class="form-control">
        </div>
        <div class="form-group">
            <label for="bonus">Thưởng:</label>
            <input type="number" id="bonus" name="bonus" class="form-control">
        </div>
    </div>
</div>
