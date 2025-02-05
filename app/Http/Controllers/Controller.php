<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function generateSlug($string)
    {
        // ลบช่องว่างเกินออกและแทนที่ด้วย "-"
        $slug = preg_replace('/\s+/', '-', trim($string));

        // เปลี่ยนข้อความเป็นตัวพิมพ์เล็ก
        $slug = mb_strtolower($slug, 'UTF-8');

        // ลบอักขระพิเศษที่ไม่ต้องการ ยกเว้นตัวอักษรไทย, อังกฤษ, ตัวเลข และเครื่องหมาย "-"
        $slug = preg_replace('/[^ก-ฮa-z0-9\-]+/u', '', $slug);

        return $slug;
    }

    public function Success(String $message = "Success", $meta = null, $data = null, Int $status = 200)
    {
        return response()->json(
            [
                'success' => true,
                'status' => $status,
                'message' => $message,
                'data' => $data,
                'meta' => $meta,
            ],
            status: $status
        );
    }
    public function Error(String $message = "Error", $error = null, Int $status = 400)
    {
        return response()->json(
            data: [
                'success' => false,
                'status' => $status,
                'message' => $message,
                'data' => $error,
            ],
            status: $status
        );
    }
}
