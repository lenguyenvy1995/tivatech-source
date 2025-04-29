@foreach ($campaign->note()->latest()->limit(3)->get() as $note)
    - {{ $note->note }}<br>
@endforeach
@if ($campaign->note()->count() > 3)
    <span class="text-danger" onclick="toggleNotes({{ $campaign->id }})">Xem thêm...</span>
@endif