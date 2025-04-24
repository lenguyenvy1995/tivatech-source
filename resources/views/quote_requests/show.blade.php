@extends('adminlte::page')

@section('title', 'Chi Tiết Yêu Cầu Báo Giá')

@section('content_header')
    <h1>
        Báo Giá : <strong class="text-danger"> {{ $quoteRequest->quoteDomain->name }}</strong></h1>
@stop

@section('content')
    @foreach ($quoteDomain->quoteRequests as $key => $quoteRequest)
        <!-- Hiển thị thông tin yêu cầu -->
        <div class="card @if ($key != 0) collapsed-card @endif">
            <div class="card-header " >
                <h3 class="card-title"> {{ $quoteRequest->user->fullname }} -
                    {{ date_format($quoteRequest->created_at, 'H:i:s d-m-Y') }}
                    @if ($quoteRequest->status != 'pending')
                        <a href="{{ route('quote-requests.edit', $quoteRequest->id) }}"
                            class="btn btn-sm btn-primary mt-3">BÁO LẠI</a>
                    @else
                        <!-- Thêm Nút Báo Giá cho người có quyền -->
                        @can('manage all quote requests')
                            <a target="_blank" href="{{ route('quotes.create', $quoteRequest->id) }}"
                                class="btn btn-success btn-sm ml-2">
                                <i class="fa fa-plus" aria-hidden="true"></i> Báo Giá
                            </a>
                            <form action="{{ route('quote-requests.reject', $quoteRequest->id) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc chắn muốn từ chối báo giá này?');">
                                    Từ chối
                                </button>
                            </form>
                        @endcan
                    @endif

                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                    <!-- Thêm các nút khác nếu cần -->
                </div>
            </div>
            <div
                class="card-body @if ($key != 0) @if ($quoteRequest->status != 'pending') collapse @endif  @endif">
                <p><strong>Từ khóa:</strong> <br> {!! nl2br(e($quoteRequest->keywords)) !!}</p>
                <p><strong>Vị trí top:</strong> {{ implode(', ', $quoteRequest->top_position) }}</p>
                <p><strong>Khu vực chạy quảng cáo:</strong> {{ $quoteRequest->region }}</p>
                <p><strong>Loại từ khóa:</strong> {{ implode(', ', $quoteRequest->keyword_type) }}</p>
                <p><strong>Hình thức chiến dịch:</strong> {{ implode(', ', $quoteRequest->campaign_type) }}</p>
                <p><strong>Trạng thái:</strong>
                    @if ($quoteRequest->status == 'pending')
                        <span class="badge badge-warning">Chờ xử lý</span>
                    @elseif($quoteRequest->status == 'approved')
                        <span class="badge badge-success">Đã báo giá</span>
                    @elseif($quoteRequest->status == 'rejected')
                        <span class="badge badge-danger">Đã từ chối</span>
                    @else
                        <span class="badge badge-success">Đã báo giá</span>
                    @endif
                </p>
                @if ($quoteRequest->quotes->count() > 0)
                    @foreach ($quoteRequest->quotes as $quote)
                        <div class="form-group d-none">
                            <label for="quoteContent">Nội dung Báo Giá</label>
                            <textarea id="quoteContent" class="form-control" rows="8" readonly>{{ str_replace('<br>', "\n", '<br>BÁO GIÁ TRỌN GÓI QUẢNG CÁO GOOGLE CÔNG TY TIVATECH <br>1. Từ khóa:<br>' . $quoteRequest->keywords . '<br>===============<br>2. Giá:<br>' . $quote->estimated_cost . '<br>3. Website: https://' . $quoteRequest->quoteDomain->name . '<br>4. Khu vực: ' . $quoteRequest->region . '<br>5. Loại từ khóa: ' . implode(', ', $quoteRequest->keyword_type) . '<br>6. Hình thức: ' . implode(', ', $quoteRequest->campaign_type) . '<br>7. Thời gian: 6h-22h<br>8. Thiết bị hiển thị: Trên tất cả các thiết bị') }}</textarea>
                        </div>


                        <div class="card card-success mb-3">
                            <div class="card-header">
                                <h5 class="card-title">Báo Giá #{{ $quote->id }}</h5>

                            </div>
                            <div id="quoteCollapse{{ $quote->id }}">
                                <div class="card-body">
                                    <p><strong>Chi phí ước tính:</strong> <br> {!! nl2br(e($quote->estimated_cost)) !!} VND</p>
                                    <p><strong>Chi tiết:</strong> {!! nl2br(e($quote->details)) !!}</p>
                                    <p><strong>Người tạo:</strong> {{ $quote->user->fullname }}</p>
                                    <p><strong>Ngày tạo:</strong> {{ $quote->created_at->format('H:i d-m-Y ') }}</p>
                                    <p><strong>Ngày update:</strong> {{ $quote->updated_at->format('H:i d-m-Y ') }}</p>
                                    <!-- Thêm các thông tin khác nếu cần -->
                                    <button type="button" class="btn btn-success" onclick="copyQuoteContent()"><i
                                            class="fas fa-copy"></i> Copy Báo Giá</button>
                                    {{-- <a target="_blank" href="route('websites.campaigns')" class="btn bg-maroon" ><i class="fas fa-copy"></i> Setup Chiến Dịch</a> --}}

                                </div>

                            </div>
                            <div class="row justify-content-center">
                                <div class="col-12 col-md-6">
                                    <div class="direct-chat" style="max-width:800px">
                                        <div class="card card-primary direct-chat direct-chat-primary">
                                            <div class="card-header">
                                                <h3 class="card-title">Trao đổi báo giá</h3>
                                            </div>
                                            <div class="card-body">
                                                <!-- Hiển thị tin nhắn -->
                                                <div class="direct-chat-messages" id="chatMessages">
                                                    @foreach ($quote->messages as $message)
                                                        <div
                                                            class="direct-chat-msg {{ $message->user_id == auth()->id() ? 'right' : '' }}">
                                                            <div class="direct-chat-infos clearfix">
                                                                <span
                                                                    class="direct-chat-name {{ $message->user_id == auth()->id() ? 'float-right' : 'float-left' }}">
                                                                    {{ $message->user->fullname }}
                                                                </span>
                                                                <span
                                                                    class="direct-chat-timestamp {{ $message->user_id == auth()->id() ? 'float-left' : 'float-right' }}">
                                                                    {{ $message->created_at->format('H:i d-m-Y') }}
                                                                </span>
                                                            </div>
                                                            <div class="direct-chat-text">
                                                                {{ $message->content }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <form id="chatForm">
                                                    @csrf
                                                    <input type="hidden" name="quote_id" value="{{ $quote->id }}">
                                                    <div class="input-group">
                                                        <input type="text" name="content" placeholder="Nhập tin nhắn ..."
                                                            class="form-control" required>
                                                        <span class="input-group-append">
                                                            <button type="submit" class="btn btn-primary">Gửi</button>
                                                        </span>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                           

                        </div>
                    @endforeach
                @else
                    <div class="alert alert-info">
                        Bạn chưa nhận được báo giá cho yêu cầu này.
                    </div>
                @endif

            </div>
        </div>
    @endforeach
@stop

@section('js')
    <script>
        // Cấu hình Toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000",
        };

        // Hiển thị thông báo lỗi từ server
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
            @endforeach
        @endif

        // Hiển thị thông báo thành công (nếu có)
        @if (session('success'))
            toastr.success('{{ session('success') }}');
        @endif
        function copyQuoteContent() {
            // Lấy nội dung trong textarea đã bị ẩn
            var quoteContent = document.getElementById("quoteContent");
            console.log(quoteContent);

            // Tạo một textarea tạm thời để sao chép nội dung (nếu cần)
            quoteContent.style.display = "block"; // Tạm thời hiển thị nếu cần thiết

            // Chọn toàn bộ nội dung
            quoteContent.select();
            quoteContent.setSelectionRange(0, 99999); // Dành cho mobile

            navigator.clipboard.writeText(quoteContent.value)
                .then(() => {
                    toastr.success("Đã sao chép nội dung báo giá vào clipboard!");

                })
                .catch(err => {
                    console.error("Lỗi sao chép: ", err);
                });

            // Ẩn lại textarea
            quoteContent.style.display = "none";

        }
    </script>

    <script>
        $(document).ready(function() {
            $('#chatForm').on('submit', function(event) {
                event.preventDefault();

                $.ajax({
                    url: '/messages',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#chatMessages').append(response.html);
                        $('#chatForm')[0].reset(); // Reset form
                        $('#chatMessages').scrollTop($('#chatMessages')[0]
                        .scrollHeight); // Cuộn xuống cuối cùng
                    },
                    error: function(xhr) {
                        alert('Không thể gửi tin nhắn.');
                    }
                });
            });
        });

        $(function() {
            // Khởi tạo Popover cho các liên kết có data-toggle="popover"
            $('[data-toggle="popover"]').popover({
                trigger: 'hover',
                placement: 'top',
                html: true,
                sanitize: false, // Bảo mật: nếu bạn không cần hiển thị HTML, hãy giữ mặc định là true
            });

            // Đóng popover khi nhấp vào bất kỳ đâu trên trang
            $('body').on('click', function(e) {
                $('[data-toggle="popover"]').each(function() {
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover')
                        .has(e.target).length === 0) {
                        $(this).popover('hide');
                    }
                });
            });
        });
        // Tự động chuyển đổi icon khi card được thu gọn/mở rộng
        $(document).on('click', '[data-card-widget="collapse"]', function() {
            var icon = $(this).find('i');
            icon.toggleClass('fa-minus fa-plus');
        });
    </script>

@stop
