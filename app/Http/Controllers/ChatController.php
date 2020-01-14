<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Traits\Similarity;
use App\Exceptions\AppException;

class ChatController extends Controller
{
    //
    use Similarity;

    public function UserList() {
      try {
        $users = User::where('type','<>','admin')->orderBy('localisation','asc')->get();
        return response()->json($users);
      } catch (AppException $e) {
        header("unprocessible entity",true,422);
        die(json_encode($e->getMessage()));
      }

    }
}
