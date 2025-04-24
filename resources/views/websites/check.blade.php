@extends('adminlte::page')

@section('title', 'Kiểm tra website')

@section('css')
    @routes()
@stop
@section('content_header')
    <h1>Kiểm tra website</h1>
@stop
@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nhập domain để kiểm tra</h3>
        </div>
        <div class="card-body">
            <form id="domainSearchForm" action="{{ route('websites.check_post') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="domain">Nhập domain:</label>
                    <textarea class="form-control" id="domain" name="domain" rows="10" placeholder="Nhập domain tại đây..."></textarea>
                </div>
               <button type="button" class="btn btn-primary" id="searchButton">Tìm kiếm</button>
            </form>
            <div id="result" class="mt-4"></div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#searchButton').click(function() {
                const input = $('#domain').val().trim();

                if (!input) {
                    alert('Vui lòng nhập dữ liệu!');
                    return;
                }

                // Tách và lọc các domain từ chuỗi nhập
                const domains = extractDomains(input);

                if (domains.length === 0) {
                    alert('Không tìm thấy domain hợp lệ!');
                    return;
                }

                // Lặp qua từng domain để gửi yêu cầu tìm kiếm
                domains.forEach(domain => {
                    // Chuẩn hóa domain để tạo ID duy nhất
                    const domainId =
                    `domain-${domain.replace(/[^\w]/g, '-')}`; // Chỉ giữ lại ký tự chữ, số và thay dấu '.' bằng '-'
                    const existing = $(
                    `#${domainId}`); // Kiểm tra xem domain đã tồn tại trong kết quả hay chưa

                    if (existing.length > 0) {
                        return; // Nếu đã tồn tại, bỏ qua
                    }

                    // Tạo container mới cho domain
                    $('#result').append(`<div id="${domainId}" class="domain-result"></div>`);
                    $.ajax({
                        url: route('websites.check_post'),
                        method: 'POST',
                        data: {
                            domain: domain,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            const resultDiv = $(`#${domainId}`);
                            if (response.status === 'success') {
                                resultDiv.html(`
                            <div class="alert alert-danger">
                                <strong>Website:</strong> <a href="https://${domain} " target="_blank">${domain} </a> - Ngày kết thúc : ${response.end} - Nhân viên: ${response.saler}
                            </div>
                        `);
                            } else {
                                resultDiv.html(`
                            <div class="alert alert-success ">
                                Webiste: <a href="https://${domain} " target="_blank">${domain}</a>
                            </div>
                        `);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            // alert('Đã xảy ra lỗi. Vui lòng thử lại sau.');
                        }
                    });
                });
            });

            // Hàm tách và trích xuất các domain từ chuỗi
            function extractDomains(input) {
                // Chia nhỏ từng dòng
                const lines = input.split('\n');

                // Regex tìm kiếm domain hoặc URL
                const regex = /(https?:\/\/)?(www\.)?([\w-]+\.[\w.]+)/gi;

                const domains = [];

                lines.forEach(line => {
                    const matches = line.match(regex);
                    if (matches) {
                        matches.forEach(url => {
                            // Loại bỏ protocol và "www."
                            const domain = url.replace(/https?:\/\/(www\.)?/, '').split('/')[0];
                            if (!domains.includes(domain)) {
                                domains.push(domain);
                            }
                        });
                    }
                });

                return domains;
            }

        });
    </script>
@stop
