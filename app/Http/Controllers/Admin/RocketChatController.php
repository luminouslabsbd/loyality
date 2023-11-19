<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RocketChatController extends Controller
{
    public function index(){
        // dd("ddd");
        $data =  'data';
        return view('admin.rocket_chat.index',compact('data'));
    }
}
