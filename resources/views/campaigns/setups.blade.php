@extends('adminlte::page')

@section('title', 'Danh sách Campaigns với Status ID 5')

@section('content_header')
    <h1>Danh sách setup chiến dịch</h1>
@stop


@section('content')
    <div class="card">
        <div class="card-body">
            @can('setup ads')
            <div id="filterContainer">
                <a href="{{route('campaigns.create')}}" class="btn btn-primary m-2"> <i class="fas fa-plus    "></i> Thêm chiến dịch</a>
            </div>
            @endcan
            <div class="table-responsive">
                <table id="campaigns-table" class="table table-success table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Website</th>
                            <th>Bắt Đầu</th>
                            <th>Kết Thúc</th>
                            <th>Ngân Sách</th>
                            <th width='100px'>Thao Tác</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.datatables.net/buttons/2.0.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.0/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.0/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#campaigns-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('campaigns.setups') }}',
                pageLength: 100,
                page: 100,
                lengthMenu: [50, 100, 200],
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'website_name',
                        name: 'website_name'
                    },
                    {
                        data: 'start',
                        name: 'start'
                    },
                    {
                        data: 'end',
                        name: 'end'
                    },
                    {
                        data: 'budgetmonth',
                        name: 'budgetmonth'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
        });
        function setupCampaign(campaignId) {
            if (confirm('Bạn có chắc chắn muốn setup xong campaign này?')) {
                $.ajax({
                    url: '/campaigns/' + campaignId + '/setup',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        
                        toastr.success(response.success); // Thông báo thành công
                        $('#campaigns-table').DataTable().ajax
                    .reload(); // Tải lại DataTable để cập nhật trạng thái
                    },
                    error: function(xhr) {
                        toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
                    }
                });
            }
        }
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
