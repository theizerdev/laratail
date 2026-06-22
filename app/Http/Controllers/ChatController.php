<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get all users for the chat sidebar.
     */
    public function users()
    {
        // Get all users except the currently authenticated user
        $users = User::where('id', '!=', Auth::id())
                     ->select('id', 'name', 'email')
                     ->get();

        return response()->json($users);
    }

    /**
     * Get chat history with a specific user.
     */
    public function messages($userId)
    {
        $currentUserId = Auth::id();

        $messages = Message::where(function ($query) use ($currentUserId, $userId) {
                $query->where('sender_id', $currentUserId)
                      ->where('receiver_id', $userId);
            })
            ->orWhere(function ($query) use ($currentUserId, $userId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $currentUserId);
            })
            ->with(['sender:id,name', 'receiver:id,name'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Send a new message.
     */
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'body' => $request->body,
        ]);

        $message->load(['sender:id,name', 'receiver:id,name']);

        // Broadcast the event
        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }
}
