<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class AdminMiddleware
{

    // ใช้ ResponeTrait เพื่อจัดการการตอบกลับ API

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== "admin") {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return  $next($request);
    }
}
