@extends('adminlte::page')

@section('title', isset($campaign) ? 'Chi tiết Chiến Dịch' : 'Thêm Chiến Dịch')

@section('content_header')
    <h1>{{ isset($campaign) ? 'Chi tiết Chiến Dịch' : 'Thêm Chiến Dịch' }}</h1>
@stop


@section('content')
    <div class="card">
        <div class="card-header">
            @isset($campaign)
                @if ($campaign->status_id == '5' || Auth::user()->hasRole('admin|manager|techads'))
                    @if (Auth::user()->hasRole('saler'))
                        <a id="renewButton" class="btn btn-primary" href="{{ route('campaigns.renew',$campaign->id) }}">Gia hạn</a>
                    @endif
                    <a class="btn btn-warning" href="{{ route('campaigns.edit',$campaign->id) }}">Sửa</a>
                @else
                    <a id="renewButton" class="btn btn-primary" href="{{ route('campaigns.renew',$campaign->id) }}">Gia hạn</a>
                @endif
                <button type="button" id="saveButton" class="btn btn-success" style="display: none;">Lưu
                    Campaign</button>
            @else
                <button type="button" id="saveButton" class="btn btn-success">Lưu
                    Campaign</button>
            @endisset
        </div>
        <div class="card-body">
            <form id="setupForm" action="{{ route('campaigns.store') }}" method="POST">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="POST">

                <div class="form-group">
                    <label>Website:</label>
                    @if (isset($campaign))
                        <select class="form-control" name="website_id" disabled>
                            <option value="{{ $campaign->website->id }}"> {{ $campaign->website->name }}
                            </option>
                        </select>
                        <input type="hidden" class="form-control" name="campaign_id" id="campaign_id"
                            value="{{ $campaign->id }}">
                    @else
                        <select class="form-control select2" name="domain">
                            @foreach ($domains as $domain)
                                <option value="{{ $domain->name }}"
                                    {{ old('website_id') == $domain->id ? 'selected' : '' }}>
                                    {{ $domain->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="form-group">
                    <label>Vị Trí:</label>
                    <input type="text" class="form-control" name="top_position"
                        value="{{ old('top_position', isset($campaign) ? $campaign->top_position : '1-2 1-3 1-4') }}"
                        {{ isset($campaign) ? 'readonly' : '' }}>
                </div>

                <div class="form-group">
                    <label>Khu Vực:</label>
                    <input type="text" class="form-control" name="region"
                        value="{{ old('region', isset($campaign) ? $campaign->region : 'Hồ Chí Minh') }}"
                        {{ isset($campaign) ? 'readonly' : '' }}>
                </div>


                <div class="form-group">
                    <label>Loại Đối Sánh:</label>
                    <select class="form-control" name="keyword_type" {{ isset($campaign) ? 'disabled' : '' }}>
                        <option value="0"
                            {{ old('keyword_type', isset($campaign) ? $campaign->keyword_type : 1) == 0 ? 'selected' : '' }}>
                            Đối Sánh Chính Xác</option>
                        <option value="1"
                            {{ old('keyword_type', isset($campaign) ? $campaign->keyword_type : 1) == 1 ? 'selected' : '' }}>
                            Đối Sánh Cụm Từ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Hình Thức:</label>
                    <select class="form-control" name="typecamp_id" {{ isset($campaign) ? 'disabled' : '' }}>
                        <option value="1"
                            {{ old('typecamp_id', isset($campaign) ? $campaign->typecamp_id : 1) == 1 ? 'selected' : '' }}>
                            Trọn Gói</option>
                        <option value="2"
                            {{ old('typecamp_id', isset($campaign) ? $campaign->typecamp_id : 1) == 2 ? 'selected' : '' }}>
                            Ngân Sách</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Thời Hạn Hiển thị:</label>
                    <input type="text" class="form-control" name="display"
                        value="{{ old('display', isset($campaign) ? $campaign->display : '6:00 đến 22:00') }}"
                        {{ isset($campaign) ? 'readonly' : '' }}>
                </div>

                <div class="form-group">
                    <label>Ngân Sách:</label>
                    <input type="text" class="form-control" name="budgetmonth" id="budgetmonth"
                        value="{{ old('budgetmonth', isset($campaign) ? number_format($campaign->budgetmonth) : '0') }}"
                        {{ isset($campaign) ? 'readonly' : '' }}>
                </div>

                <div class="form-group">
                    <label>Thanh Toán:</label>
                    <input type="text" class="form-control" name="payment" id="payment"
                        value="{{ old('payment', isset($campaign) ? number_format($campaign->payment) : '0') }}"
                        {{ isset($campaign) ? 'readonly' : '' }}>
                </div>

                <div class="form-group " id="promotion-group">
                    <label>Giá Giảm:</label>
                    <input type="text" class="form-control" name="promotion" id="promotion"
                        value="{{ old('promotion', isset($campaign) ? number_format($campaign->promotion) : '') }}"
                        {{ isset($campaign) ? 'readonly' : '' }}>
                </div>
                <!-- Input cho Percent -->
                <div class="form-group" id="percent-group" style="display: none;">
                    <label>Phí Quản Lý (%):</label>
                    <input type="number" class="form-control" name="percent" id="percent"
                        value="{{ old('percent', isset($campaign) ? $campaign->percent : '') }}"
                        {{ isset($campaign) ? 'readonly' : '' }}>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Bắt Đầu:</label>
                            <input type="text" class="form-control" id="start" name="start"
                                value="{{ old('start', isset($campaign) ? Carbon\Carbon::parse($campaign->start)->format('d-m-Y H:i') : now()->startOfDay()->format('d-m-Y H:i')) }}"
                                {{ isset($campaign) ? 'readonly' : '' }}>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kết Thúc:</label>
                            <input type="text" class="form-control" id="end" name="end"
                                value="{{ old('end', isset($campaign) ? Carbon\Carbon::parse($campaign->end)->format('d-m-Y H:i') : '') }}"
                                readonly>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Số Ngày:</label>
                    @php
                        $start = \Carbon\Carbon::parse($campaign->start);
                        $end = \Carbon\Carbon::parse($campaign->end);
                        $days = $start->diffInDays($end);
                    
                        // Xác định giờ nhập
                        $startTime = $start->format('H:i');
                        $endTime = $end->format('H:i');
                    
                        if ($startTime == '00:00' && $endTime == '00:00') {
                            $days += 1; // Từ 00:00 đến 00:00 là trọn ngày
                        } elseif ($startTime == '12:00' && $endTime == '12:00') {
                            // Giữ nguyên, vì diffInDays() đã đúng
                        } elseif ($startTime == '00:00' && $endTime == '12:00') {
                            $days -= 1; // Vì 00:00 đến 12:00 không trọn 1 ngày
                        } elseif ($startTime == '12:00' && $endTime == '00:00') {
                            $days += 1; // Vì 12:00 đến 00:00 kéo dài hơn 1 ngày
                        }
                    @endphp
                
                <input type="number" class="form-control" id="days" placeholder="Nhập số ngày"
                    value="@isset($campaign){{ $days }}@else{{ old('days') ?? 15 }}@endisset" readonly>
                <div class="form-group">
                    <label>Thiết Bị:</label>
                    <input type="text" class="form-control" name="device"
                        value="{{ old('device', isset($campaign) ? $campaign->device : 'Trên tất cả các thiết bị') }}"
                        {{ isset($campaign) ? 'readonly' : '' }}>
                </div>

                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input custom-control-input-danger custom-control-input-outline"
                        type="checkbox" name="vat" id="vat"
                        {{ old('vat', isset($campaign) && $campaign->vat ? 'checked' : '') }} value="1">
                    <label for="vat" class="custom-control-label">Thuế GTGT (VAT)</label>
                </div>

                <div class="form-group">
                    <label>Từ Khóa:</label>
                    <textarea class="form-control" rows="5" name="keywords" {{ isset($campaign) ? 'readonly' : '' }}>{{ old('keywords', isset($campaign) ? $campaign->keywords : '') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Ghi Chú setup:</label>
                    <textarea class="form-control" rows="5" name="notes" {{ isset($campaign) ? 'readonly' : '' }}>{{ old('notes', isset($campaign) ? $campaign->notes : '') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Ghi Chú Chiến dịch:</label>
                    <textarea class="form-control" rows="5" name="note_campaign" {{ isset($campaign) ? 'readonly' : '' }}>{{ old('note_campaign') ?? '' }}</textarea>
                </div>
                @isset($campaign)
                    <div class="form-group">
                        <label>Ghi Chú Chiến dịch:</label>
                        <ul>
                            @foreach ($campaign->note as $note)
                                <li>{{ $note->user->fullname }} : {{ $note->note }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endisset
            </form>
        </div>
    </div>

@stop
@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @routes()
@stop
@section('js')
    <script>
        $('#setupForm').on('submit', function(e) {

            // Lấy giá trị từ Flatpickr
            const startInput = $('#start');
            const endInput = $('#end');

            // Chuyển đổi định dạng d-m-Y H:i -> Y-m-d H:i:s
            if (startInput.val()) {
                const startDate = flatpickr.parseDate(startInput.val(), "d-m-Y H:i");
                startInput.val(flatpickr.formatDate(startDate, "Y-m-d H:i:S"));
            }

            if (endInput.val()) {
                const endDate = flatpickr.parseDate(endInput.val(), "d-m-Y H:i");
                endInput.val(flatpickr.formatDate(endDate, "Y-m-d H:i:S"));
            }

            this.submit();
        });
        // Khởi tạo Select2
        $('.select2').select2({
            placeholder: '-- Chọn Domain ( Tên Miền ) --',
            allowClear: false
        });
        // Khởi tạo formatNumberInput cho các trường số
        formatNumberInput('budgetmonth');
        formatNumberInput('payment');
        formatNumberInput('promotion');
        // Flatpickr cho start
        const startDatePicker = flatpickr("#start", {
            enableTime: true,
            dateFormat: "d-m-Y H:i",
            altFormat: "Y-m-d H:i:S", // Định dạng dữ liệu gửi lên server
            time_24hr: true,
            defaultHour: 0, // Giờ mặc định là 00
            defaultMinute: 0, // Phút mặc định là 00
            allowInput: true,
            clickOpens: true,
            // onChange: function() {
            //     updateDays();
            //     updateEndDays();
            // }
        });

        // Flatpickr cho end
        const endDatePicker = flatpickr("#end", {
            enableTime: true,
            dateFormat: "d-m-Y H:i",
            altFormat: "Y-m-d H:i:S", // Định dạng dữ liệu gửi lên server
            time_24hr: true,
            allowInput: true,
            clickOpens: true,
        });

        // Cập nhật ngày kết thúc khi thay đổi số ngày
        $('#days').on('input', function() {
            updateEndDays();
        });
        $(document).ready(function() {
            // Gọi hàm kiểm tra khi trang được tải
            togglePromotionPercentFields();

            // Gắn sự kiện change cho select typecamp_id
            $('select[name="typecamp_id"]').on('change', function() {
                togglePromotionPercentFields();
            });
        });
        // Hàm tính toán số ngày giữa start và end
        // const daysInput = $('#days');
        // daysInput.on('input', function() {
        //     const startDate = startDatePicker.selectedDates[0];
        //     const days = parseInt($(this).val());
        //     if (startDate && !isNaN(days)) {
        //         const endDate = new Date(startDate);
        //         endDate.setDate(startDate.getDate() + days - 1);
        //         endDatePicker.setDate(endDate, true);
        //     }
        // });
        function updateDays() {
            const startDate = startDatePicker.selectedDates[0];
            const endDate = endDatePicker.selectedDates[0];
            if (startDate && endDate) {
                // Tính thời gian chênh lệch giữa endDate và startDate
                const diffTime = endDate - startDate;

                // Chuyển đổi sang số ngày và cộng thêm 1 để tính cả ngày bắt đầu
                const diffDays = diffTime / (1000 * 60 * 60 * 24) + 1;

                // Làm tròn đến 1 chữ số thập phân
                const roundedDays = Math.round(diffDays * 10) / 10;

                // Gán kết quả vào ô input ngày
                $('#days').val(roundedDays);
            } else {
                $('#days').val("");
            }
        }

        function updateEndDays() {
            const startDate = startDatePicker.selectedDates[0];
            const days = parseFloat($('#days').val());

            if (startDate && !isNaN(days)) {
                const endDate = new Date(startDate);

                // Trừ 1 ngày vì ngày bắt đầu đã tính
                const wholeDays = Math.floor(days) - 1;
                const remainingHours = (days - Math.floor(days)) * 24;

                // Thêm phần ngày và giờ vào startDate
                endDate.setDate(startDate.getDate() + wholeDays);
                endDate.setHours(startDate.getHours() + remainingHours);

                // Cập nhật Flatpickr của end
                endDatePicker.setDate(endDate, true);

            }
        }

        function setupFormForNew(renew = null) {
            // Thay đổi action và method cho form để tạo mới
            $('#setupForm').attr('action', route('campaigns.store'));
            $('#setupForm').attr('method', 'POST');
            $('#renewButton').addClass('d-none');

            if (renew === 'renew') {
                // Lấy ngày hiện tại và đặt giờ về 00:00
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                // Cập nhật ngày bắt đầu là ngày hôm nay
                startDatePicker.clear(); // Xóa giá trị cũ trong Flatpickr
                startDatePicker.setDate(today, true); // true: Kích hoạt sự kiện onChange

                // Tính toán và thiết lập ngày kết thúc
                const defaultDays = $('#days').val(); // Giá trị mặc định số ngày cho chiến dịch mới
                console.log( $('#days').val());
                
                const endDate = new Date(today);
                endDate.setDate(today.getDate() + defaultDays - 1); // Thêm số ngày trừ đi 1 (tính cả ngày bắt đầu)
                endDatePicker.clear(); // Xóa giá trị cũ trong Flatpickr
                endDatePicker.setDate(endDate, true); // Cập nhật ngày kết thúc
            }
            setupForm();
        }

        function setupFormForUpdate() {
            // Thay đổi action và method cho form để cập nhật
            var campaign_id = $('#campaign_id').val()
            $('#setupForm').attr('action', '/campaigns/' + campaign_id + '/update');
            $('#methodField').val('PUT');
            $('#editButton').addClass('d-none');
            setupForm();
        }

        function formatNumberInput(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;

            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                this.value = new Intl.NumberFormat().format(value);
            });

            input.addEventListener('focus', function() {
                this.value = this.value.replace(/,/g, '');
            });

            input.addEventListener('blur', function() {
                let value = this.value.replace(/\D/g, '');
                this.value = new Intl.NumberFormat().format(value);
            });
        }

        function removeFormatting(inputId) {
            const input = document.getElementById(inputId);
            if (input) {
                input.value = input.value.replace(/[,.]/g, '');
            }
        }

        function setupForm() {
            const inputs = document.querySelectorAll('#setupForm input, #setupForm textarea, #setupForm select');
            inputs.forEach(input => {
                input.removeAttribute('readonly');
                input.removeAttribute('disabled');
            });

            // Hiển thị nút Lưu và trường Số Ngày
            document.getElementById('saveButton').style.display = 'inline-block';
            const daysField = document.querySelector('.form-group.d-none');
            if (daysField) daysField.classList.remove('d-none');
        }

        $('#saveButton').click(function(e) {
            removeFormatting('budgetmonth');
            removeFormatting('payment');
            removeFormatting('promotion');
            formatDateToISO('start');
            formatDateToISO('end');
            $('#setupForm').submit();
        });

        function formatDateToISO(inputId) {
            const input = document.getElementById(inputId);
            if (input && input.value) {
                const [day, month, year] = input.value.split('-');
                input.value = `${year}-${month}-${day}`;
            }
        }

        function togglePromotionPercentFields() {
            var typecampId = $('select[name="typecamp_id"]').val();

            if (typecampId == '1') {
                // Khi chọn Trọn Gói (typecamp_id=1)
                $('#promotion-group').show();
                $('#percent-group').hide();
            } else if (typecampId == '2') {
                // Khi chọn Ngân Sách (typecamp_id=2)
                $('#promotion-group').hide();
                $('#percent-group').show();
            }

        }
        // updateEndDays()
    </script>

    <script>
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
                setup();
            @endforeach
        @endif

        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        @if (session('warning'))
            toastr.warning('{{ session('warning') }}');
        @endif
    </script>
@stop
