<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications;
use App\Alert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    //

    public function getList(Request $request) {
      try {
        $all = [];
        $all = Notifications::where('status','unread')->where('vendeurs',Auth::user()->username)->orderBy('created_at','desc')->limit(4)->get();
        $read = Notifications::where('status','read')->where('vendeurs',Auth::user()->username)->orderBy('created_at','desc')->get();
        $unread = Notifications::where('status','unread')->where('vendeurs',Auth::user()->username)->orderBy('created_at','desc')->get();

        $all->each(function ($element , $index) {
          $element->humanDate();
        });

        $unread->each(function ($element, $index) {
          $element->humanDate();
        });

        $read->each(function ($element , $index) {
          $element->humanDate();
        });


        return response()->json([
          'all' =>  $all,
          'count' =>  $unread->count(),
          'all_read'  => $read,
          'all_unread'  => $unread
        ]);
      } catch (AppException $e) {
        header("unprocessible entity",true,422);
        die($e->getMessage());
      }

    }

    public function markAsRead(Request $request) {
      try {
        $notification = Notifications::find($request->input('ref-0'));
        if($notification) {
          $notification->status = 'read';
          $notification->save();
          return response()->json('done');
        } else {
          throw new AppException("Erreur !");
        }
        return response()->json($alert);
      } catch (AppException $e) {
        header("unprocessible entity",true,422);
        die($e->getMessage());
      }

    }
}
