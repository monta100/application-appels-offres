<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Events\NotificationEvent;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    public function index($receiverId)
    {
        $userId = auth()->user()->idUser;

        $messages = Message::where(function($query) use ($receiverId, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $receiverId);
        })->orWhere(function($query) use ($receiverId, $userId) {
            $query->where('sender_id', $receiverId)
                  ->where('receiver_id', $userId);
        })->orderBy('created_at')->get();

        return response()->json($messages);
    }

   public function store(Request $request)
{
    $request->validate([
        'receiver_id' => 'required|exists:users,idUser',
        'content' => 'nullable|required_without:file|string',
        'file' => 'nullable|required_without:content|file|max:5120',
    ]);

    $filePath = null;

    if ($request->hasFile('file')) {
        $filePath = $request->file('file')->store('uploads/messages', 'public'); // storage/app/public/uploads/messages
    }

    $message = Message::create([
        'sender_id' => auth()->user()->idUser,
        'receiver_id' => $request->receiver_id,
        'content' => $request->content,
        'file_path' => $filePath,
    ]);

    // ✅ Notifier le destinataire
    $receiver = User::find($request->receiver_id);
    $notif = Notification::create([
        'user_id' => $receiver->idUser,
        'title' => '💬 Nouveau message',
        'message' => 'Vous avez reçu un message de ' . auth()->user()->prenom . ' ' . auth()->user()->nom,
        'type' => 'chat',
    ]);

    // ✅ Broadcast message et notification
    broadcast(new MessageSent($message))->toOthers();
    broadcast(new NotificationEvent($notif))->toOthers();

    return response()->json($message, 201);
}

    public function markAsSeen($id)
    {
        $message = Message::findOrFail($id);

        if ($message->receiver_id !== auth()->user()->idUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->seen = true;
        $message->save();

        return response()->json(['success' => true]);
    }
}
