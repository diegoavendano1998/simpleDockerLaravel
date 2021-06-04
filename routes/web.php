<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;

Route::get('/', function () {  
  return view('welcome');  
});

Route::get('/email', function(Request $request) {  
  $email = App\Email::updateOrCreate($request->all());

  \Mail::raw('You have been subscribed', function($message) use ($email) {  
      $message->to($email->email);  
  });

  return sprintf('Thanks for submitting your email, %s <a href="/">home</a>', $email->email);  
});
