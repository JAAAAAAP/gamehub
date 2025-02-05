<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use App\Events\UserActivityLogged;
use App\Http\Controllers\Controller;
use App\Models\User;

class AuthController extends Controller
{
    // ใช้ ResponeTrait เพื่อจัดการการตอบกลับ API

    /**
     * ลงทะเบียนผู้ใช้งานใหม่
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // ตรวจสอบข้อมูลที่รับเข้ามา
        $request->validate([
            'name' => 'required|string|max:255', // ชื่อผู้ใช้งานต้องไม่ว่างและเป็นตัวอักษร
            'email' => 'required|string|email|max:255|unique:users', // ตรวจสอบอีเมลไม่ให้ซ้ำ
            'password' => 'required|string',
        ], [
            //Custom error message
            'email.unique' => 'อีเมลนี้มีผู้ใช้งานแล้ว'
        ]);

        try {
            // สร้างผู้ใช้งานใหม่ในฐานข้อมูล
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // เข้ารหัสรหัสผ่านก่อนบันทึกฐานข้อมูล
            ]);

            event(new UserActivityLogged(
                $user->id,
                'auth',
                'register',
                'ทำการสมัครผู้ใช้'
            ));

            // ส่งข้อความสำเร็จ
            return $this->Success(message: 'Register Successfully');
        } catch (\Exception $e) {
            // ดักข้อผิดพลาดที่เกิดขึ้นระหว่างการสมัคร
            return $this->Error(message: 'Failed to register user. Please try again later.', status: 500);
        }
    }

    /**
     * เข้าสู่ระบบผู้ใช้งาน
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // ตรวจสอบข้อมูลที่รับเข้ามา
        $request->validate([
            'email' => 'required|email', // ต้องใส่อีเมล
            'password' => 'required', // ต้องใส่รหัสผ่าน
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->Error(message: 'ไม่พบอีเมลนี้ในระบบ', status: 404);
        }

        if (!\Hash::check($request->password, $user->password)) {
            return $this->Error(message: 'รหัสผ่านไม่ถูกต้อง', status: 401);
        }

        Auth::login($user);
        $request->session()->regenerate();

        event(new UserActivityLogged(
            $user->id,
            'auth',
            'login',
            'ทำการเข้าสู่ระบบ'
        ));

        return $this->Success(
            message: 'Login Successfully',
        );
    }

    /**
     * ออกจากระบบ (ลบ Token ทั้งหมดของผู้ใช้งาน)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {

            $user = Auth::user();

            event(new UserActivityLogged(
                $user->id,
                'auth',
                'logout',
                'ทำการออกจากระบบ'
            ));

            auth('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return $this->Success(message: 'Logged out successfully');
        } catch (\Exception $e) {
            return $this->Error(message: 'Failed to loging out', error: $e->getMessage());
        }
    }
}
