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
                    <option value="{{ $user->id }}" data-salary="{{ $user->base_salary }}">
                        {{ $user->fullname }}
                    </option>
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
            <label for="work_days">Ngày công làm: <a href="" id="attendance" target="blankw">Chi tiết</a></label>
            <input type="text" id="work_days" name="work_days" class="form-control" value="0">
        </div>
        <div class="form-group">
            <label for="actual_work_days">Thực làm:</label>
            <input type="text" id="actual_work_days" name="actual_work_days" class="form-control" value="0" required>
        </div>
        <div class="form-group">
            <label for="bhxh">BHXH</label>
            <input type="text" id="bhxh" name="bhxh" class="form-control"
                placeholder="Nhập bhxh" readonly>
        </div>
        <div class="form-group">
            <label for="phone_allowance">Phụ cấp điện thoại:</label>
            <input type="text" id="phone_allowance" name="phone_allowance"
                class="form-control" placeholder="Nhập phụ cấp điện thoại" readonly>
        </div>
        <div class="form-group">
            <label for="attendance_bonus">Chuyên cần:</label>
            <input type="text" id="attendance_bonus" name="attendance_bonus"
                class="form-control" placeholder="Nhập thưởng chuyên cần">
        </div>
        <div class="form-group">
            <label for="bonus">Thưởng:</label>
            <input type="text" id="bonus" name="bonus" class="form-control"
                placeholder="Nhập thưởng">
        </div>
    </div>
    <div class="card-footer">
        <div class="form-group">
            <label for="real_salary">Lương thực tế</label>
            <input type="text" id="actual_salary" name="actual_salary" class="form-control"
                value="0" readonly>

            <input type="hidden" id="t_total_salary" name="t_total_salary" class="form-control"
                value="">
        </div>
    </div>
</div>