<span class="status-dot {{ $campaign->status->theme }}"></span> {{ ucfirst($campaign->status->name) }}<br>
@if ($campaign->paid) <span class="badge badge-success">✔️ Thanh toán</span>
@else <span class="badge badge-danger">❌ Thanh toán</span> @endif
@if ($campaign->vat == 2) <span class="badge badge-info">📄 VAT Xuất</span>
@else <span class="badge badge-warning">💰 VAT Chưa xuất</span> @endif