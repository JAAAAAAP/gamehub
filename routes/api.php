<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DashBoardStatController;
use App\Http\Controllers\Api\V1\ReviewsController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\VisitorController;

Route::prefix('V1')->group(function (): void {

    Route::get('/search', [SearchController::class, 'search']);

    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/{slug}', [GameController::class, 'show']);

    // Route::get( '/indexGames', [GameController::class,'indexGames']);
    Route::get('/newestgames', [GameController::class, 'NewestGames']);
    Route::get('/mostdownloadgames', [GameController::class, 'mostDowloadGames']);
    Route::get('/mostratinggames', [GameController::class, 'MostRatingGames']);

    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/category/{category}', [CategoryController::class, 'show']);

    Route::post('/recordVisit', [VisitorController::class, 'recordVisit']);
    Route::get('/getTotalVisitCount', [VisitorController::class, 'getTotalVisitCount']);

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('/getme',  function (Request $request) {
            $user = $request->user();
            return response()->json([
                'id' => Crypt::encrypt($user->id),
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);
        });


        Route::get('/dashboardstat', [DashBoardStatController::class, 'getStats']);
        Route::get('/userstat', [DashBoardStatController::class, 'getUserStat']);
        Route::get('/categoriesstat', [DashBoardStatController::class, 'getCategoriesStat']);
        Route::get('/gamestat', [DashBoardStatController::class, 'getGameStat']);

        Route::put('/updaterole/{id}', [UserController::class, 'updateRole']);
        Route::post('/createuser', [UserController::class, 'createUser']);
        Route::delete('/deleteuser/{id}', [UserController::class, 'DeleteUser']);


        Route::get('/myprofile', [UserController::class, 'Myprofile']);

        Route::post('/logout',  [AuthController::class, 'logout']);

        Route::apiResource('/category',  CategoryController::class)->except(['index', 'show']);
        Route::apiResource('/games',  GameController::class)->except(['index', 'show']);
        Route::post('/updateDownloadCount/{id}', [GameController::class, 'updateDownloadCount']);


        Route::post('/addreviews', [ReviewsController::class, 'store']);
        Route::put('/updatereview/{id}', [ReviewsController::class, 'update']);
        Route::delete('/deletereview/{id}', [ReviewsController::class, 'destroy']);
    });
});
Route::middleware('api')->get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'Ok']);
});

Route::post('/register',  [AuthController::class, 'register'])->name('register');
Route::post('/login',  [AuthController::class, 'login'])->name('login');//->middleware('throttle:10,1');
