<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    public function Myprofile()
    {
        $profile = Game::with(['user', 'galleries'])->where('user_id', auth()->id())->get();
        $gamecount = Game::with('user')->where('user_id', auth()->id())->count();

        if (!$profile) {
            return $this->Error(message: "User not found", status: 404);
        }

        return $this->Success(data: [
            "profile" => GameResource::collection($profile),
            "gamecount" => $gamecount,
        ]);
    }

    public function userprofile($id)
    {
        $profile = User::with('games')->where('id', $id)->first();

        if (!$profile) {
            return $this->Error(message: "User not found", status: 404);
        }

        return $this->Success(data: $profile);
    }

    public function createUser(Request $request)
    {
        try {

            $this->authorize('create', auth()->user());

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string',
                'role' => 'required|in:admin,user,student'
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role
            ]);

            return $this->Success(message: 'createUser Success');
        } catch (\Exception $e) {
            return $this->Error(message: "เกิดข้อผิดพลาดบางอย่าง", error: $e->getMessage());
        }
    }

    public function updateRole(Request $request, string $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->Error(message: "ไม่พบผู้ใช้");
            }

            $this->authorize('updateRole', auth()->user());

            $request->validate([
                'role' => ['required', 'in:admin,user,student'], // กำหนดค่าที่ role สามารถเป็นได้
            ]);

            $user->update([
                'role' => $request->role
            ]);


            return $this->Success(message: "จัดการสำเร็จ");
        } catch (\Exception $e) {
            return $this->Error(message: "เกิดข้อผิดพลาดบางอย่าง", error: $e->getMessage());
        }
    }

    public function DeleteUser(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->Error('ไม่พบความผู้ใช้ที่ต้องการลบ', status: 404);
            }
            $this->authorize('delete', $user);
            $user->delete();
            return $this->Success(message: 'ลบผู้ใช้สำเร็จ');
        } catch (\Exception $e) {
            return $this->Error(message: "เกิดข้อผิดพลาดบางอย่าง", error: $e->getMessage());
        }
    }
}
