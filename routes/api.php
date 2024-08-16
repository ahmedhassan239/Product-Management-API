<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// This will include the default routes for password reset
Auth::routes(['verify' => true]);
Route::post('users/register', [UserController::class, 'register']);
Route::post('users/login', [UserController::class, 'login']);
/*You Need To Configure Real Email */
Route::post('/users/reset-password', [UserController::class, 'sendResetLinkEmail']);
Route::post('/users/reset-password/confirm', [UserController::class, 'resetPassword']);

// Protected routes (authentication required)
Route::middleware('jwt.auth')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'getUserDetails']);
    Route::post('/users/logout', [UserController::class, 'logout']);
    Route::post('/users/refresh', [UserController::class, 'refresh']);
    Route::middleware('role:Super Admin')->group(function () {
        Route::put('/users/{id}', [UserController::class, 'updateUser']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
});






Route::get('/products/{id}', [ProductController::class, 'getProductDetails']);
Route::middleware('role:Super Admin')->group(function () {
    Route::post('/products', [ProductController::class, 'create']);
    Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ProductController::class, 'deleteProduct']);
});
