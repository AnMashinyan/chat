<?php
//
//use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\SampleController;
//
///*
//|--------------------------------------------------------------------------
//| Web Routes
//|--------------------------------------------------------------------------
//|
//| Here is where you can register web routes for your application. These
//| routes are loaded by the RouteServiceProvider within a group which
//| contains the "web" middleware group. Now create something great!
//|
//*/
//
//Route::get('/', function () {
//    return view('welcome');
//});
//Route::get('main', [App\Http\Controllers\SampleController::class,'index'])->name('login');
//Route::get('registration', [App\Http\Controllers\SampleController::class,'registration'])->name('registration');
//Route::post('validate_registration',[App\Http\Controllers\SampleController::class,'validate_registration'])->name('validate_registration');
//Route::post('validate_login',[App\Http\Controllers\SampleController::class,'validate_login'])->name('validate_login');
//Route::get('profile',[App\Http\Controllers\SampleController::class,'profile'])->name('profile');
//Route::get('logout',[App\Http\Controllers\SampleController::class,"logout"])->name('logout');
//Route::get('search',[App\Http\Controllers\SampleController::class,"search"])->name('search');
//Route::get("search",[App\Http\Controllers\SampleController::class,'search']);
//
//Route::group(['middleware' => ['auth']], function () {
//
//    Route::get('/chat', [App\Http\Controllers\ChatController::class,'create'])->name('chat');
//
//});


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SampleController;

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
    return view('login');
});
//Route::get('search',[App\Http\Controllers\SampleController::class,"search"])->name('search');

Route::controller(SampleController::class)->group(function () {

    Route::get('login', 'index')->name('login');

    Route::get('registration', 'registration')->name('registration');

    Route::get('logout', 'logout')->name('logout');

    Route::post('validate_registration', 'validate_registration')->name('sample.validate_registration');

    Route::post('validate_login', 'validate_login')->name('sample.validate_login');

    Route::get('dashboard', 'dashboard')->name('dashboard');

});
