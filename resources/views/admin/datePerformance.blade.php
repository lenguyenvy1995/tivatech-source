@extends('adminlte::page')

@section('title', 'Hiệu Suất Theo Ngày')

@section('content_header')
    <h1>Dữ Liệu Website Vượt </h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <!-- Input Date Filters -->
                <div class="row mb-3">
                    {{-- <form action="{{ route('budgets.import') }}" method="POST">
                        @csrf
                        <label for="date">Chọn ngày:</label>
                        <input type="date" name="date" id="date" required>
                        <button type="submit">Nhập dữ liệu</button>
                    </form> --}}

                    <div class="col-md-3">
                        <div class="form-inline">
                            <label for="dateBq">Chọn ngày: </label>
                            <input type="text" id="dateBq" class="form-control"
                                value="{{ \Carbon\Carbon::yesterday()->format('d-m-Y') }}">
                        </div>
                    </div>
                    <div class="col text-right">
                        <button class="btn btn-secondary filter-tech-button" data-tech-id="">Tất cả</button>
                        @foreach (App\Models\User::where('status',1)->where('roles_id','4')->get() as $tech)
                            <button class="btn btn-success filter-tech-button" data-tech-id="{{ $tech->id }}">{{ $tech->fullname }}</button>
                        @endforeach
                    </div>
                </div>

                <table id="datePerformanceDataTable" class="table table-bordered table-striped table-hover   ">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Website</th>
                            <th>Ngân sách </th>
                            <th>Chi Phí</th>
                            <th>Lợi Nhuận</th>
                            <th>Tổng Lợi Nhuận</th>
                            <th>Ngày / Tổng Ngày</th>
                            <th>Saler</th>
                            <th>Cảnh báo</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    @routes()
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        td {
            position: relative;
        }
    </style>
@stop
@section('js')
    <script>
        const startDatePicker = flatpickr("#dateBq", {
            dateFormat: "d-m-Y",
            // maxDate: 0, // Không cho phép chọn ngày tương lai
            // inline: true,
            locale: {
                firstDayOfWeek: 1 // start week on Monday
            }
        });
        $('[data-toggle="tooltip"]').tooltip();
        // Làm mới tooltip sau khi DataTable tải lại
        $('#datePerformanceDataTable').on('draw.dt', function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
        $('#dateBq').change(function(e) {
            table.draw();
        });
        // Biến lưu kỹ thuật được chọn, mặc định lấy từ server nếu là techads, rỗng nếu admin
        let selectedTechId = '{{ Auth::user()->hasRole("techads") ? Auth::id() : "" }}';

        // Xử lý sự kiện click cho các nút kỹ thuật
        $(document).on('click', '.filter-tech-button', function() {
            selectedTechId = $(this).data('tech-id');
            table.draw();
        });
        let table = $('#datePerformanceDataTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 200,
            lengthMenu: [50, 100, 200, -1],
            ajax: {
                url: route('datePerformance'),
                data: function(d) {
                    d.date = $('#dateBq').val();
                    d.tech_id = selectedTechId;
                },
                dataSrc: 'data', // Đảm bảo ánh xạ tới `data` trong JSON trả về
                error: function(xhr, status, error) {
                    console.log('Lỗi AJAX:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false }, // Cột STT
                {
                    data: 'website_name',
                    name: 'website_name'
                },
                {
                    data: 'budget',
                    name: 'budget',
                    render: $.fn.dataTable.render.number(',', '.', 0)
                },
                {
                    data: 'cost',
                    name: 'cost',
                },
                {
                    data: 'profit',
                    name: 'profit',
                },
                {
                    data: 'total_profit',
                    name: 'total_profit',
                    orderable: true,
                    render: function(data) {
                        // Đảm bảo dữ liệu trả về là kiểu số
                        return data + ' %';
                    }

                },
                {
                    data: 'expired',
                    name: 'expired'
                },
                {
                    data: 'saler',
                    name: 'saler'
                },
                {
                    data: 'warning',
                    name: 'warning',
                }


            ],
            order: [
                [4, 'asc']
            ], // Sắp xếp theo cột `total_profit` (cột thứ 4)

            language: {
                // Tùy chỉnh ngôn ngữ nếu cần
                "processing": "Đang tải...",
                "lengthMenu": "Hiển thị _MENU_ mục",
                "zeroRecords": "Không tìm thấy dữ liệu",
                "info": "Hiển thị trang _PAGE_ của _PAGES_",
                "infoEmpty": "Không có mục nào",
                "infoFiltered": "(lọc từ _MAX_ mục)",
                "search": "Tìm kiếm:",
                "paginate": {
                    "first": "Đầu",
                    "last": "Cuối",
                    "next": "Tiếp",
                    "previous": "Trước"
                },
            },

            createdRow: function(row, data) {
                $(row).attr('data-id', data.idCounter); // Thêm `data-id` vào từng hàng
            }
        });
        // Khi load trang, nếu có selectedTechId (techads), chỉ hiển thị dữ liệu của họ; nếu admin, mặc định là "Tất cả"
        table.draw();
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
