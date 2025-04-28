<strong>Bắt đầu:</strong> {{ Carbon\Carbon::parse($campaign->start)->format('H:i d-m-Y') }}<br>
<strong>Kết thúc:</strong> {{ Carbon\Carbon::parse($campaign->end)->format('H:i d-m-Y') }}