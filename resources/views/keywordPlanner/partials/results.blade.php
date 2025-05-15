@if(count($results))
    <h5 class="mb-3">Kết quả từ khóa</h5>
    <div class="table-responsive">
        <table id="keywordTable" class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Từ khóa</th>
                    <th>Lượt tìm kiếm TB hàng tháng</th>
                    <th>Mức độ cạnh tranh</th>
                    <th>Giá thầu thấp (VNĐ)</th>
                    <th>Giá thầu cao (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $row)
                    <tr>
                        <td>{{ $row['keyword'] }}</td>
                        <td>{{ number_format($row['avg_monthly_searches']) }}</td>
                        <td>{{ ucfirst($row['competition']) }}</td>
                        <td>{{ number_format($row['low_bid']) }}</td>
                        <td>{{ number_format($row['high_bid']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif