<?php

use App\Http\Controllers\AiUsageController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest / Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & notifications
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Campaigns (Find Projects)
    Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('campaigns/create', [CampaignController::class, 'showCreate'])->name('campaigns.create');
    Route::post('campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('campaigns/{campaign}/progress', [CampaignController::class, 'progress'])->name('campaigns.progress');
    Route::post('campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('campaigns/{campaign}/resume', [CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::post('campaigns/{campaign}/cancel', [CampaignController::class, 'cancel'])->name('campaigns.cancel');

    // Leads
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('leads/import', [LeadController::class, 'importView'])->name('leads.import');
    Route::post('leads/import', [LeadController::class, 'import'])->name('leads.import.submit');
    Route::post('leads/{lead}/bulk-status', [LeadController::class, 'bulkStatus'])->name('leads.bulk-status');
    Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::post('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
    Route::post('leads/{lead}/analyse', [LeadController::class, 'analyse'])->name('leads.analyse');
    Route::post('leads/{lead}/claim', [LeadController::class, 'claim'])->name('leads.claim');
    Route::post('leads/{lead}/notes', [LeadController::class, 'addNote'])->name('leads.add-note');

    // Pipeline (kanban)
    Route::get('pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
    Route::post('pipeline/{lead}/move', [PipelineController::class, 'move'])->name('pipeline.move');

    // Opportunities
    Route::get('opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('opportunities/{lead}', [OpportunityController::class, 'show'])->name('opportunities.show');

    // Email outreach
    Route::get('emails', [EmailController::class, 'index'])->name('emails.index');
    Route::get('emails/pending', [EmailController::class, 'pending'])->name('emails.pending');
    Route::post('emails/{lead}/generate', [EmailController::class, 'generate'])->name('emails.generate');
    Route::post('emails/{email}/approve', [EmailController::class, 'approve'])->name('emails.approve');
    Route::post('emails/{email}/send', [EmailController::class, 'send'])->name('emails.send');

    // Follow-ups
    Route::get('followups', [FollowUpController::class, 'index'])->name('followups.index');
    Route::post('followups/{followUp}/complete', [FollowUpController::class, 'complete'])->name('followups.complete');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // AI Usage
    Route::get('ai/usage', [AiUsageController::class, 'index'])->name('ai.usage');

    // Services & Settings (admin)
    Route::get('services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('services/{service}', [ServiceController::class, 'show'])->name('services.show');
    Route::post('services/{service}/rules', [ServiceController::class, 'storeRule'])->name('services.rules.store')->middleware('role:admin');
    Route::delete('services/{service}/rules/{rule}', [ServiceController::class, 'deleteRule'])->name('services.rules.destroy')->middleware('role:admin');

    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.index')->middleware('role:admin');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('role:admin');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('role:admin');
});