<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Categories;
use App\Models\Game;
use Illuminate\Http\Request;
use App\Models\UserActivityLog;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserActivityLogResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashBoardStatController extends Controller
{
    use AuthorizesRequests;
    protected $visitorController;

    public function __construct(VisitorController $visitorController)
    {
        $this->visitorController = $visitorController;
    }

    public function getStats()
    {
        try {

            $totalVisits = $this->visitorController->getTotalVisitCount();
            $totalGame = Game::count();
            $gameInMonth = Game::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();
            $gamelog = UserActivityLog::where('log_type', 'game')
                ->with(['game', 'user'])
                ->get();

            $totalCategories = Categories::count();

            $reviewsLog = UserActivityLog::where('log_type', 'reviews')
                ->with(['game', 'user'])
                ->get();

            $authLog = UserActivityLog::where('log_type', 'auth')
                ->with(['game', 'user'])
                ->get();

            return $this->Success(data: [
                "totalVisits" => $totalVisits,
                "totalGame" => $totalGame,
                "gameInMonth" => $gameInMonth,
                "totalCategories" => $totalCategories,
                "gamelog" => UserActivityLogResource::collection($gamelog),
                "reviewsLog" => UserActivityLogResource::collection($reviewsLog),
                "authLog" => UserActivityLogResource::collection($authLog),
            ]);
        } catch (\Exception $e) {
            return $this->Error(message: "เกิดข้อผิดพลาดบางอย่าง", error: $e->getMessage(), status: 500);
        }
    }

    public function getUserStat()
    {
        try {

            $this->authorize('viewAny', User::class);

            $totalUsers = User::count();
            $usersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            $totalStudents = User::where('role', 'student')->count();
            $userdata = User::all();

            return $this->Success(data: [
                "totalUsers" => $totalUsers,
                "usersThisMonth" => $usersThisMonth,
                "totalStudents" => $totalStudents,
                "userdata" => $userdata,
            ]);
        } catch (\Exception $e) {
            return $this->Error(message: "เกิดข้อผิดพลาดบางอย่าง", error: $e->getMessage(), status: 500);
        }
    }

    public function getCategoriesStat()
    {
        try {
            $totalCategories = Categories::count();
            $Categoriesdata = Categories::all();
            return $this->Success(data: [
                "totalCategories" => $totalCategories,
                "Categoriesdata" => $Categoriesdata,
            ]);
        } catch (\Exception $e) {
            return $this->Error(message: "เกิดข้อผิดพลาดบางอย่าง", error: $e->getMessage(), status: 500);
        }
    }
    public function getGameStat()
    {
        try {
            $totalCategories = Categories::count();
            $Categoriesdata = Categories::all();
            return $this->Success(data: [
                "totalCategories" => $totalCategories,
                "Categoriesdata" => $Categoriesdata,
            ]);
        } catch (\Exception $e) {
            return $this->Error(message: "เกิดข้อผิดพลาดบางอย่าง", error: $e->getMessage(), status: 500);
        }
    }
}
