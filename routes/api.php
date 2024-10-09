<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\v1\EntryController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\RoleGroupController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\v1\InquiryController;
use App\Http\Controllers\API\SystemConfigController;
use App\Http\Controllers\API\v1\ImpairmentController;
use App\Http\Controllers\Api\MonitoringConfigController;

Route::middleware(['api'])->group( function () {
    // Authentication routes
    Route::group(['prefix' => 'auth'], function ($router) {
        Route::post('login', [AuthController::class, 'login'])->name('api.login');
        Route::middleware(['auth:api'])->group( function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('me', [AuthController::class, 'me']);
        });
    });

    // Custom Auth API routes using JWT token
    Route::middleware(['custom.auth'])->group( function () {
        // MID API resources routes
        Route::apiResources([
            'users' => UserController::class,
            'entries' => EntryController::class,
            'impairments' => ImpairmentController::class,
            // 'inquiries' => InquiryController::class,
        ]);

        // Route::get('searchBar', [UserController::class, 'searchBar'])->middleware('role:admin');

        Route::group(['prefix' => 'audits'], function () {
            Route::get('/entry', function () {
                $entry = App\Models\Entry::find(1388360);
                $data = $entry->audits()->first();
                return response()->json($data);
            });
            Route::get('/impairment', function () {
                $impairment = App\Models\Impairment::find(4908444);
                $data = $impairment->audits;
                return response()->json($data);
            });
        });

        // Analytics
        Route::group(['prefix' => 'analytics'], function () {
            Route::get('/count', [AnalyticsController::class, 'getCount']);
            Route::get('/years', [AnalyticsController::class, 'getYears']);
            Route::get('/yearly-data', [AnalyticsController::class, 'getYearlyData']);
            Route::get('/months', [AnalyticsController::class, 'getMonths']);
            Route::get('/monthly-data', [AnalyticsController::class, 'getMonthlyData']);
        });

        Route::group(['prefix' => 'inquiries'], function () {
            Route::post('/search', [InquiryController::class, 'search']);
            Route::post('/count-entries', [InquiryController::class, 'countEntries']);
            // Route::post('/count-impairments', [InquiryController::class, 'countImpairments']);
        });
    });

});

// API routes that use web auth guard for admin module api requests
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('search', [InquiryController::class, 'search']);
    // Settings
    Route::group(['prefix' => 'settings'], function () {
        Route::apiResources([
            'users' => UserController::class,
            'roles' => RoleController::class,
            'role-groups' => RoleGroupController::class,
            'permissions' => PermissionController::class,
            'monitoring-configs' => MonitoringConfigController::class,
        ]);
        Route::post('reset-monitoring', [SystemConfigController::class, 'resetMonitoringData']);
    });
});
