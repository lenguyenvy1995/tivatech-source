@extends('adminlte::page')

@section('title', 'Danh sách Chiến Dịch')

@section('content_header')
    <h1>Chi Phí Website {{ $campaign->website->name }}</h1>
@stop
@section('content')
    <div class="card">

        <div class="card-body">
            <!-- Button mở modal -->
            <button type="button" class="btn btn-success mb-3" id="addBudget">
                Thêm Chi Phí Mới
            </button>

            <!-- Modal -->
            <div class="modal fade" id="addBudgetModal" tabindex="-2" role="dialog" >
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addBudgetModalLabel">Thêm Chi Phí Mới</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="addBudgetForm">
                                <div class="form-group">
                                    <label for="budget">Chi Phí:</label>
                                    <input type="number" id="budget" class="form-control" placeholder="Nhập chi phí">
                                </div>
                                <div class="form-group">
                                    <label for="date">Ngày Chạy:</label>
                                    <input type="date" id="date" class="form-control">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                            <button type="button" id="saveBudget" class="btn btn-primary">Lưu</button>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table table-success table-striped table-bordered table-hover" id="budgetsTable">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Ngày Chạy</th>
                        <th>Chi Phí</th>
                        <th>Tính Ngày</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="modal fade" id="editBudgetModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Budget</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_budget_id">
                        @csrf()
                        <div class="form-group">
                            <label for="edit_budget">Chi Phí:</label>
                            <input type="text" class="form-control" id="edit_budget">
                        </div>
                        <div class="form-group">
                            <label for="edit_date">Ngày chạy:</label>
                            <input type="date" class="form-control" id="edit_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="updateBudget" class="btn btn-primary">Lưu</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@section('css')
    @routes()
@endsection

@section('js')
    <script>
        // Đặt giá trị mặc định cho trường ngày là hôm nay
        const today = new Date().toISOString().split('T')[0];
        $('#date').val(today);
        $(document).ready(function() {
            const table = $('#budgetsTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 100, // Số lượng dòng hiển thị mặc định

                ajax: '{{ route('campaigns.budgets', $campaign->id) }}',
                columns: [{
                        data: null,
                        name: 'stt',
                        searchable: false,
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },

                    {
                        data: 'date',
                        name: 'date',
                        class: 'text-center',
                    },
                    {
                        data: 'budget',
                        name: 'budget',
                        class: 'text-right',

                    },
                    {
                        data: 'calu',
                        name: 'calu',
                        class: 'text-center',

                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        class: 'text-center',

                    }
                ]
            });

            // Kích hoạt tooltip sau khi bảng render lại
            table.on('draw', function() {
                setTimeout(() => {
                    $('[data-toggle="tooltip"]').tooltip();
                }, 500);
            });

           
            $('#addBudget').on('click', function() {
                $('#addBudgetModal').modal('show'); // Đóng modal

            })
            // Lưu dữ liệu mới từ modal
            $('#saveBudget').on('click', function() {
                const formData = {
                    budget: $('#budget').val(),
                    date: $('#date').val(),
                    campaign_id: '{{ $campaign->id }}',
                    _token: '{{ csrf_token() }}' // CSRF Token
                };

                $.ajax({
                    url: '{{ route('budgets.store') }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#addBudgetModal').modal('hide'); // Đóng modal
                        $('#addBudgetForm')[0].reset(); // Reset form
                        table.ajax.reload(); // Reload Datatable
                        toastr.success('Budget added successfully!');
                        
                    },
                    error: function(err) {
                        toastr.error('Error: ' + (err.responseJSON.message ||
                            'Không thể thêm dữ liệu'));
                    }
                });
            });
            // Xử lý sửa
            $(document).on('click', '.editBudget', function() {
                const id = $(this).data('id');
                $.get('{{ url('budgets') }}/' + id + '/edit', function(data) {
                    $('#editBudgetModal').modal('show');
                    $('#edit_budget').val(data.budget);
                    $('#edit_date').val(data.date);
                    $('#edit_budget_id').val(data.id);
                });
            });

            $('#updateBudget').on('click', function() {
                const id = $('#edit_budget_id').val();
                const formData = {
                    budget: $('#edit_budget').val(),
                    date: $('#edit_date').val(),
                    _token: '{{ csrf_token() }}' // CSRF Token

                };
                $.ajax({
                    url: '{{ url('budgets') }}/' + id,
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        table.ajax.reload();
                        $('#editBudgetModal').modal('hide');
                        toastr.success('Chi phí đã được cập nhật thành công!');
                    },
                    error: function(err) {
                        toastr.error('Error occurred!');
                    }
                });
            });

            // Xử lý xóa
            $(document).on('click', '.deleteBudget', function() {
                const id = $(this).data('id');
                const formData = {
                    _token: '{{ csrf_token() }}' // CSRF Token

                };
                if (confirm('Bạn có chắc chắn là xoá chi phí này?')) {
                    $.ajax({
                        url: '{{ url('budgets') }}/' + id,
                        method: 'DELETE',
                        data: formData,
                        success: function(response) {
                            table.ajax.reload();
                            toastr.success('Chi phí đã được xoá thành công!');
                        },
                        error: function(err) {
                            toastr.error('Error occurred!');
                        }
                    });
                }
            });
            $(document).on('change', '.changeCalu', function() {
                const budgetId = $(this).data('id');                
                const caluValue = $(this).val();
                
                $.ajax({
                    url: '{{ route('budgets.updateCalu') }}', // Thay bằng route thực tế
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: budgetId,
                        calu: caluValue
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Cập nhật thành công');
                    },
                    error: function(err) {
                        toastr.error('Có lỗi xảy ra. Vui lòng thử lại.');
                    }
                });
            });

        });
    </script>
@endsection
