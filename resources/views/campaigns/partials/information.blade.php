Ngân sách: <span class="text-danger font-weight-bold">{{ number_format($campaign->budgetmonth) }}</span><br>
Thanh toán: <span class="font-weight-bold">{{ number_format($campaign->payment) }}</span><br>
GTGT (VAT): 
@if ($campaign->vat == 2)
    <span class="badge badge-success">Đã Xuất</span>
@else
    <span class="badge badge-danger">Chưa Xuất</span>
@endif