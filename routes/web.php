<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;


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
})->name('welcome');

Route::get('/dashboard', DashboardController::class)->middleware(['auth'])->name('dashboard');

Auth::routes();

Route::get('test',function(){
    $myfile = fopen(URL::asset("text.txt"), "r") or die("Unable to open file!");
    echo fread($myfile,filesize("text.txt"));
    fclose($myfile);
});
// Salla Auth OAuth 
Route::group(['middleware' => 'auth'], function () {
    Route::get('/oauth/redirect', [OAuthController::class, 'redirect'])->name('oauth.redirect');
    Route::get('/oauth/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
});
