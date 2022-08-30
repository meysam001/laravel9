<?php

use Illuminate\Support\Facades\Route;
use Snappshop\NotificationClient\Facades\Notification;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return 'test';
});

Route::get('/sn', function () {
//    dd('ssss');
    $message = 'Body message';
    $cellPhones = ['09121234567'];
    $agent = 'SnappShop';
    $additionalData = ['order_id' => 1];
    for($i=0;$i<100;$i++){
        Notification::smsDirect($message, $cellPhones, $agent, $additionalData);
    }
    return 'done';
});
