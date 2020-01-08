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
        $_alert = Alert::where('vendeurs',$request->input('ref-0'))->where('status','unread')->orderBy('created_at','desc')->limit(4)->get();
        $alert = Alert::where('vendeurs',$request->input('ref-0'))->where('status','unread')->orderBy('created_at','desc')->get();
        $alert_read = Alert::where('vendeurs',$request->input('ref-0'))->where('status','read')->orderBy('created_at','desc')->get();
        $alert_unread = Alert::where('vendeurs',$request->input('ref-0'))->where('status','unread')->orderBy('created_at','desc')->get();
        $all = [];
        $_all = [];
        $all = $this->organizeNotification($_alert);
        $read = $this->organizeNotification($alert_read);
        $unread = $this->organizeNotification($alert_unread);
        return response()->json([
          'all' =>  $all,
          'count' =>  $alert->count(),
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
        $alert = Alert::where([
          'vendeurs'  =>  Auth::user()->username,
          'notification'  =>  $request->input('ref-0'),
          'status'  =>  'unread'
        ])->first();
        if($alert) {
          Alert::where([
            'vendeurs'  =>  Auth::user()->username,
            'notification'  =>  $request->input('ref-0'),
            'status'  =>  'unread'
          ])->update([
            'status'  =>  'read'
          ]);
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

    public function organizeNotification($data) {
      $all=[];
      foreach($data as $key => $value) {
        $date = new Carbon($value->created_at);
        $date->setLocale('fr_FR');

        $all[$key] = [
          'id'  =>  $value->notification,
          'title' =>  $value->notifications()->titre,
          'description' =>  $value->notifications()->description,
          'status'  =>  $value->status,
          'date'  =>  $date->diffForHumans()
        ];
      }
      return $all;
    }
}
