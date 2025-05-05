@extends('adminlte::page')

@section('title', isset($campaign) ? 'Chi tiết Chiến Dịch' : 'Thêm Chiến Dịch')

@section('content_header')
    <h1>{{ isset($campaign) ? 'Chi tiết Chiến Dịch' : 'Thêm Chiến Dịch' }}</h1>
@stop


@section('content')
    <div class="container">
        <div class="row">
            <div class=" col-9 col-md-12 ">
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
                        @if (isset($campaign))
                            <table class="table table-bordered">
                                <tr><th>Website</th><td>{{ $campaign->website->name }}</td></tr>
                                <tr><th>Kỹ thuật</th><td>{{ $campaign->tech?->fullname ?? '' }}</td></tr>
                                <tr><th>Vị trí</th><td>{{ $campaign->top_position }}</td></tr>
                                <tr><th>Khu vực</th><td>{{ $campaign->region }}</td></tr>
                                <tr><th>Loại đối sánh</th><td>{{ $campaign->keyword_type == 0 ? 'Đối Sánh Chính Xác' : 'Đối Sánh Cụm Từ' }}</td></tr>
                                <tr><th>Hình thức</th><td>{{ $campaign->typecamp_id == 1 ? 'Trọn Gói' : 'Ngân Sách' }}</td></tr>
                                <tr><th>Thời hạn hiển thị</th><td>{{ $campaign->display }}</td></tr>
                                <tr><th>Ngân sách</th><td>{{ number_format($campaign->budgetmonth) }}</td></tr>
                                <tr><th>Thanh toán</th><td>{{ number_format($campaign->payment) }}</td></tr>
                                <tr><th>Giá giảm</th><td>{{ number_format($campaign->promotion) }}</td></tr>
                                <tr><th>Phí quản lý (%)</th><td>{{ $campaign->percent }}</td></tr>
                                <tr><th>Bắt đầu</th><td>{{ Carbon\Carbon::parse($campaign->start)->format('d-m-Y H:i') }}</td></tr>
                                <tr><th>Kết thúc</th><td>{{ Carbon\Carbon::parse($campaign->end)->format('d-m-Y H:i') }}</td></tr>
                                <tr>
                                    <th>Số ngày chạy</th>
                                    <td>{{ \Carbon\Carbon::parse($campaign->start)->diffInDays(\Carbon\Carbon::parse($campaign->end)) + 1 }}</td>
                                </tr>
                                <tr><th>Thiết bị</th><td>{{ $campaign->device }}</td></tr>
                                <tr><th>VAT</th>
                                    <td>
                                        @if ($campaign->vat == 0)
                                            Không xuất
                                        @elseif ($campaign->vat == 1)
                                            Xuất
                                        @elseif ($campaign->vat == 2)
                                            Đã xuất
                                        @endif
                                    </td>
                                </tr>
                                <tr><th>Từ khoá</th><td>{{ $campaign->keywords }}</td></tr>
                                <tr><th>Ghi chú setup</th><td>{{ $campaign->notes }}</td></tr>
                                <tr>
                                    <th>Ghi chú chiến dịch</th>
                                    <td>
                                        @if ($campaign->note && count($campaign->note))
                                            @foreach ($campaign->note as $note)
                                            @if ($note->note!= null)
                                                <div class="alert alert-info">
                                                    <strong>{{ $note->user->fullname }}:</strong> {{ $note->note }}
                                                    <br>
                                                    <small>{{ \Carbon\Carbon::parse($note->created_at)->format('d-m-Y H:i') }}</small>
                                                </div>
                                                
                                            @endif
                                            @endforeach
                                        @else
                                            Không có ghi chú
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        @else
                            <form id="setupForm" action="{{ route('campaigns.store') }}" method="POST">
                                @csrf
                                <input type="hidden" id="methodField" name="_method" value="POST">
                                <!-- ... giữ nguyên phần form cho trường hợp tạo mới ... -->
                                <!-- (giữ toàn bộ phần else cũ, không sửa) -->
                            </form>
                        @endif
                    </div>
                </div>
            </div>
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
