<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

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

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::apiResource('brands',BrandController::class);
Route::get('brands/{brand}/products',[BrandController::class,'products']);
Route::apiResource('categories',CategoryController::class);
Route::get('categories/{category}/products',[CategoryController::class,'products']);
Route::get('categories/{category}/children',[CategoryController::class,'children']);
Route::get('categories/{category}/parent',[CategoryController::class,'parent']);

Route::apiResource('products',ProductController::class);
