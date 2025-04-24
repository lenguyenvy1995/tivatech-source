@extends('adminlte::page')

@section('title', 'Danh sách lương theo tháng')

@section('content_header')
    <h1>Danh sách lương</h1>
@stop

@section('css')
    @routes()
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('salaries.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="month">Tháng:</label>
                        <select name="month" id="month" class="form-control">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>Tháng
                                    {{ $m }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year">Năm:</label>
                        <select name="year" id="year" class="form-control">
                            @for ($y = now()->year - 5; $y <= now()->year; $y++)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="user_id">Nhân viên:</label>
                        <select name="user_id" id="user_id" class="form-control">
                            @if (Auth::user()->hasRole('admin'))
                                <option value="">Tất cả</option>
                            @endif
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" {{ $employee->id == $employeeId ? 'selected' : '' }}>
                                    {{ $employee->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            @if (Auth::user()->hasRole('admin'))
                {{-- Hiển thị tổng số ngày công và tổng tiền phạt --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tổng Lương: </strong><span id="total_salary"></span>
                    </div>
                </div>
                <table id="dataTable" class="table table-bordered">
                    <!-- Cấu trúc bảng -->
                </table>
                
            @endif

            {{-- Hiển thị bảng danh sách lương --}}
            <table class="table table-bordered table-striped" id="salaryTable">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Nhân viên</th>
                        <th>Ngân hàng</th>
                        <th>Thực lãnh</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
@stop
@section('js')
    <script>
        $(document).ready(function() {
            function deleteSalary(salaryId) {
                if (confirm('Bạn có chắc chắn muốn xóa?')) {
                    $.ajax({
                        url: route('salaries.destroy', salaryId),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success('Xóa thành công');
                            table.ajax.reload(); // Cập nhật lại bảng
                        },
                        error: function() {
                            toastr.error('Có lỗi xảy ra trong quá trình xóa');
                        }
                    });
                }
            }
            // Khi người dùng nhấn nút Xóa
            $(document).on('click', '.delete-salary', function() {
                const salaryId = $(this).data('id'); // Lấy ID từ thuộc tính data-id
                deleteSalary(salaryId); // Gọi hàm deleteSalary và truyền vào ID
            });
            const table = $('#salaryTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 50,
                ajax: {
                    url: route('salaries.index'),
                    data: function(d) {
                        d.month = $('#month').val();
                        d.year = $('#year').val();
                        d.user_id = $('#user_id').val();
                    },
                    dataSrc: function(json) {
                        // Cập nhật các giá trị bổ sung
                        $('#total_salary').text(json.total_salary);
                        return json.data;
                    },
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        class: 'text-center',
                        searchable: false
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name'
                    },
                    {
                        data: 'ngan_hang',
                        name: 'ngan_hang',
                        class: 'text-left'
                    },
                    {
                        data: 'total_salary',
                        name: 'total_salary',
                        class: 'text-right'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        class: 'text-center'
                    },
                    {
                        data: null, // Cột này sẽ chứa các nút Xem, Sửa, Xóa
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                            <a target="_blank" href="/salaries/${row.id}" class="btn btn-info btn-sm">Xem</a>
                            <a href="/salaries/${row.id}/edit" class="btn btn-warning btn-sm">Sửa</a>
                                            <button class="btn btn-danger btn-sm delete-salary" data-id="${row.id}">Xóa</button>
                        `;
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' // Tiếng Việt
                }
            });

            // Khi form lọc thay đổi, tải lại bảng
            $('#month, #year, #user_id').on('change', function() {
                table.ajax.reload();
            });
            // Lắng nghe sự kiện thay đổi checkbox
            $('#salaryTable').on('change', 'input.custom-control-input', function() {
                const isChecked = $(this).prop('checked'); // Kiểm tra xem checkbox có được chọn không
                const salaryId = $(this).attr('id').replace('customCheckbox_',
                    ''); // Lấy ID salary từ checkbox

                const newEnConfirmValue = isChecked ? 2 :
                    1; // Nếu checked, en_confirm = 2, nếu unchecked, en_confirm = 1

                // Gửi AJAX để cập nhật giá trị `en_confirm` trong cơ sở dữ liệu
                $.ajax({
                    url: route('salaries.update_em_confirm'), // Đường dẫn tới API cập nhật
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        salary_id: salaryId,
                        em_confirm: newEnConfirmValue
                    },
                    dataSrc: function(json) {
                        // Cập nhật các giá trị bổ sung
                        $('#total_salary').text(json.total_salary);
                        return json.data;
                    },
                    success: function(response) {
                        if (response.success) {
                            // Nếu thành công, cập nhật lại bảng
                            table.ajax.reload(); // Làm mới dữ liệu bảng
                            toastr.success('Cập nhật thành công');
                        } else {
                            toastr.error('Có lỗi xảy ra');
                        }
                    },
                    error: function() {
                        toastr.error('Có lỗi xảy ra trong quá trình gửi yêu cầu');
                    }
                });
            });



        });

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
    </script>
@stop
