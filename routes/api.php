<?php

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\{Route, RateLimiter};
use App\Http\Controllers\Api\{
  AdminController,
  AuthController,
  PartnerController,
  ProcurementController,
  SecurityController
};

RateLimiter::for('api', function (Request $request) {
  return Limit::perMinute(60)->by($request->ip());
});

Route::middleware(['guest', 'throttle:60,1'])->group(function () {
  // Authenticate
  Route::controller(AuthController::class)->group(function () {
    Route::post('/auth/login', 'login');
  });
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
  // Auth
  Route::post('/auth/logout', [AuthController::class, 'logout']);
  Route::group(['prefix' => 'apps'], function () {});
});

Route::group(['prefix' => 'apps'], function () {
  // Location
  Route::controller(AdminController::class)->group(function () {
    Route::get('/location/list', 'list');
    Route::post('/location/search', 'search');
    Route::post('/location/add', 'add');
    Route::get('/location/edit/{id}', 'edit');
    Route::put('/location/update/{id}', 'update');
    Route::delete('/location/delete/{id}', 'destroy');
  });
  // Partner
  Route::controller(PartnerController::class)->group(function () {
    Route::get('/partner/list', 'listAccessLevel');
    Route::post('/partner/search', 'search');
    Route::post('/partner/add', 'add');
    Route::get('/partner/edit/{id}', 'edit');
    Route::put('/partner/update/{id}', 'update');
    Route::delete('/partner/delete/{id}', 'destroy');
    Route::post('/partner/approval', 'approval');
    Route::put('/partner/approval/approve/{id}', 'approve');
    Route::put('/partner/approval/reject/{id}', 'reject');
  });
  // Procurement
  Route::controller(ProcurementController::class)->group(function () {
    Route::get('/procurement/list', 'listAccessLevel');
    Route::post('/procurement/search', 'search');
    Route::post('/procurement/add', 'add');
    Route::get('/procurement/edit/{id}', 'edit');
    Route::put('/procurement/update/{id}', 'update');
    Route::delete('/procurement/delete/{id}', 'destroy');
  });
  // Security
  Route::controller(SecurityController::class)->group(function () {
    Route::get('/security/list', 'listAccessLevel');
    Route::post('/security/search', 'search');
    Route::post('/security/add', 'add');
    Route::get('/security/edit/{id}', 'edit');
    Route::put('/security/update/{id}', 'update');
    Route::delete('/security/delete/{id}', 'destroy');
  });
});
