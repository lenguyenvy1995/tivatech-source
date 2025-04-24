<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'quote_id' => 'required|exists:quotes,id',
            'content' => 'required|string'
        ]);
    
        $message = Message::create([
            'quote_id' => $request->quote_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);
        $html='';
        $html.= '<div class="direct-chat-msg right"> <div class="direct-chat-infos clearfix"> <span class="direct-chat-name float-right">';
        $html.= $message->content;
        $html.= '</span> <span class="direct-chat-timestamp float-left">';
        $html.= $message->created_at->format('H:i d-m-Y') ;
        $html.= '</span></div><div class="direct-chat-text">'. $message->content .'</div></div>';        
        return response()->json(['html' => $html]);
    }
    
}
