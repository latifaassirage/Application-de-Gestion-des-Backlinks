<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SourceSiteController;
use App\Http\Controllers\BacklinkController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BacklinkTypeController;

Route::get('/backlink-types', [BacklinkTypeController::class, 'index']);
Route::post('/backlink-types', [BacklinkTypeController::class, 'store']);
Route::delete('/backlink-types/{id}', [BacklinkTypeController::class, 'destroy']);

// Auth
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');
Route::get('/me',[AuthController::class,'me'])->middleware('auth:sanctum');

// Password Reset
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Profile
Route::put('/profile',[AuthController::class,'updateProfile'])->middleware('auth:sanctum');

// Read-only routes for Staff and Admin (dashboard stats)
Route::middleware(['auth:sanctum', 'staff'])->group(function(){
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/all-clients', [ClientController::class, 'all']);
    Route::get('/unique-clients', [ClientController::class, 'unique']);
    Route::get('/sources', [SourceSiteController::class, 'index']);
    Route::get('/all-sources', [SourceSiteController::class, 'all']);
    Route::get('/grouped-sources', [SourceSiteController::class, 'grouped']);
    Route::get('/backlinks', [BacklinkController::class, 'index']);
    Route::get('/all-backlinks', [BacklinkController::class, 'all']);
    Route::get('/dashboard-stats', [BacklinkController::class, 'dashboardStats']);
    Route::get('/summary-sources', [BacklinkController::class, 'getSummarySources']);
    Route::get('/all-summary-sources', [BacklinkController::class, 'getAllSummarySources']);
    Route::put('/summary-sources/{id}', [BacklinkController::class, 'updateSummarySource']);
    Route::delete('/summary-sources/{id}', [BacklinkController::class, 'deleteSummarySource']);
    
    // Report generation routes (moved here for testing)
    Route::post('/reports/summary-pdf', [ReportController::class, 'generateSummaryPdf']);
    
    // Route de test pour diagnostiquer le problème
    Route::post('/test-pdf', function() {
        return response()->json(['message' => 'Test route works']);
    });
});

// Admin only routes
Route::middleware(['auth:sanctum', 'admin'])->group(function(){
    Route::apiResource('clients', ClientController::class)->except('index');
    Route::apiResource('sources', SourceSiteController::class)->except('index');
    
    // Report generation routes
    Route::post('/reports/pdf/{clientId?}', [ReportController::class, 'generatePdf']);
    Route::post('/reports/excel/{clientId?}', [ReportController::class, 'generateExcel']);
});

// Staff and Admin routes (backlinks management)
Route::middleware(['auth:sanctum', 'staff'])->group(function(){
    Route::apiResource('backlinks', BacklinkController::class);
    Route::post('/backlinks/import', [BacklinkController::class, 'importBacklinks']);
    Route::post('/sources/import', [BacklinkController::class, 'importSourceSites']);
    Route::post('/summary/import', [BacklinkController::class, 'importSummary']);
});
