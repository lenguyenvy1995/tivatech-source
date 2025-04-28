@extends('adminlte::page')

@section('title', 'Danh sách Campaigns')
@section('css')
    @routes()
    <style>
        td {
            position: relative;
        }

        .status-dots {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .status-dot {
            width: 15px;
            height: 15px;
            display: inline-block;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s ease;
            border: 2px solid transparent;
        }

        .status-dot.selected {
            transform: scale(1.2);
            border: 2px solid #000;
            /* Viền đen để chỉ trạng thái được chọn */
        }
        #campaigns-table th, #campaigns-table td {
            white-space: normal !important; /* Cho phép xuống dòng */
            word-wrap: break-word !important; /* Ngắt dòng khi cần */
            overflow-wrap: break-word !important;
            min-width: 150px; /* Đặt kích thước tối thiểu cho cột */
        }
    </style>
@stop
@section('content_header')
    <h1 id='total'>Danh sách Campaigns</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div id="filterContainer">
                <div class="form-group d-inline-block ml-2">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="filterPaid">
                        <label class="custom-control-label" for="filterPaid">Chưa Thanh toán</label>
                    </div>
                </div>
                <div class="form-group d-inline-block ml-2">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="filterVat">
                        <label class="custom-control-label" for="filterVat">Thuế GTGT</label>
                    </div>
                </div>
                <div class="checkbox icheck-turquoise d-inline-block ml-2">
                    <input type="checkbox" id="filterExpired">
                    <label for="filterExpired">Sắp hết hạn</label>
                </div>
                <div class="checkbox icheck-peterriver d-inline-block ml-2">
                    <input type="checkbox" id="filter_typecamp_tg">
                    <label for="filter_typecamp_tg">Trọn gói</label>
                </div>
                <div class="checkbox icheck-amethyst d-inline-block ml-2">
                    <input type="checkbox" id="filter_typecamp_ns">
                    <label for="filter_typecamp_ns">Ngân sách</label>
                </div>
                <form class="form-inline d-inline-block">
                    <select class="custom-select my-1 ml-2" id="filterStatus">
                        <option value=''>Chọn trạng thái</option>
                        @foreach (App\Models\Status::all() as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </form>
                @if (Auth::user()->hasRole('admin|manager|techads'))
                    <form class="form-inline d-inline-block ">
                        <select class="custom-select my-1 ml-2" id="filterUser">
                            <option value=''>Chọn trạng thái</option>
                            @foreach (App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->fullname }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
            <div class="table-responsive">
                <table id="campaigns-table"
                    class="table table-bordered table-success table-stripped table-hover text-center" style="
    table-layout: auto !important;
    width: 100% !important;">
                <thead>
                    <tr>
                        <th>Trạng thái</th>
                        <th>Website</th>
                        <th>Thời gian</th>
                        <th>Thông tin</th>
                        <th>Gia hạn</th>
                        <th>Ghi Chú</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteModalLabel">Ghi chú chiến dịch</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="noteCampaignId" name="campaign_id">
                    <div class="form-group">
                        <label for="noteContent">Nội dung ghi chú</label>
                        <textarea class="form-control" id="noteContent" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="saveNote()">Lưu ghi chú</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script>
        let table = $('#campaigns-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 200, // Số lượng dòng hiển thị mặc định
            scrollX: true, // Cho phép cuộn ngang
            autoWidth: false, // Không tự động thay đổi chiều rộng cột
            search: {
                return: true
            },
            lengthMenu: [
                [10, 50, 100, 200, -1],
                [10, 50, 100, 200, 'All']
            ],
            ajax: {
                url: '{{ route('campaigns') }}',
                data: function(d) {
                    d.filter_vat = $('#filterVat').is(':checked') ? 1 : 0; //// Thuế GTGT
                    d.filter_paid = $('#filterPaid').is(':checked') ? 1 : 0; // Lọc chưa thanh toán
                    d.filter_expired = $('#filterExpired').is(':checked') ? 1 : 0; // Lọc sắp hết hạn
                    d.filter_typecamp_tg = $('#filter_typecamp_tg').is(':checked') ? 1 : 0; // Lọc ngân sách
                    d.filter_typecamp_ns = $('#filter_typecamp_ns').is(':checked') ? 1 : 0; // Lọc trọn gói
                    d.search = $('#campaigns-table_filter input').val(); // Gửi từ khóa tìm kiếm
                    d.filter_status = $('#filterStatus').val(); // Gửi trạng thái được chọn
                    d.filter_user = $('#filterUser ').val(); // Gửi trạng thái được chọn
                },
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [{
                    data: 'stt',
                    name: 'stt', // Tên cho cột ID giả
                    className: 'text-left align-content-center',
                    orderable: false, // Tắt sắp xếp
                    searchable: false // Tắt tìm kiếm
                },
                {
                    data: 'website_name',
                    name: 'website_name',
                    className: 'text-left align-content-center',
                    searchable: true // Tắt tìm kiếm

                },
                {
                    data: 'duration',
                    name: 'duration',
                    className: 'align-content-center p-1 justify-content-left',
                    searchable: false // Tắt tìm kiếm
                },
                {
                    data: 'information',
                    name: 'information',
                    className: 'text-left align-content-center ',
                    searchable: false // Tắt tìm kiếm
                },
                {
                    data: 'expired',
                    name: 'expired',
                    className: 'align-content-center',
                    searchable: false // Tắt tìm kiếm

                },
                {
                    data: 'note_campaign',
                    name: 'note_campaign',
                    className: 'text-left align-content-center',
                    searchable: false // Tắt tìm kiếm
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center',
                    searchable: false // Tắt tìm kiếm

                }
            ]
        });
        // Lắng nghe sự thay đổi trên checkbox và tải lại DataTable khi checkbox thay đổi
        $('#filterPaid, #filterVat, #filterExpired,#filter_typecamp_tg,#filter_typecamp_ns').on('change', function() {
            table.ajax.reload(); // Tải lại bảng với điều kiện lọc mới
        });

        function openNoteModal(campaignId) {
            // Đặt ID chiến dịch vào modal (nếu cần)
            $('#noteCampaignId').val(campaignId);
            // Nếu muốn hiển thị ghi chú hiện tại của chiến dịch
            $.ajax({
                url: '/campaigns/' + campaignId + '/note', // Đường dẫn API để lấy ghi chú
                method: 'GET',
                success: function(response) {
                    $('#noteModal').modal('show'); // Hiển thị modal
                },
                error: function() {
                    alert('Không thể tải ghi chú.');
                }
            });
        }

        function saveNote() {
            var campaignId = $('#noteCampaignId').val();
            var noteContent = $('#noteContent').val();

            $.ajax({
                url: route('notes.store'),
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}', // Laravel CSRF token
                    note: noteContent,
                    campaign_id: campaignId
                },
                success: function(response) {
                    $('#noteModal').modal('hide');
                    toastr.success('Ghi chú đã được lưu.');
                    table.ajax.reload();
                },
                error: function() {
                    toastr.error('Không thể lưu ghi chú.');
                }
            });
        }

        function toggleNotes(campaignId) {
            const fullNotes = document.getElementById(`fullNotes${campaignId}`);
            const showMore = fullNotes.previousElementSibling;

            if (fullNotes.style.display === 'none') {
                fullNotes.style.display = 'block';
                showMore.textContent = 'Thu gọn';
            } else {
                fullNotes.style.display = 'none';
                showMore.textContent = 'Xem thêm';
            }
        }
        $(document).ready(function() {
            // Thay đổi trạng thái
            $('.status-select').change(function() {
                const id = $(this).data('id');
                const statusId = $(this).val();

                $.ajax({
                    url: route('campaigns.updateStatus'),
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: id,
                        status_id: statusId,
                    },
                    success: function(response) {
                        toastr.success('Cập nhật trạng thái thành công!');
                    },
                    error: function() {
                        toastr.error('Có lỗi xảy ra!');
                    },
                });
            });

            // Toggle VAT
            $('.vat-toggle').click(function() {
                const id = $(this).data('id');
                const currentStatus = $(this).hasClass('btn-success') ? 1 : 0;

                $.ajax({
                    url: route('campaigns.updateVat'),
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: id,
                        vat: currentStatus ? 0 : 1,
                    },
                    success: function(response) {
                        toastr.success('Cập nhật VAT thành công!');
                        $(this).toggleClass('btn-success btn-danger').text(currentStatus ?
                            'VAT: Không' : 'VAT: Có');
                    },
                    error: function() {
                        toastr.error('Có lỗi xảy ra!');
                    },
                });
            });

            // Toggle Paid
            $('.paid-toggle').click(function() {
                const id = $(this).data('id');
                const currentStatus = $(this).hasClass('btn-success') ? 1 : 0;

                $.ajax({
                    url: route('campaigns.updatePaid'),
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: id,
                        paid: currentStatus ? 0 : 1,
                    },
                    success: function(response) {
                        toastr.success('Cập nhật Paid thành công!');
                        $(this).toggleClass('btn-success btn-danger').text(currentStatus ?
                            'Paid: Chưa thanh toán' : 'Paid: Đã thanh toán');
                    },
                    error: function() {
                        toastr.error('Có lỗi xảy ra!');
                    },
                });
            });
        });
        $(document).on('click', '.status-item', function(e) {
            e.preventDefault();

            let campaignId = $(this).attr('data-id');
            let statusId = $(this).attr('data-status-id');

            // Gửi AJAX để cập nhật trạng thái
            $.ajax({
                url: route('campaigns.updateStatus'),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    campaign_id: campaignId,
                    status_id: statusId
                },
                success: function(response) {
                    if (response.success) {
                        // Cập nhật dot hiển thị
                        toastr.success('Trạng thái đã được cập nhật!');
                        table.ajax.reload();

                    } else {
                        toastr.error('Cập nhật thất bại!');
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra khi cập nhật trạng thái!');
                }
            });
        });
        $(document).on('change', '.paid-switch', function() {
            let campaignId = $(this).attr('data-id'); // Lấy ID của campaign
            let paidStatus = $(this).is(':checked') ? 1 : 0; // Lấy trạng thái của checkbox

            // Gửi yêu cầu AJAX để cập nhật
            $.ajax({
                url: route('campaigns.updatePaid'), // Đường dẫn xử lý yêu cầu
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    campaign_id: campaignId,
                    paid: paidStatus
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Cập nhật thanh toán thành công!');
                    } else {
                        toastr.error('Cập nhật thanh toán thất bại!');
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra, vui lòng thử lại!');
                }
            });
        });
        $(document).on('change', '.vat-switch', function() {
            let campaignId = $(this).attr('data-id'); // Lấy ID của campaign
            let vatStatus = $(this).is(':checked') ? 2 : 1; // Lấy trạng thái của checkbox

            // Gửi yêu cầu AJAX để cập nhật
            $.ajax({
                url: route('campaigns.updateVat'), // Đường dẫn xử lý yêu cầu
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                    campaign_id: campaignId,
                    vat: vatStatus
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Cập nhật thuế GTGT thành công!');
                    } else {
                        toastr.error('Cập nhật thuế GTGT thất bại!');
                    }
                },
                error: function() {
                    toastr.error('Có lỗi xảy ra, vui lòng thử lại!');
                }
            });
        });

        // Lắng nghe sự thay đổi trên bộ lọc trạng thái
        $('#filterStatus').on('change', function() {
            table.ajax.reload(); // Reload bảng khi thay đổi trạng thái
        });
        // Khởi tạo Select2
        $('#filterUser').select2({
            placeholder: "Chọn nhân viên",
            allowClear: true
        });

        // Lắng nghe sự thay đổi và tải lại bảng DataTable
        $('#filterUser').on('change', function() {
            table.ajax.reload(); // Tải lại bảng với trạng thái được chọn
        });
    </script>
    
@stop