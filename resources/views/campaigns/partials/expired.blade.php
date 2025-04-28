@php
    $today = Carbon\Carbon::today();
    $end = Carbon\Carbon::parse($campaign->end);
    $remainingDays = $today->diffInDays($end, false);
@endphp

@if ($remainingDays >= 0)
    <span class="badge badge-pill badge-success">Còn {{ $remainingDays }} ngày</span>
@else
    <span class="badge badge-pill badge-danger">Hết hạn {{ abs($remainingDays) }} ngày</span>
@endif