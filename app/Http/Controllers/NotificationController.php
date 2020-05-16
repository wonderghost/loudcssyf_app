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

    public function markAsRead(Request $request , Notifications $n) {
      try {
          $validation = $request->validate([
            'id_notify' =>  'required|string|exists:notifications,id'
          ]);

          $notify = $n->find($request->input('id_notify'));
          $notify->status = 'read';
          $notify->save();
          return response()
            ->json('done');
      } catch(AppException $e) {
          header("Erreur",true,422);
          die(json_encode($e->getMessage()));
      }   
    }
}
