<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Events\UserActivityLogged;
use App\Models\Reviews;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReviewsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'comment' => 'required|string',
                'rating' => 'nullable|integer|min:0|max:5',
                'parent' => 'nullable|integer',
                'game_id' => 'required|exists:games,id'
            ], [
                'comment.required' => 'กรุณากรอกความคิดเห็น',
                'game_id.required' => 'กรุณาระบุเกมที่ต้องการรีวิว',
            ]);

            $rating = $request->rating;

            // ถ้า rating เป็น 0 ให้เป็น null
            if ($rating == 0) {
                $rating = null;
            }

            // ถ้า rating มากกว่า 5 ให้ return error
            if ($rating > 5) {
                return $this->Error('ค่าคะแนนไม่ถูกต้อง', status: 400);
            }

            $reviews = Reviews::create([
                'comment' => $request->comment,
                'rating' => $rating,
                'parent_id' => $request->parent,
                'game_id' => $request->game_id,
                'user_id' => auth()->id(),
            ]);

            $reviews->load('game');

            // ตรวจสอบว่าเกมมีอยู่จริงก่อนเรียกใช้ `title`
            $gameTitle = $reviews->game->title;

            event(new UserActivityLogged(
                auth()->id(),
                'reviews',
                'create_reviews',
                'ทำการเพิ่มความคิดเห็นที่เกม ' . $gameTitle
            ));


            return $this->Success("เพิ่มความคิดเห็นสำเร็จ");
        } catch (\Exception $e) {
            return $this->Error(message: "ไม่สามารถเพิ่มความคิดเห็นได้", error: $e->getMessage(), status: 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'comment' => 'required|string',
                'rating' => 'nullable|integer|min:0|max:5',
            ]);

            $rating = $request->rating;

            // ถ้า rating เป็น 0 ให้เป็น null
            if ($rating == 0) {
                $rating = null;
            }

            // ถ้า rating มากกว่า 5 ให้ return error
            if ($rating > 5) {
                return $this->Error('ค่าคะแนนไม่ถูกต้อง', status: 400);
            }

            $review = Reviews::find($id);

            if (!$review) {
                return $this->Error('ไม่พบความคิดเห็นที่ต้องการแก้ไข.', status: 404);
            }

            $this->authorize('update', $review);


            $review->update([
                'comment' => $request->comment,
                'rating' => $rating,  // ถ้าไม่มีค่าใน rating ก็จะเป็นค่าเดิม
            ]);

            $review->load('game');

            // ตรวจสอบว่าเกมมีอยู่จริงก่อนเรียกใช้ `title`
            $gameTitle =  $review->game->title;


            event(new UserActivityLogged(
                auth()->id(),
                'reviews',
                'edit_reviews',
                'ทำการแก้ไขความคิดเห็นที่เกม ' . $gameTitle
            ));

            return $this->Success('แก้ไขความคิดเห็นสำเร็จ');
        } catch (\Exception $e) {
            return $this->Error('Something went wrong while updating the reviews.', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $review = Reviews::find($id);

            if (!$review) {
                return $this->Error('ไม่พบความคิดเห็นที่ต้องการลบ', status: 404);
            }

            $this->authorize('delete', $review);

            $review->load(['user', 'game']);

            $userName = $review->user->name;
            $gameName = $review->game->title;

            Reviews::where('parent_id', $id)->delete();

            $review->delete();

            $actionMessage = auth()->user()->role === "admin"
                ? "ทำการลบความคิดเห็นของ $userName ที่เกม $gameName "
                : "ทำการลบความคิดเห็น ที่เกม $gameName ";

            event(new UserActivityLogged(
                auth()->id(),
                'reviews',
                'delete_reviews',
                $actionMessage
            ));


            return $this->Success('ลบความคิดเห็นสำเร็จ');
        } catch (\Exception $e) {
            return $this->Error('Something went wrong while deleting the reviews.', $e->getMessage(), 500);
        }
    }
}
