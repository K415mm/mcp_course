<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\ContentController as AdminContent;
use App\Http\Controllers\Admin\MediaController as AdminMedia;
use App\Http\Controllers\Admin\UserController as AdminUsers;
use App\Http\Controllers\Admin\QuizController as AdminQuiz;
use App\Http\Controllers\CsController;
use App\Http\Controllers\CsApiController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

// ── Guest-only routes ──────────────────────────────────────────────────────
Route::get('/2fa-challenge', [\App\Http\Controllers\TwoFactorController::class, 'challenge'])->name('2fa.challenge');
Route::post('/2fa-challenge', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('2fa.verify');

// ── Public Invitation routes (no auth required) ──────────────────────────────
Route::get('/invite/{token}', [\App\Http\Controllers\InvitationController::class, 'accept'])->name('invite.accept');
Route::post('/invite/{token}', [\App\Http\Controllers\InvitationController::class, 'register'])->name('invite.register');



Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ── Auth-required routes ───────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification Routes
    Route::get('/email/verify', [AuthController::class, 'verifyNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyHandler'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'verifyResend'])->middleware('throttle:6,1')->name('verification.send');

    // ── Verified Auth routes ───────────────────────────────────────────────
    Route::middleware('verified')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [HomeController::class, 'index'])->name('home');


    // Student Course Catalog
    Route::name('courses.')->prefix('courses')->group(function () {
        Route::get('/', [\App\Http\Controllers\CourseCatalogController::class, 'index'])->name('index');
        Route::get('/{course}', [\App\Http\Controllers\CourseCatalogController::class, 'show'])->name('show');
    });

    // Course (legacy specific module routing)
    Route::prefix('course')->name('course.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::get('/{moduleSlug}', [CourseController::class, 'module'])->name('module');
        Route::get('/{moduleSlug}/{section}/{lessonSlug}', [CourseController::class, 'lesson'])->name('lesson');
        Route::post('/progress', [CourseController::class, 'markProgress'])->name('progress');
        Route::post('/quiz-grade', [CourseController::class, 'gradeQuiz'])->name('quiz.grade');
    });

    // Lesson Progress (Smart Verification)
    Route::prefix('progress')->name('progress.')->group(function () {
        Route::post('/ping', [\App\Http\Controllers\LessonProgressController::class, 'ping'])->name('ping');
        Route::post('/complete', [\App\Http\Controllers\LessonProgressController::class, 'complete'])->name('complete');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Notes
    Route::prefix('notes')->name('notes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\NoteController::class, 'index'])->name('index');
        Route::get('/view/{note}', [\App\Http\Controllers\NoteController::class, 'show'])->name('show');
        Route::post('/create', [\App\Http\Controllers\NoteController::class, 'store'])->name('store');
        Route::put('/{note}', [\App\Http\Controllers\NoteController::class, 'update'])->name('update');
        Route::delete('/{note}', [\App\Http\Controllers\NoteController::class, 'destroy'])->name('destroy');
        Route::get('/module/{moduleSlug}', [\App\Http\Controllers\NoteController::class, 'forModule'])->name('forModule');
    });

    // Diagrams (instructor creates, students view)
    Route::prefix('diagrams')->name('diagrams.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DiagramController::class, 'index'])->name('index');
        Route::get('/new', [\App\Http\Controllers\DiagramController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\DiagramController::class, 'store'])->name('store');
        Route::get('/{diagram}/edit', [\App\Http\Controllers\DiagramController::class, 'edit'])->name('edit');
        Route::put('/{diagram}', [\App\Http\Controllers\DiagramController::class, 'update'])->name('update');
        Route::post('/{diagram}/publish', [\App\Http\Controllers\DiagramController::class, 'publish'])->name('publish');
        Route::delete('/{diagram}', [\App\Http\Controllers\DiagramController::class, 'destroy'])->name('destroy');
        Route::get('/{diagram}/file', [\App\Http\Controllers\DiagramController::class, 'file'])->name('file');
        Route::get('/{diagram}', [\App\Http\Controllers\DiagramController::class, 'show'])->name('show');
    });

    // Module Completion
    Route::post('/modules/{moduleSlug}/complete', [\App\Http\Controllers\ModuleCompletionController::class, 'mark'])->name('modules.complete');
    Route::get('/my-completions', [\App\Http\Controllers\ModuleCompletionController::class, 'index'])->name('modules.completions');

    // 2FA Management
    Route::post('/user/two-factor-authentication', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/user/confirmed-two-factor-authentication', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::delete('/user/two-factor-authentication', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('two-factor.disable');

    // ── CyberBreach Game ─────────────────────────────────────────────────
    Route::prefix('game')->name('game.')->group(function () {
        Route::get('/', [\App\Http\Controllers\GameController::class, 'lobby'])->name('lobby');

        // Admin-only: create & manage
        Route::middleware('admin')->group(function () {
            Route::get('/create', [\App\Http\Controllers\GameController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\GameController::class, 'store'])->name('store');
            Route::get('/{session}/manage', [\App\Http\Controllers\GameController::class, 'manage'])->name('manage');
        });

        Route::get('/{session}', [\App\Http\Controllers\GameController::class, 'show'])->name('show');
        Route::post('/{session}/leave', [\App\Http\Controllers\GameController::class, 'leave'])->name('leave');

        // Game API (JSON)
        Route::prefix('/{session}/api')->name('api.')->group(function () {
            Route::get('/state', [\App\Http\Controllers\GameApiController::class, 'getGameState'])->name('state');
            Route::post('/play-card', [\App\Http\Controllers\GameApiController::class, 'playCard'])->name('playCard');
            Route::post('/draw-card', [\App\Http\Controllers\GameApiController::class, 'drawCard'])->name('drawCard');
            Route::post('/buy-shop', [\App\Http\Controllers\GameApiController::class, 'buyFromShop'])->name('buyShop');
            Route::post('/start-round', [\App\Http\Controllers\GameApiController::class, 'startRound'])->name('startRound');
            Route::post('/advance-phase', [\App\Http\Controllers\GameApiController::class, 'advancePhase'])->name('advancePhase');
            Route::post('/draw-event', [\App\Http\Controllers\GameApiController::class, 'drawEvent'])->name('drawEvent');
            Route::post('/adjust-tokens', [\App\Http\Controllers\GameApiController::class, 'adjustTokens'])->name('adjustTokens');
            Route::post('/deal-hands', [\App\Http\Controllers\GameApiController::class, 'dealHands'])->name('dealHands');
            Route::get('/card-effectiveness', [\App\Http\Controllers\GameApiController::class, 'cardEffectiveness'])->name('cardEffectiveness');
            Route::post('/end-game', [\App\Http\Controllers\GameApiController::class, 'endGame'])->name('endGame');
            Route::post('/assign-player', [\App\Http\Controllers\GameApiController::class, 'assignPlayer'])->name('assignPlayer');
            Route::post('/remove-player', [\App\Http\Controllers\GameApiController::class, 'removePlayer'])->name('removePlayer');
        });
    });

    // ── CARTHAGE SHIELD — Public join routes (auth optional) ─────────────────
    // These are inside 'verified' middleware so users need accounts,
    // BUT the join/participant view itself handles anonymous guests via session.
    Route::prefix('cs')->name('cs.')->group(function () {
        Route::get('/{code}', [CsController::class, 'show'])->name('show');            // participant join
        Route::get('/{code}/dashboard', [CsController::class, 'dashboard'])->name('dashboard'); // big screen (public)
        Route::get('/{code}/moderator', [CsController::class, 'moderator'])->name('moderator'); // moderator console

        // CS API endpoints (used by all 3 views, CSRF-protected)
        Route::prefix('/{code}/api')->name('api.')->group(function () {
            Route::get('/state',             [CsApiController::class, 'state'])->name('state');
            Route::get('/bank',              [CsApiController::class, 'getBank'])->name('bank');
            Route::post('/join',             [CsApiController::class, 'join'])->name('join');
            Route::post('/heartbeat',        [CsApiController::class, 'heartbeat'])->name('heartbeat');
            Route::post('/timer/start',      [CsApiController::class, 'timerStart'])->name('timer.start');
            Route::post('/timer/pause',      [CsApiController::class, 'timerPause'])->name('timer.pause');
            Route::post('/timer/reset',      [CsApiController::class, 'timerReset'])->name('timer.reset');
            Route::post('/timer/set',        [CsApiController::class, 'timerSet'])->name('timer.set');
            Route::post('/phase/advance',    [CsApiController::class, 'phaseAdvance'])->name('phase.advance');
            Route::post('/phase/goto',       [CsApiController::class, 'phaseGoto'])->name('phase.goto');
            Route::post('/score/{teamId}',   [CsApiController::class, 'scoreAdjust'])->name('score.adjust');
            Route::post('/broadcast',        [CsApiController::class, 'broadcast'])->name('broadcast');
            Route::post('/phantom',          [CsApiController::class, 'phantom'])->name('phantom');
            Route::post('/inject/{injectId}',[CsApiController::class, 'inject'])->name('inject');
            Route::post('/atmosphere',       [CsApiController::class, 'atmosphere'])->name('atmosphere');
            Route::post('/vote/open',        [CsApiController::class, 'voteOpen'])->name('vote.open');
            Route::post('/vote/close',       [CsApiController::class, 'voteClose'])->name('vote.close');
            Route::post('/vote/submit',      [CsApiController::class, 'voteSubmit'])->name('vote.submit');
            Route::post('/quiz/open',        [CsApiController::class, 'quizOpen'])->name('quiz.open');
            Route::post('/quiz/close',       [CsApiController::class, 'quizClose'])->name('quiz.close');
            Route::post('/quiz/submit',      [CsApiController::class, 'quizSubmit'])->name('quiz.submit');
            Route::post('/media/save',       [CsApiController::class, 'mediaSave'])->name('media.save');
            Route::post('/media/upload',     [CsApiController::class, 'mediaUpload'])->name('media.upload');
            Route::post('/media/inject',     [CsApiController::class, 'mediaInject'])->name('media.inject');
            Route::post('/media/delete',     [CsApiController::class, 'mediaDelete'])->name('media.delete');
            Route::post('/decision',         [CsApiController::class, 'decision'])->name('decision');
            Route::post('/decision/{id}/award',[CsApiController::class, 'awardScore'])->name('decision.award');
            Route::post('/badge/{teamId}',   [CsApiController::class, 'badgeAward'])->name('badge.award');
            Route::post('/end',              [CsApiController::class, 'end'])->name('end');
        });
    });

    // ── CARTHAGE SHIELD — Admin session management + all other Admin routes ───────
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        // ── CARTHAGE SHIELD sessions ──
        Route::get('/cs',  [CsController::class, 'index'])->name('cs.index');
        Route::post('/cs', [CsController::class, 'store'])->name('cs.store');

        Route::get('/cs/entities', [\App\Http\Controllers\Admin\CsEntityController::class, 'index'])->name('cs.entities.index');
        Route::put('/cs/entities/{entity}', [\App\Http\Controllers\Admin\CsEntityController::class, 'update'])->name('cs.entities.update');

        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');

        Route::get('courses', [\App\Http\Controllers\Admin\AdminCourseController::class, 'index'])->name('courses.index');
        Route::post('courses/assign-class', [\App\Http\Controllers\Admin\AdminCourseController::class, 'assignToClass'])->name('courses.assignClasses');
        Route::post('courses/unassign-class', [\App\Http\Controllers\Admin\AdminCourseController::class, 'unassignFromClass'])->name('courses.unassignClasses');
        Route::post('courses/assign-user', [\App\Http\Controllers\Admin\AdminCourseController::class, 'assignToStudent'])->name('courses.assignStudent');
        
        Route::resource('classes', \App\Http\Controllers\Admin\AdminClassController::class);
        Route::post('classes/{class}/add-user', [\App\Http\Controllers\Admin\AdminClassController::class, 'addStudent'])->name('classes.addStudent');
        Route::post('classes/{class}/remove-user', [\App\Http\Controllers\Admin\AdminClassController::class, 'removeStudent'])->name('classes.removeStudent');

        // Global Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/roles', [\App\Http\Controllers\Admin\SettingsController::class, 'roles'])->name('settings.roles');
        Route::post('/settings/roles', [\App\Http\Controllers\Admin\SettingsController::class, 'updateRoles'])->name('settings.updateRoles');

        // Content (CRUD .md files)
        Route::prefix('content')->name('content.')->group(function () {
            Route::get('/', [AdminContent::class, 'index'])->name('index');
            Route::get('/create', [AdminContent::class, 'create'])->name('create');
            Route::post('/', [AdminContent::class, 'store'])->name('store');
            Route::post('/toggle-status', [AdminContent::class, 'toggleStatus'])->name('toggleStatus');
            Route::post('/bulk-publish', [AdminContent::class, 'bulkPublish'])->name('bulkPublish');
            Route::get('/{module}/{file}/edit', [AdminContent::class, 'edit'])->name('edit');
            Route::put('/{module}/{file}', [AdminContent::class, 'update'])->name('update');
            Route::delete('/destroy', [AdminContent::class, 'destroy'])->name('destroy');
        });

        // Media Library
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [AdminMedia::class, 'index'])->name('index');
            Route::post('/upload', [AdminMedia::class, 'upload'])->name('upload');
            Route::delete('/{media}', [AdminMedia::class, 'destroy'])->name('destroy');
        });

        // Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUsers::class, 'index'])->name('index');
            Route::post('/{user}/role', [AdminUsers::class, 'updateRole'])->name('role');
            Route::put('/{user}', [AdminUsers::class, 'update'])->name('update');
            Route::post('/{user}/password', [AdminUsers::class, 'updatePassword'])->name('password');
            Route::post('/{user}/toggle-ban', [AdminUsers::class, 'toggleBan'])->name('toggleBan');
            Route::delete('/{user}', [AdminUsers::class, 'destroy'])->name('destroy');
            Route::get('/{user}/progress', [AdminUsers::class, 'progress'])->name('progress');
        });

        // Quiz Builder
        Route::get('/quiz/{module}/{lesson}', [AdminQuiz::class, 'edit'])->name('quiz.edit');
        Route::put('/quiz/{module}/{lesson}', [AdminQuiz::class, 'update'])->name('quiz.update');

        // Invitations
        Route::prefix('invitations')->name('invitations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\InvitationController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\InvitationController::class, 'store'])->name('store');
            Route::post('/bulk', [\App\Http\Controllers\Admin\InvitationController::class, 'bulkStore'])->name('bulk');
            Route::post('/{invitation}/resend', [\App\Http\Controllers\Admin\InvitationController::class, 'resend'])->name('resend');
            Route::delete('/{invitation}', [\App\Http\Controllers\Admin\InvitationController::class, 'destroy'])->name('destroy');
        });
    });
    
    }); // End verified middleware group
});
Route::get('/cs-test', function() { return view('cs.index', ['sessions' => App\Models\CsSession::paginate(10), 'scenarios' => App\Models\CsScenario::list()]); });
