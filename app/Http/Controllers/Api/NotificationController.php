<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications=$request->user()
        ->notifications()
        ->latest()
        ->get();

        return response()->json($notifications);
    }
    public function unreadCount(Request $request)
    {
        $count = $request->user()
        ->unreadNotifications()
        ->count();
        return response()->json([
            'count'=>$count,
        ]);
    }

    public function markAsRead(Request $request, string $id)
{
    $notification = $request->user()
        ->notifications()
        ->where('id', $id)
        ->firstOrFail();

    $notification->markAsRead();

    return response()->json([
        'message' => 'Notification marked as read.',
    ]);
}

}
