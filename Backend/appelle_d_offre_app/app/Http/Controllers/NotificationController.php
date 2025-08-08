<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // 🔁 Récupérer les notifications de l'utilisateur connecté
    public function index()
    {
        return Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
    }

    // ✅ Marquer comme lue une notification
    public function markAsRead($id)
    {
        $notif = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notif->is_read = true;
        $notif->save();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
{
    $userId = auth()->id();

    Notification::where('user_id', $userId)
        ->where('is_read', false)
        ->update(['is_read' => true]);

    return response()->json(['message' => '✅ Toutes les notifications ont été marquées comme lues.']);
}
}
