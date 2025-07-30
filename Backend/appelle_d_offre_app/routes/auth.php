<?php

use App\Events\CallAccepted;
use Illuminate\Http\Request;
use App\Events\CallRequested;
use App\Events\SignalReceived;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\DomainesController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\SoumissionController;
use App\Http\Controllers\Auth\MessageController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\AppelleOffresController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest')
                ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('guest')
                ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware('auth:sanctum')
                ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware('auth:sanctum')
                ->name('verification.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('logout');


Route::middleware('auth:sanctum')->put('/profil', [ProfileController::class, 'update']);
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);


Route::middleware('auth:sanctum')->group(function () {
Route::apiResource('appels', AppelleOffresController::class);});
Route::middleware('auth:sanctum')->get('/appelle_offres/user', [AppelleOffresController::class, 'userAppels']);



Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('contrats', ContratController::class);
});

Route::middleware('auth:sanctum')->get('/domaines', [DomainesController::class, 'index']);


Route::middleware('auth:sanctum')->get('/users', [UserController::class, 'index']);
Route::middleware('auth:sanctum')->patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);



Route::middleware('auth:sanctum')->get('/mes-soumissions', [SoumissionController::class, 'mesSoumissions']);
Route::middleware('auth:sanctum')->get('/soumissions/choisies', [SoumissionController::class, 'soumissionsChoisies']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('soumissions', SoumissionController::class);
});


Route::middleware('auth:sanctum')->delete('/soumissions/{id}', [SoumissionController::class, 'destroy']);

Route::middleware('auth:sanctum')->get('/soumissions/verifier/{idAppel}', [SoumissionController::class, 'aDejaSoumis']);
Route::get('/appels/{idAppel}/soumissions', [SoumissionController::class, 'getSoumissionsByAppel']);
Route::post('/soumissions/{id}/choisir', [SoumissionController::class, 'choisir']);

Route::post('/soumissions/{id}/generer-contrat', [ContratController::class, 'genererContratPourSoumission']);
Route::get('/contrat/generer/{soumission}', [ContratController::class, 'genererPDF']);




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/messages/{receiverId}', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::patch('/messages/{id}/seen', [MessageController::class, 'markAsSeen']);
});


Broadcast::routes(['middleware' => ['auth:sanctum']]);


Route::post('/video-call/request', function (Request $request) {
    broadcast(new CallRequested(
        $request->caller_id,
        $request->receiver_id
    ))->toOthers();

    return response()->json(['status' => 'Call requested']);
});


Route::post('/video-call/accept', function (Request $request) {
    broadcast(new CallAccepted(
        $request->caller_id,
        $request->receiver_id
    ))->toOthers();

    return response()->json(['status' => 'Call accepted']);
});

Route::post('/video-call/signal', function (Request $request) {
    broadcast(new SignalReceived(
        $request->from,
        $request->type,
        $request->data
    ))->toOthers();

    return response()->json(['status' => 'Signal sent']);
});


Route::post('/soumissions/{id}/scoring', [SoumissionController::class, 'scoring']);
Route::middleware('auth:sanctum')->post('/chatbot/create-offre', [ChatbotController::class, 'generateAndCreateOffer']);
Route::post('/generate-soumission', [ChatbotController::class, 'genererSoumission']);


//routes pour chatboot
// Génération de soumission
Route::post('/chatbot/generate-soumission', [ChatbotController::class, 'generateSoumission']);

// Création d’appel d’offre depuis prompt
Route::middleware('auth:sanctum')->post('/chatbot/create-appel-offre', [ChatbotController::class, 'createAppelOffreFromPrompt']);



// Vérifier si la deadline est passée
Route::get('/chatbot/check-deadline/{id}', [ChatbotController::class, 'checkDeadline']);

// Vérifier si un contrat est généré
Route::get('/chatbot/contrat-existe/{id}', [ChatbotController::class, 'isContratGenere']);




// Aide à la rédaction

Route::post('/chatbot/aide-redaction-soumission', [ChatbotController::class, 'aideRedactionSoumission']);
Route::get('/chatbot/appels-recents', [ChatbotController::class, 'appelsRecents']);


//route unifie pour toutes ces requettes 

Route::post('/chatbot/unified', [ChatbotController::class, 'handleUnifiedChat']);
