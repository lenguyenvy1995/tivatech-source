@extends('adminlte::page')

@section('title', 'Thêm Chiến Dịch')

@section('content_header')
    <h1>Thêm Chiến Dịch</h1>
@stop

@section('content')
    <div class="d-flex justify-content-center">
        <div class="card" style="width:1200px">

            <div class="card-body">
                <form id="setupForm" action="{{ route('campaigns.store') }}" method="POST">
                    @csrf
                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <p class="text-center">{{ $error }}</p>
                        @endforeach
                    @endif
                    <div class="form-group">
                        <label>Website:</label>
                        <select class="form-control select2" name="domain">
                            <option value=""> Chọn domain</option>
                            @foreach ($domains as $domain)
                                <option value="{{ $domain->name }}"
                                    {{ old('website_id') == $domain->id ? 'selected' : '' }}>
                                    {{ $domain->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('domain')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Vị Trí:</label>
                        <input type="text" class="form-control" name="top_position"
                            value="{{ old('top_position', '1-4') }}">
                        @error('top_position')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Thiết bị:</label>
                        <input type="text" class="form-control" name="device"
                            value="{{ old('device', 'tất cả thiết bị') }}">
                        @error('device')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Khu Vực:</label>
                        <input type="text" class="form-control" name="region"
                            value="{{ old('region', 'Hồ Chí Minh') }}">
                        @error('region')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Loại Đối Sánh:</label>
                        <select class="form-control" name="keyword_type">
                            <option value="0" {{ old('keyword_type', 1) == 0 ? 'selected' : '' }}>Đối Sánh Chính Xác
                            </option>
                            <option value="1" {{ old('keyword_type', 1) == 1 ? 'selected' : '' }}>Đối Sánh Cụm Từ
                            </option>
                        </select>
                        @error('keyword_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Hình Thức:</label>
                        <select class="form-control" name="typecamp_id">
                            <option value="1" {{ old('typecamp_id') == 1 ? 'selected' : '' }}>Trọn Gói</option>
                            <option value="2" {{ old('typecamp_id') == 2 ? 'selected' : '' }}>Ngân Sách</option>
                        </select>
                        @error('typecamp_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Thời Hạn Hiển thị:</label>
                        <input type="text" class="form-control" name="display"
                            value="{{ old('display', '6:00 đến 22:00') }}">
                        @error('display')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Ngân Sách:</label>
                        <input type="text" class="form-control" name="budgetmonth" id="budgetmonth"
                            value="{{ old('budgetmonth', '') }}">
                        @error('budgetmonth')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group " id="promotion-group">
                        <label>Giá Giảm:</label>
                        <input type="text" class="form-control" name="promotion" id="promotion" value="">
                        @error('promotion')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Input cho Percent -->
                    <div class="form-group" id="percent-group">
                        <label>Phí Quản Lý (%):</label>
                        <input type="number" class="form-control" name="percent" id="percent"
                            value="{{ old('percent', '15') }}">
                        @error('percent')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Thanh Toán:</label>
                        <input type="text" class="form-control" name="payment" id="payment"
                            value="{{ old('payment', '') }}">
                        @error('payment')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input class="custom-control-input custom-control-input-danger custom-control-input-outline"
                            type="checkbox" name="vat" id="vat" value="1">
                        <label for="vat" class="custom-control-label text-danger">Thuế GTGT (VAT)</label>
                    </div>
                    <div class="form-group">
                        <label>Bắt Đầu:</label>
                        <input type="text" class="form-control" id="start" name="start"
                            value="{{ now()->startOfDay()->format('d-m-Y H:i') }}">
                        @error('start')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Số Ngày:</label>
                        <input type="text" class="form-control" id="days" name="days" placeholder="Nhập số ngày"
                            value="{{ old('days', 15) }}">
                        @error('days')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Kết Thúc:</label>
                        <input type="text" class="form-control" id="end" name="end" readonly>
                        @error('end')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Từ Khóa:</label>
                        <textarea class="form-control" rows="5" name="keywords">{{ old('keywords') }}</textarea>
                        @error('keywords')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Ghi Chú setup:</label>
                        <textarea class="form-control" rows="5" name="notes">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Lưu Campaign</button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Khởi tạo Select2
            $('.select2').select2({
                placeholder: '-- Chọn Domain (Tên Miền) --',
                allowClear: false
            });

            // Khởi tạo Flatpickr cho trường "start" và "end"
            const startDatePicker = flatpickr("#start", {
                enableTime: true,
                dateFormat: "d-m-Y H:i",
                time_24hr: true,
                defaultHour: 0,
                defaultMinute: 0,
            });

            const endDatePicker = flatpickr("#end", {
                enableTime: true,
                dateFormat: "d-m-Y H:i",
                time_24hr: true,
                clickOpens: false, // Không cho phép mở
            });
            // Hàm làm tròn số
            function roundDays(value) {
                const integerPart = Math.floor(value); // Phần nguyên
                const decimalPart = value - integerPart; // Phần thập phân

                if (decimalPart < 0.5) {
                    return integerPart; // Làm tròn xuống
                } else {
                    return integerPart + 0.5; // Làm tròn lên thành x.5
                }
            }

            function calculateEndDate() {
                let days = parseFloat($('#days').val()); // Lấy số ngày từ input
                days = roundDays(days)
                if (!isNaN(days) && days > 0) {
                    const startDate = startDatePicker.selectedDates[0]; // Ngày bắt đầu
                    if (startDate) {
                        const endDate = new Date(startDate);
                        // Tách phần nguyên và phần thập phân của số ngày
                        const integerDays = Math.floor(days); // Phần nguyên
                        const fractionalDays = days - integerDays; // Phần thập phân                    
                        if (startDate.getHours() == 0) {
                            if (days % 1 == 0) {
                                endDate.setDate(startDate.getDate() + integerDays -
                                1); // Trừ 1 để tính ngày bắt đầu

                            } else {
                                endDate.setDate(startDate.getDate() + integerDays); 
                                // Thêm giờ nếu có phần thập phân
                                const fractionalHours = (days - Math.floor(days)) * 24;
                                endDate.setHours(startDate.getHours() + fractionalHours);
                            }

                        } else {
                            if (days % 1 == 0) {
                                endDate.setDate(startDate.getDate() + integerDays); 

                            } else {
                                endDate.setDate(startDate.getDate() + integerDays-1); 
                                // Thêm giờ nếu có phần thập phân
                                const fractionalHours = (days - Math.floor(days)) * 24;
                                endDate.setHours(startDate.getHours() + fractionalHours);
                            }

                        }
                        // Cập nhật trường "end"
                        endDatePicker.setDate(endDate, true);
                    }

                } else {
                    // Xóa giá trị ngày kết thúc nếu không hợp lệ
                    endDatePicker.clear();
                }
            }
            // Gọi hàm khi giá trị "days" hoặc "start" thay đổi
            $('#days').on('input', calculateEndDate);
            $('#start').on('change', calculateEndDate);



            // Khi submit form, loại bỏ dấu phẩy trước khi gửi dữ liệu
            $('#setupForm').on('submit', function() {
                $('#budgetmonth, #payment, #promotion').each(function() {
                    $(this).val(removeFormatting($(this).val()));
                });
            });
            // Gán sự kiện input cho các trường số
            $('#budgetmonth, #payment, #promotion').on('input', function() {
                $(this).val(formatNumber($(this).val()));
            });
            // Hàm format số theo định dạng có dấu phẩy
            function formatNumber(value) {
                return value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            // Hàm loại bỏ dấu phẩy
            function removeFormatting(value) {
                return value.replace(/,/g, '');
            }

            // Nút lưu
            $('#saveButton').on('click', function() {
                $('#setupForm').submit();
            });
            // Thay đổi hiển thị dựa trên loại "Hình Thức"

            function toggleFieldsBasedOnType() {
                const selectedType = $('select[name="typecamp_id"]').val();
                if (selectedType === '1') {
                    // Nếu chọn "Trọn Gói"
                    $('#promotion-group').show();
                    $('#percent-group').hide();
                } else if (selectedType === '2') {
                    // Nếu chọn "Ngân Sách"
                    $('#promotion-group').hide();
                    $('#percent-group').show();
                }
            }

            // Gọi hàm khi thay đổi "Hình Thức"
            $('select[name="typecamp_id"]').on('change', toggleFieldsBasedOnType);

            // Gọi lần đầu khi trang tải
            toggleFieldsBasedOnType();
            calculateEndDate();
        });
    </script>
@stop
