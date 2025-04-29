<form action="{{ route('campaigns.destroy', $campaign->id) }}" method="POST" style="display:inline;">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
</form>
<a href="{{ route('campaigns.show', $campaign->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
<button class="btn btn-sm bg-purple" onclick="openNoteModal({{ $campaign->id }})"><i class="fas fa-sticky-note"></i></button>