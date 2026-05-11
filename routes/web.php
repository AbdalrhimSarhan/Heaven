<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/test-redis', function () {
    // تخزين قيمة في Redis لمدة 10 ثوانٍ
    Redis::set('user_message', 'Redis is working fine!');
    
    // استرجاع القيمة
    return Redis::get('user_message');
});

Route::get('/', function () {
    return view('welcome');
});
