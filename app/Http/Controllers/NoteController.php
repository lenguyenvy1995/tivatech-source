<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'note' => 'required|string|max:255',
        ]);
        if ($request->ajax() == true) {
            $note = Note::create([
                'campaign_id' =>  $request->campaign_id,
                'user_id' => auth()->id(),
                'note' => $request->note,
            ]);
            return response()->json($note);
        }
        return redirect()->back()->with('success', 'Ghi chú đã được thêm.');
    }
    public function destroy(Note $note)
    {
        $note->delete();
    
        return response()->json(['message' => 'Ghi chú đã được xóa.']);
    }
    
    public function update(Request $request, Note $note)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:255',
        ]);

        $note->update($validated);

        return response()->json(['message' => 'Ghi chú đã được cập nhật.']);
    }

    public function index($campaignId)
    {
        $note = Note::where('campaign_id', $campaignId)->get();
        return view('note.index', compact('note', 'campaignId'));
    }
}
