<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitorController extends Controller
{

    public function recordVisit(Request $request)
    {
        try {
            $today = Carbon::now('Asia/Bangkok')->toDateString();

            $visitor = Visitor::where('date', $today)->first();

            if ($visitor) {
                $visitor->increment('count');
            } else {
                Visitor::create(['date' => $today, 'count' => 1]);
            }

            return $this->Success(message: "Visitor recorded");
        } catch (\Exception $e) {
            return $this->Error(message: "Cant recorded visitor", error: $e->getMessage());
        }
    }

    public function getTotalVisitCount()
    {
        try {
            $today = Carbon::now('Asia/Bangkok')->toDateString();
            $totalVisits = Visitor::sum('count');
            $todayVisits = Visitor::where('date', $today)->sum('count');
            return $this->Success(data: [
                'totalVisits' => $totalVisits,
                'todayVisits' => $todayVisits,
                'date' => $today
            ]);
        } catch (\Exception $e) {
            return $this->Error(message: "Error retrieving total visit count", error: $e->getMessage());
        }
    }
}
