<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Notifications extends Controller
{

    public function notifications()
    {
        return Auth::user()->notifications;
    }

    public function unReadNotifications()
    {
        return Auth::user()->unreadNotifications;
    }

    public function readNotifications()
    {
        return Auth::user()->unreadNotifications->markAsRead();
    }

    public function markAsRead($id)
    {
        Auth::user()->notifications->where('id', $id)->markAsRead();
        return response()->json(['message' => 'Notification marked as read']);
    }
}
