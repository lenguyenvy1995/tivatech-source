@extends('adminlte::page')

@section('title', 'Google Sheet Data')

@section('content_header')
    <h1>Dữ Liệu Website Vượt Ngân Sách Trong Ngày</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="sheetDataTable" class="table table-bordered table-striped">
                <thead>  
                    <tr>
                        <th>Tài Khoản</th>
                        <th>Chiến Dịch</th>
                        <th>Hiển Thị</th>
                        <th>Click</th>
                        <th>Ngân Sách</th>
                        <th>Chi Phí</th>
                    </tr>
                </thead>
                <tbody></tbody>
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
            let table = $('#sheetDataTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 200,
                lengthMenu: [50, 100, 200, -1],
                ajax: {
                    url: route('intradayPerformance'),
                    dataSrc: 'data' // Đảm bảo ánh xạ tới `data` trong JSON trả về
                },
                columns: [{
                        data: 'account',
                        name: 'account',
                        searchable: false
                    },
                    {
                        data: 'domain',
                        name: 'domain',
                    },
                    {
                        data: 'impressions',
                        name: 'impressions',
                        searchable: false,
                        class: 'text-center'
                    },
                    {
                        data: 'clicks',
                        name: 'clicks',
                        searchable: false,
                        class: 'text-center'
                    },
                    {
                        data: 'budget',
                        name: 'budget',
                        searchable: false,
                        class: 'text-right'
                    },
                    {
                        data: 'converted_cost',
                        name: 'converted_cost',
                        searchable: false,
                        class: 'text-right'

                    },
                  
                ],
                createdRow: function(row, data) {
                    $(row).attr('data-id', data.idCounter); // Thêm `data-id` vào từng hàng
                }
            });
            let previousData = {};
            setInterval(function() {
                table.ajax.reload(function(json) {
                    // So sánh dữ liệu mới và cũ
                    json.data.forEach(row => {
                        let rowId = row.idCounter;
                        let oldRowData = previousData[rowId];

                        if (oldRowData && (JSON.stringify(oldRowData) !== JSON.stringify(
                                row))) {
                            // Nếu dữ liệu đã thay đổi, thêm lớp CSS vào hàng tương ứng
                            $(`#sheetDataTable tbody tr[data-id="${rowId}"]`).addClass(
                                'bg-danger');
                        }

                        // Cập nhật dữ liệu hiện tại vào bộ nhớ
                        previousData[rowId] = row;
                    });
                }, false);
            }, 600000); // Làm mới mỗi phút (60,000ms)

        });
    </script>
@stop
