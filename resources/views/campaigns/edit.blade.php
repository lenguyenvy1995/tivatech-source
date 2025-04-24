@extends('adminlte::page')

@section('title', 'Sửa Chiến Dịch')

@section('content_header')
    <h1>Sửa Chiến Dịch</h1>
@stop

@section('content')
    <div class="d-flex justify-content-center">
        <div class="card" style="width:1200px">
        
            <div class="card-body">
                <form id="setupForm" action="{{ route('campaigns.update', $campaign->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Domain</label>
                        <input type="text" class="form-control" readonly name="domain"
                            value="{{ $campaign->website->name }}">
                    </div>

                    <div class="form-group">
                        <label>Vị Trí:</label>
                        <input type="text" class="form-control" name="top_position"
                            value="{{ old('top_position', $campaign->top_position) }}">
                        @error('top_position')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Thiết bị:</label>
                        <input type="text" class="form-control" name="device"
                            value="{{ old('device', $campaign->device) }}">
                        @error('device')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Khu Vực:</label>
                        <input type="text" class="form-control" name="region"
                            value="{{ old('region', $campaign->region) }}">
                        @error('region')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Loại Đối Sánh:</label>
                        <select class="form-control" name="keyword_type">
                            <option value="0"
                                {{ old('keyword_type', $campaign->keyword_type) == 0 ? 'selected' : '' }}>
                                Đối Sánh Chính Xác
                            </option>
                            <option value="1"
                                {{ old('keyword_type', $campaign->keyword_type) == 1 ? 'selected' : '' }}>
                                Đối Sánh Cụm Từ
                            </option>
                        </select>
                        @error('keyword_type')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Hình Thức:</label>
                        <select class="form-control" name="typecamp_id">
                            <option value="1" {{ old('typecamp_id', $campaign->typecamp_id) == 1 ? 'selected' : '' }}>
                                Trọn
                                Gói</option>
                            <option value="2" {{ old('typecamp_id', $campaign->typecamp_id) == 2 ? 'selected' : '' }}>
                                Ngân
                                Sách</option>
                        </select>
                        @error('typecamp_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Thời Hạn Hiển thị:</label>
                        <input type="text" class="form-control" name="display"
                            value="{{ old('display', $campaign->display) }}">
                        @error('display')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" id="promotion-group">
                        <label>Giá Giảm:</label>
                        <input type="text" class="form-control" name="promotion" id="promotion"
                            value="{{ old('promotion', $campaign->promotion) }}">
                        @error('promotion')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" id="percent-group">
                        <label>Phí Quản Lý (%):</label>
                        <input type="number" class="form-control" name="percent"
                            value="{{ old('percent', $campaign->percent) }}">
                        @error('percent')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Ngân Sách:</label>
                        <input type="text" class="form-control" name="budgetmonth" id="budgetmonth"
                            value="{{ old('budgetmonth', $campaign->budgetmonth) }}">
                        @error('budgetmonth')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Thanh Toán:</label>
                        <input type="text" class="form-control" name="payment" id="payment"
                            value="{{ old('payment', $campaign->payment) }}">
                        @error('payment')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input class="custom-control-input custom-control-input-danger custom-control-input-outline"
                            type="checkbox" name="vat" id="vat" value="1" {{ old('vat', $campaign->vat) ? 'checked' : '' }}>
                        <label for="vat" class="custom-control-label text-danger">Thuế GTGT (VAT)</label>
                    </div>
                    <div class="form-group">
                        <label>Bắt Đầu:</label>
                        <input type="text" class="form-control" id="start" name="start"
                            value="{{ old('start', \Carbon\Carbon::parse($campaign->start)->format('d-m-Y H:i')) }}">
                        @error('start')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Số Ngày:</label>
                        <input type="text" class="form-control" id="days" name="days"
                            value="">
                        @error('days')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Kết Thúc:</label>
                        <input type="text" class="form-control" id="end" name="end" readonly
                            value="{{ old('end', \Carbon\Carbon::parse($campaign->end)->format('d-m-Y H:i')) }}">
                        @error('end')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Từ Khóa:</label>
                        <textarea class="form-control" rows="5" name="keywords">{{ old('keywords', $campaign->keywords) }}</textarea>
                        @error('keywords')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Ghi Chú setup:</label>
                        <textarea class="form-control" rows="5" name="notes">{{ old('notes', $campaign->notes) }}</textarea>
                        @error('notes')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Cập Nhật Campaign</button>
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

           // Hàm tính ngày kết thúc
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
            // Hàm tính toán số ngày giữa start và end
    
            function calculateDays() {
                const startDate = startDatePicker.selectedDates[0]; // Ngày bắt đầu
                const endDate = endDatePicker.selectedDates[0]; // Ngày kết thúc

                if (startDate && endDate) {
                    let diffDays = Math.abs((endDate - startDate) / (1000 * 60 * 60 * 24)); // Tính số ngày chênh lệch
                    
                    // Lấy giờ của ngày bắt đầu và ngày kết thúc
                    const startHours = startDate.getHours();
                    const startMinutes = startDate.getMinutes();
                    const endHours = endDate.getHours();
                    const endMinutes = endDate.getMinutes();

                    // Chuyển đổi giờ phút thành chuỗi 'HH:MM' để so sánh
                    const startTime = `${String(startHours).padStart(2, '0')}:${String(startMinutes).padStart(2, '0')}`;
                    const endTime = `${String(endHours).padStart(2, '0')}:${String(endMinutes).padStart(2, '0')}`;

                    // Áp dụng điều kiện tính số ngày dựa vào giờ nhập vào
                    if (startTime === '00:00' && endTime === '00:00') {
                        diffDays += 1; // Từ 00:00 đến 00:00 là trọn ngày
                    } else if (startTime === '12:00' && endTime === '12:00') {
                        // Giữ nguyên số ngày đã tính
                    } else if (startTime === '00:00' && endTime === '12:00') {
                        diffDays -= 1; // Vì từ 00:00 đến 12:00 không trọn 1 ngày
                    } else if (startTime === '12:00' && endTime === '00:00') {
                        diffDays += 1; // Vì từ 12:00 đến 00:00 vượt quá 1 ngày
                    }

                    $('#days').val(diffDays.toFixed(0)); // Hiển thị số ngày làm tròn
                }
            }
            // Gọi hàm tính toán số ngày khi trang lần đầu tải
            calculateDays();
            // Gán sự kiện input cho các trường số
            $('#budgetmonth, #payment, #promotion').on('input', function() {
                $(this).val(formatNumber($(this).val()));
            });
            // Hàm áp dụng định dạng số cho các trường khi trang tải lần đầu
            function applyNumberFormatOnLoad() {
                $('#budgetmonth, #payment, #promotion').each(function() {
                    const value = $(this).val(); // Lấy giá trị hiện tại
                    if (value) {
                        $(this).val(formatNumber(value)); // Áp dụng định dạng số
                    }
                });
            }

            // Gọi hàm để áp dụng định dạng khi tải trang
            applyNumberFormatOnLoad();
            // Khi submit form, loại bỏ dấu phẩy trước khi gửi dữ liệu
            $('#setupForm').on('submit', function() {
                $('#budgetmonth, #payment, #promotion').each(function() {
                    $(this).val(removeFormatting($(this).val()));
                });
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
        });
    </script>
@stop
