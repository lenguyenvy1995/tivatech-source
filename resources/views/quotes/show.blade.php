@extends('adminlte::page')

@section('title', 'Chi Tiết Báo Giá')

@section('content')
    <h1>Báo Giá #{{ $quote->id }}</h1>
    
    <!-- Hiển thị báo giá -->
    <div class="quote-content" id="quoteContent">
        {!! nl2br(e($quote->estimated_cost)) !!}
    </div>

    <!-- Button để sao chép báo giá -->
    <button onclick="copyQuote()" class="btn btn-primary mt-3">Sao Chép Báo Giá</button>

    <!-- Form để gửi email -->
    <form method="POST" class="mt-4">
        @csrf
        <div class="form-group">
            <label for="email">Email khách hàng</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="subject">Tiêu đề</label>
            <input type="text" name="subject" class="form-control" value="Báo giá từ công ty" required>
        </div>
        <div class="form-group">
            <label for="message">Nội dung báo giá</label>
            <textarea name="message" id="quoteMessage" class="form-control" rows="6" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Gửi Báo Giá</button>
    </form>
@stop

@section('js')
    <script>
        function copyQuote() {
            // Lấy nội dung báo giá và sao chép vào clipboard
            var quoteContent = document.getElementById("quoteContent").innerText;
            navigator.clipboard.writeText(quoteContent).then(() => {
                alert("Đã sao chép báo giá!");
                document.getElementById("quoteMessage").value = quoteContent; // Đưa nội dung vào form gửi email
            }).catch(err => {
                console.error('Không thể sao chép:', err);
            });
        }
    </script>
@stop
