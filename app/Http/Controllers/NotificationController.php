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

    public function getList(Request $request,Notifications $n) {
      try {
        return response()
          ->json($n->where('vendeurs',$request->user()->username)->orderBy('created_at','desc')->get());
      } catch (AppException $e) {
        header("Erreur",true,422);
        die(json_encode($e->getMessage()));
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
