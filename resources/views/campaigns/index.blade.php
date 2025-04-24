@extends('adminlte::page')

@section('title', 'Danh sách Chiến Dịch')

@section('content_header')
    <h1>Danh sách Chiến Dịch cho Website {{ $website->name }}</h1>
@stop

@section('content')
    <div class="card">
        
        <div class="card-body">
            <table class="table table-success table-striped table-bordered table-hover" id="campaigns-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ngân Sách</th>
                        <th>Thời Gian</th>
                        <th>Trạng Thái</th>
                        <th width='100px'>Thao Tác</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop
@section('css')
    @routes()
@stop
@section('js')
    <script>
        $(document).ready(function() {
            $('#campaigns-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('websites.campaigns', $website->id) }}',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },

                    {
                        data: 'budgetmonth',
                        name: 'budgetmonth',
                        render: $.fn.dataTable.render.number(',', '.', 0,
                            '') // Định dạng số với phân cách hàng nghìn
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'status_name',
                        name: 'status_name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    
    </script>
    <script>
        // Hiển thị thông báo từ server
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
                setup()
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
