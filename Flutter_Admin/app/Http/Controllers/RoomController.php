<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function viewCreatePage(){

        return view('room.create');
    }
}
