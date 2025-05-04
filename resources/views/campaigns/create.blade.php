@extends('adminlte::page')

@section('title', 'Thêm Chiến Dịch')

@section('content_header')
    <h1>Thêm Mới Chiến Dịch</h1>
@stop

@section('content')
    <div class="d-flex justify-content-center">
        <div class="card" style="width:1200px">

            <div class="card-body">
                <form id="setupForm" method="POST" action="{{ route('campaigns.store') }}">
                    @csrf
                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <p class="text-center">{{ $error }}</p>
                        @endforeach
                    @endif
                    <div class="form-group">
                        <label>Chọn Website:</label>
                        <select class="form-control" name="domain">
                            @foreach ($domains as $domain)
                                <option value="" disabled selected>Chọn website...</option>
                                <option value="{{ $domain->name }}" {{ old('domain') == $domain->name ? 'selected' : '' }}>
                                    {{ $domain->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Vị Trí:</label>
                        <input type="text" class="form-control" name="top_position"
                            value="{{ old('top_position') ?? '1-4' }}">
                        @error('top_position')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Thiết bị:</label>
                        <input type="text" class="form-control" name="device"
                            value="{{ old('device') ?? 'Tất cả thiết bị' }}">
                        @error('device')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Khu Vực:</label>
                        <input type="text" class="form-control" name="region" value="{{ old('region') ?? 'TPHCM' }}">
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
                        <input type="text" class="form-control" name="display" value="{{ old('display') ?? '24/24' }}">
                        @error('display')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Ngân Sách:</label>
                        <input type="text" class="form-control" name="budgetmonth" id="budgetmonth"
                            value="{{ old('budgetmonth') }}">
                        @error('budgetmonth')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group " id="promotion-group">
                        <label>Giá Giảm:</label>
                        <input type="text" class="form-control" name="promotion" id="promotion"
                            value="{{ old('promotion') }}">
                        @error('promotion')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Input cho Percent -->
                    <div class="form-group" id="percent-group">
                        <label>Phí Quản Lý (%):</label>
                        <input type="number" class="form-control" name="percent" id="percent"
                            value="{{ old('percent') }}">
                        @error('percent')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Thanh Toán:</label>
                        <input type="text" class="form-control" name="payment" id="payment"
                            value="{{ old('payment') }}">
                        @error('payment')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Thuế GTGT (VAT):</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="vat" id="vat0" value="0"
                                {{ old('vat') == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="vat0">Không xuất</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="vat" id="vat1" value="1"
                                {{ old('vat') == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="vat1">Xuất VAT</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Bắt Đầu:</label>
                        <input type="text" class="form-control" id="start" name="start"
                            value="{{ old('start', date('d-m-Y H:i')) }}">
                        @error('start')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Radio chọn giờ bắt đầu -->
                    <div class="form-group">
                        <label>Giờ bắt đầu:</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="startHour" id="startHour0"
                                value="0" {{ old('startHour', '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="startHour0">00:00</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="startHour" id="startHour12"
                                value="12" {{ old('startHour') == '12' ? 'checked' : '' }}>
                            <label class="form-check-label" for="startHour12">12:00</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Số Ngày:</label>
                        <input type="text" class="form-control" id="days" name="days"
                            placeholder="Nhập số ngày" value="{{ old('days') ?? '30' }}">
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
                    <div id="timeRangeDisplay" class="mt-2 font-weight-bold text-danger"></div>

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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
@stop

@section('js')
    <!-- jQuery CDN nếu chưa có -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Select2 cho phần chọn domain
            $('select[name="domain"]').select2({
                placeholder: "Chọn website...",
                allowClear: true
            });

            // Khởi tạo flatpickr cho các trường ngày tháng - KHÔNG set time mặc định
            flatpickr("#start", {
                dateFormat: "d-m-Y",
                allowInput: true,
            });
            recalculateEndDate()
            // === Thêm xử lý recalculateEndDate ===
            function recalculateEndDate() {
                const startDateStr = $('#start').val();
                const days = parseFloat($('#days').val());
                const selectedHour = parseInt($('input[name="startHour"]:checked').val());

                if (startDateStr && !isNaN(days)) {
                    // Tạo moment gốc với giờ từ radio
                    const startMoment = moment(startDateStr, 'DD-MM-YYYY').hour(selectedHour).minute(0).second(0);

                    // Tính end theo timestamp
                    const durationMs = days * 24 * 60 * 60 * 1000; // days to milliseconds
                    const endTimestamp = startMoment.valueOf() + durationMs;
                    // Luôn trừ 1 phút cho tất cả trường hợp
                    const endMoment = moment(endTimestamp).subtract(1, 'minute');

                    $('#end').val(endMoment.format('DD-MM-YYYY HH:mm'));
                    $('#timeRangeDisplay').text(
                        `Thời gian từ ${startMoment.format('DD-MM-YYYY HH:mm')} đến ${endMoment.format('DD-MM-YYYY HH:mm')}`
                    );
                }
            }

            $('#start').on('change', recalculateEndDate);
            $('#days').on('input change', recalculateEndDate);
            // Trigger lại khi đổi radio giờ
            $('input[name="startHour"]').on('change', recalculateEndDate);
            // Xử lý submit form bằng AJAX
            $('#setupForm').on('submit', function(e) {
                e.preventDefault();
                // Remove commas from number fields before serializing
                ['budgetmonth', 'promotion', 'payment'].forEach(function(field) {
                    const input = $('input[name="' + field + '"]');
                    input.val(input.val().replace(/,/g, '')); // remove commas before submit
                });
                // Thêm giờ vào trường start trước khi submit
                const rawStartDate = $('#start').val().substring(0, 10); // chỉ lấy phần ngày d-m-Y
                const startHour = $('input[name="startHour"]:checked').val();
                const fullStart = rawStartDate + ' ' + (startHour === '12' ? '12:00' : '00:00');
                $('#start').val(fullStart);
                const formData = $(this).serialize();

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        toastr.success('Tạo chiến dịch thành công!');
                        // Chuyển hướng về trang danh sách chiến dịch
                        window.location.href = "/campaigns/setups";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $('.text-danger').remove(); // Xóa thông báo lỗi cũ

                            $.each(errors, function(field, messages) {
                                const input = $('[name="' + field + '"]');
                                if (input.length) {
                                    input.addClass('is-invalid');
                                    input.closest('.form-group').append(
                                        '<span class="text-danger">' + messages[0] +
                                        '</span>');
                                }
                            });
                        } else {
                            toastr.error('Có lỗi xảy ra. Vui lòng kiểm tra lại.');
                        }
                    }
                });
            });
            // ===== Thêm xử lý định dạng số cho các trường tiền tệ =====
            function formatNumberInput(selector) {
                $(selector).on('input', function() {
                    let value = $(this).val().replace(/,/g, '');
                    if (!isNaN(value) && value !== '') {
                        value = parseFloat(value).toLocaleString('en-US');
                        $(this).val(value);
                    }
                });
            }

            // Gán định dạng cho các input liên quan đến tiền tệ
            formatNumberInput('#budgetmonth');
            formatNumberInput('#promotion');
            formatNumberInput('#payment');
        });
    </script>
@stop
