<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Game;


use Illuminate\Http\Request;
use App\Events\UserActivityLogged;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 100);
        $sortBy = $request->input('sort_by', 'title');
        $sortOrder = $request->input('sort_order', 'asc');
        $categories = $request->input('categories', []);
        if (!is_array($categories)) {
            $categories = explode(',', $categories); // แปลง string เป็น array
        }
        $platforms = $request->input('platforms', []);
        if (!is_array($platforms)) {
            $platforms = explode(',', $platforms); // แปลง string เป็น array
        }
        $playType = $request->input('playtype');

        $cacheKey = 'game_page=' . $page . 'sortBy=' . $sortBy . 'sortOrder=' . $sortOrder .
            'perPage=' . $perPage . 'platforms=' . implode(',', $platforms) .
            'playType=' . $playType . 'categories=' . implode(',', $categories);

        $games = Cache::remember($cacheKey, 10, function () use ($perPage, $sortBy, $sortOrder, $categories, $platforms, $playType) {
            return Game::with(['categories:id,name', 'galleries', 'likes:id', 'reviews'])
                ->withCount('likes')
                ->withAvg('reviews', 'rating')
                ->when(!empty($categories), function ($query) use ($categories) {
                    $query->whereHas('categories', function ($q) use ($categories) {
                        $q->whereIn('name', $categories);
                    });
                })
                ->when(!empty($platforms), function ($query) use ($platforms) {
                    $query->where(function ($q) use ($platforms) {
                        foreach ($platforms as $platform) {
                            $q->orWhere('canplay', 'LIKE', '%' . $platform . '%');
                        }
                    });
                })
                ->when(!empty($playType), function ($query) use ($playType) {
                    $query->where('play_type', $playType); // กรอง play_type เป็น web หรือ download
                })
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
        });


        return $this->Success(
            data: GameResource::collection($games),
            meta: [
                'total_games' => $games->total(), // จำนวนเกมทั้งหมด
                'games_in_page' => $games->count(), // จำนวนเกมในหน้านี้
                'current_page' => $games->currentPage(), // หน้าปัจจุบัน
                'last_page' => $games->lastPage(), // หน้าสุดท้าย (จำนวนหน้าทั้งหมด)
                'per_page' => $games->perPage(), // จำนวนเกมต่อหน้า
                'from' => $games->firstItem(), // ลำดับแรกของรายการในหน้านี้
                'to' => $games->lastItem(), // ลำดับสุดท้ายของรายการในหน้านี้
            ]
        );
    }
    public function NewestGames()
    {
        $cacheKey = 'Top10_newestGames';
        $games = Cache::remember($cacheKey, 10, function () {
            return Game::with(['categories:id,name', 'galleries', 'likes:id', 'reviews'])
                ->withCount('likes')
                ->withAvg('reviews', 'rating')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        });
        return $this->Success(
            data: GameResource::collection($games),
        );
    }
    public function mostRatingGames()
    {
        $cacheKey = 'Top10_ratingGames';
        $games = Cache::remember($cacheKey, 10, function () {
            return Game::with(['categories:id,name', 'galleries', 'likes:id', 'reviews'])
                ->withCount('likes')
                ->withAvg('reviews', 'rating')
                ->orderBy('reviews_avg_rating', 'desc')
                ->limit(10)
                ->get();
        });
        return $this->Success(
            data: GameResource::collection($games),
        );
    }
    public function mostDowloadGames()
    {
        $cacheKey = 'Top10_dowloadGames';
        $games = Cache::remember($cacheKey, 10, function () {
            return Game::with(['categories:id,name', 'galleries', 'likes:id', 'reviews'])
                ->where('play_type','download')
                ->withCount('likes')
                ->withAvg('reviews', 'rating')
                ->orderBy('download', 'desc')
                ->limit(10)
                ->get();
        });
        return $this->Success(
            data: GameResource::collection($games),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255|unique:games,title',
            'content' => 'required|string|max:655350',
            'play_type' => 'required|in:web,download',
            'canplay' => 'nullable|array|min:1',
            'canplay.*' => 'in:Ios,Android,Window,Linux,Mac',

            'file.web' => 'nullable|file|mimes:zip|max:51200',

            'file' => 'nullable|array', // ตรวจสอบว่า file เป็น array
            'file.*' => 'nullable|file|mimes:zip,apk,exe', // ตรวจสอบแต่ละไฟล์

            'category_id' => 'required|array|min:1',
            'category_id.*' => 'integer|exists:categories,id',

            //Image Galleries
            'logo' => 'required|image|mimes:jpg,jpeg,png,gif,webp',
            'background' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp',
            'galleries' => 'nullable|array',
            'galleries.*' => 'image|mimes:jpg,jpeg,png,gif,webp',

            //theme
            'Bg_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
            'Bg_2_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
            'Background_opacity' => 'nullable|integer|between:0,100',

            'Text_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
            'Link_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
            'Button_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
        ], [
            'title.unique' => 'ชื่อเกมนี้มีอยู่ในระบบแล้ว',
            'content.required' => 'กรุณาใส่เนื้อหาของเกม',
            'play_type.required' => 'กรุณาเลือกประเภทการเล่น',
            'category_id.required' => 'กรุณาเลือกหมวดหมู่',
            'canplay.*.in' => 'กรุณาเลือกแพลตฟอร์ม Ios, Android, Window, Linux หรือ Mac',
            'logo.image' => 'โลโก้ต้องเป็นไฟล์รูปภาพ',
            'galleries.image' => 'รูปภาพในแกลเลอรีต้องเป็นไฟล์รูปภาพ',
            'background.image' => 'รูปภาพพื้นหลังต้องเป็นไฟล์รูปภาพ',
            'file.web.mimes' => 'ไฟล์สำหรับเกมเว็บต้องเป็นไฟล์ .zip เท่านั้น',
            'file.web.max' => 'ไฟล์สำหรับเกมเว็บต้องมีขนาดไม่เกิน 50MB',
            'file.*.mimes' => 'ไฟล์สำหรับเกมเว็บต้องเป็นไฟล์ .zip .apk .exe เท่านั้น',
        ]);

        $slug = $this->generateSlug($request->title);
        if (Game::where('title', $slug)->exists()) {
            return $this->Error(message: "ชื่อเกมซ้ำกับเกมอื่น", status: 442); // ใช้ HTTP status code 422 สำหรับ validation error
        }

        $game = Game::create([
            'title' => $slug,
            'content' => $request->content,
            'play_type' => $request->play_type,
            'canplay' => $request->canplay ? json_encode($request->canplay) : null,
            'user_id' => auth()->id(),
        ]);

        // ใช้คำสั่ง sync เพื่ออัพเดตความสัมพันธ์
        $game->categories()->sync($request->category_id);

        $themeData = [
            'Bg_Color' => $request->input('Bg_Color'),
            'Bg_2_Color' => $request->input('Bg_2_Color'),
            'Background_opacity' => $request->input('Background_opacity'),
            'Text_Color' => $request->input('Text_Color'),
            'Link_Color' => $request->input('Link_Color'),
            'Button_Color' => $request->input('Button_Color'),
        ];

        $uploadedFiles = [];

        // จัดการไฟล์สำหรับ Web
        if ($request->hasFile('file.web')) { // ใช้ dot notation
            $webFile = $request->file('file.web');
            $fileName = $slug . '_web.' . $webFile->getClientOriginalExtension();
            $filePath = $webFile->storeAs('GameFiles/web', $fileName, 'public');

            $zipFilePath = storage_path('app/public/' . $filePath);
            $extractTo = storage_path('app/public/GameFiles/web/' . $slug . '_web');
            if (!file_exists($extractTo)) {
                mkdir($extractTo, 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath) === true) {
                $zip->extractTo($extractTo); // แตกไฟล์ไปยังโฟลเดอร์ที่กำหนด
                $zip->close();

                // ลบไฟล์ ZIP หลังจากแตกไฟล์ (ถ้าไม่จำเป็นต้องเก็บไว้)
                Storage::disk('public')->delete($filePath);

                $subfolders = array_filter(glob($extractTo . '/*'), 'is_dir');

                if (!empty($subfolders)) {
                    // ถ้ามีโฟลเดอร์ย่อย ให้ใช้ชื่อโฟลเดอร์แรกที่พบเป็น path
                    $subfolderName = basename(reset($subfolders));
                    $uploadedFiles['web'] = url(Storage::url('GameFiles/web/' . $slug . '_web/' . $subfolderName . '/'));
                } else {
                    // ถ้าไม่มีโฟลเดอร์ย่อย บันทึกโฟลเดอร์หลักตามปกติ
                    $uploadedFiles['web'] = url(Storage::url('GameFiles/web/' . $slug . '_web' . '/'));
                }
            } else {
                return response()->json([
                    'message' => 'ไม่สามารถแตกไฟล์ ZIP ได้',
                ], 500);
            }
        }

        // จัดการไฟล์สำหรับ Download
        if ($request->play_type === 'download') {
            foreach ($request->canplay as $platform) {
                if ($request->hasFile("file.$platform")) { // ใช้ dot notation
                    $platformFile = $request->file("file.$platform");
                    $fileName = $slug . '_' . $platform . '.' . $platformFile->getClientOriginalExtension();
                    $filePath = $platformFile->storeAs('GameFiles/download/' . strtolower($platform), $fileName, 'public');

                    $fileSizeMB = round($platformFile->getSize() / (1024 * 1024), 2);

                    $uploadedFiles['download'][$platform] = [
                        'url' => url(Storage::url($filePath)),
                        'size' => $fileSizeMB
                    ];
                }
            }
        }

        // บันทึกข้อมูลไฟล์ในฐานข้อมูล
        $game->update([
            'file_path' => json_encode($uploadedFiles), // เก็บเป็น JSON
        ]);

        $images = [];
        if ($request->hasFile('logo')) {
            $logoName = 'logo_' . uniqid() . '.' . $request->file('logo')->getClientOriginalExtension();
            $filePath = $request->file('logo')->storeAs('GameImg/logo', $logoName, 'public');
            $images['logo'] = url(Storage::url($filePath));
        }

        if ($request->hasFile('background')) {
            $backgroundName = 'background_' . uniqid() . '.' . $request->file('background')->getClientOriginalExtension();
            $filePath = $request->file('background')->storeAs('GameImg/background', $backgroundName, 'public');
            $images['background'] = url(Storage::url($filePath));
        }

        if ($request->hasFile('galleries')) {
            $galleries = $request->file('galleries');

            foreach ($galleries as $gallery) {
                $galleryName = 'galleries_' . uniqid() . '.' . $gallery->getClientOriginalExtension();
                $filePath = $gallery->storeAs('GameImg/gallery', $galleryName, 'public');
                $images['galleries'][] = url(Storage::url($filePath));
            }
        }

        $game->galleries()->create([
            'images' => json_encode($images),
            'theme' => json_encode($themeData),
        ]);

        event(new UserActivityLogged(auth()->id(), 'game', 'create_game', 'ทำการเพิ่มเกม'));

        return $this->Success(data: new GameResource($game));
    }

    public function updateDownloadCount(string $id)
    {
        $game = Game::find($id);

        if (!$game) {
            return $this->Error('Couldn\'t find the game with the provided ID.', status: 500);
        }

        $game->download++;

        $game->save();
        return $this->Success("เพิ่มสำเร็จ");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        try {

            $game = Game::with(['categories:id,name', 'galleries', 'likes:id', 'reviews' => function ($query) {
                $query->orderBy('created_at', 'desc');  // เรียง reviews ตาม created_at ล่าสุดไปเก่าสุด
            }])
                ->withCount('likes')
                ->withAvg('reviews', 'rating')
                ->where('title', $slug)
                ->first();
            if (!$game) {
                return $this->Error('Couldn\'t find the game with the provided slug.');
            }

            return $this->Success(data: new GameResource($game));
        } catch (\Exception $e) {
            return $this->Error($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'nullable|string|max:255|unique:games,title',
            'content' => 'nullable|string|max:655350',
            'play_type' => 'nullable|in:web,download',
            'canplay' => 'nullable|array|min:1',
            'canplay.*' => 'in:Ios,Android,Window,Linux,Mac',

            'file.web' => 'nullable|file|mimes:zip|max:51200',

            'file' => 'nullable|array', // ตรวจสอบว่า file เป็น array
            'file.*' => 'nullable|file|mimes:zip,apk,exe', // ตรวจสอบแต่ละไฟล์

            'category_id' => 'nullable|array|min:1',
            'category_id.*' => 'integer|exists:categories,id',

            //Image Galleries
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp',
            'background' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp',
            'galleries' => 'nullable|array',
            'galleries.*' => 'image|mimes:jpg,jpeg,png,gif,webp',

            //theme
            'Bg_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
            'Bg_2_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
            'Background_opacity' => 'nullable|integer|between:0,100',

            'Text_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
            'Link_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
            'Button_Color' => 'nullable|string|regex:/^#([0-9A-Fa-f]{3}){1,2}$/',
        ], [
            'title.unique' => 'ชื่อเกมนี้มีอยู่ในระบบแล้ว',
            'canplay.*.in' => 'กรุณาเลือกแพลตฟอร์ม Ios, Android, Window, Linux หรือ Mac',
            'logo.image' => 'โลโก้ต้องเป็นไฟล์รูปภาพ',
            'galleries.image' => 'รูปภาพในแกลเลอรีต้องเป็นไฟล์รูปภาพ',
            'background.image' => 'รูปภาพพื้นหลังต้องเป็นไฟล์รูปภาพ',
            'file.web.mimes' => 'ไฟล์สำหรับเกมเว็บต้องเป็นไฟล์ .zip เท่านั้น',
            'file.web.max' => 'ไฟล์สำหรับเกมเว็บต้องมีขนาดไม่เกิน 50MB',
            'file.*.mimes' => 'ไฟล์สำหรับเกมเว็บต้องเป็นไฟล์ .zip .apk .exe เท่านั้น',
        ]);

        try {
            $game = Game::find($id);

            if (!$game) {
                return $this->Error('Couldn\'t find the game with the provided ID.', status: 500);
            }

            $slug = $this->generateSlug($request->title ?: $game->title);

            $game->update([
                'title' => $slug,
                'content' => $request->content ?: $game->content,
                'play_type' => $request->play_type ?: $game->play_type,
                'canplay' => $request->canplay ? json_encode($request->canplay) : $game->canplay,
                'user_id' => auth()->id(),
            ]);

            if ($request->category_id) {
                $game->categories()->sync($request->category_id);
            }

            $themeData = [
                'Bg_Color' => $request->input('Bg_Color', $game->theme->Bg_Color),
                'Bg_2_Color' => $request->input('Bg_2_Color', $game->theme->Bg_2_Color),
                'Background_opacity' => $request->input('Background_opacity', $game->theme->Background_opacity),
                'Text_Color' => $request->input('Text_Color', $game->theme->Text_Color),
                'Link_Color' => $request->input('Link_Color', $game->theme->Link_Color),
                'Button_Color' => $request->input('Button_Color', $game->theme->Button_Color),
            ];

            $uploadedFiles = [];

            if ($request->hasFile('file.web')) {
                $oldFilePath = storage_path('app/public/GameFiles/web/' . $slug . '_web');
                if (file_exists($oldFilePath)) {
                    // ลบโฟลเดอร์เก่าและไฟล์ในโฟลเดอร์
                    \File::deleteDirectory($oldFilePath);
                }
                $webFile = $request->file('file.web');
                $fileName = $slug . '_web.' . $webFile->getClientOriginalExtension();
                $filePath = $webFile->storeAs('GameFiles/web', $fileName, 'public');

                $zipFilePath = storage_path('app/public/' . $filePath);
                $extractTo = storage_path('app/public/GameFiles/web/' . $slug . '_web');

                if (!file_exists($extractTo)) {
                    mkdir($extractTo, 0755, true);
                }
                $zip = new \ZipArchive();
                if ($zip->open($zipFilePath) === true) {
                    $zip->extractTo($extractTo);
                    $zip->close();

                    // ลบไฟล์ ZIP หลังจากแตกไฟล์
                    Storage::disk('public')->delete($filePath);

                    // จัดการให้ได้ URL สำหรับไฟล์ใหม่
                    $subfolders = array_filter(glob($extractTo . '/*'), 'is_dir');
                    if (!empty($subfolders)) {
                        $subfolderName = basename(reset($subfolders));
                        $uploadedFiles['web'] = url(Storage::url('GameFiles/web/' . $slug . '_web/' . $subfolderName . '/'));
                    } else {
                        $uploadedFiles['web'] = url(Storage::url('GameFiles/web/' . $slug . '_web' . '/'));
                    }
                } else {
                    return response()->json(['message' => 'ไม่สามารถแตกไฟล์ ZIP ได้'], 500);
                }
            } else {
                $uploadedFiles['web'] = $game->file_path['web'] ?? null;
            }

            if ($request->play_type === 'download') {
                // ตรวจสอบแพลตฟอร์มแต่ละตัวที่เลือก
                foreach ($request->canplay as $platform) {
                    // ตรวจสอบว่าไฟล์ใหม่ถูกอัปโหลดหรือไม่
                    if ($request->hasFile("file.$platform")) {
                        // ลบไฟล์เก่าก่อนที่จะแทนที่ด้วยไฟล์ใหม่
                        $oldFilePath = storage_path('app/public/GameFiles/download/' . strtolower($platform) . '/' . $slug . '_' . $platform . '.*');
                        $existingFiles = glob($oldFilePath);
                        if (!empty($existingFiles)) {
                            // ลบไฟล์เก่าที่มีอยู่
                            \Storage::disk('public')->delete($existingFiles);
                        }

                        // อัปโหลดไฟล์ใหม่
                        $platformFile = $request->file("file.$platform");
                        $fileName = $slug . '_' . $platform . '.' . $platformFile->getClientOriginalExtension();
                        $filePath = $platformFile->storeAs('GameFiles/download/' . strtolower($platform), $fileName, 'public');

                        // คำนวณขนาดไฟล์ (ในหน่วย MB)
                        $fileSizeMB = round($platformFile->getSize() / (1024 * 1024), 2);

                        // เก็บข้อมูลไฟล์ใน array สำหรับการบันทึก
                        $uploadedFiles['download'][$platform] = [
                            'url' => url(Storage::url($filePath)),
                            'size' => $fileSizeMB
                        ];
                    }
                }
            }

            $game->update([
                'file_path' => json_encode($uploadedFiles),
            ]);

            $images = [];

            if ($request->hasFile('logo')) {
                // ถ้ามีไฟล์ logo เก่า ลบออก
                if ($game->logo) {
                    // ดึงชื่อไฟล์จาก URL
                    $oldLogoPath = str_replace('http://localhost/storage/', '', $game->logo);
                    $oldLogoPath = storage_path('app/public/' . $oldLogoPath); // สร้าง path ที่สามารถใช้ลบได้

                    // ตรวจสอบว่าไฟล์มีอยู่จริงหรือไม่
                    if (file_exists($oldLogoPath)) {
                        \Storage::disk('public')->delete($oldLogoPath); // ลบไฟล์เก่า
                    }
                }

                // อัปโหลดไฟล์ logo ใหม่
                $logoName = 'logo_' . uniqid() . '.' . $request->file('logo')->getClientOriginalExtension();
                $filePath = $request->file('logo')->storeAs('GameImg/logo', $logoName, 'public');
                $images['logo'] = url(Storage::url($filePath));
            }

            if ($request->hasFile('background')) {
                // ถ้ามีไฟล์ background เก่า ลบออก
                if ($game->background) {
                    $oldBackgroundPath = str_replace('http://localhost/storage/', '', $game->background);
                    $oldBackgroundPath = storage_path('app/public/' . $oldBackgroundPath);

                    if (file_exists($oldBackgroundPath)) {
                        \Storage::disk('public')->delete($oldBackgroundPath);
                    }
                }

                // อัปโหลดไฟล์ background ใหม่
                $backgroundName = 'background_' . uniqid() . '.' . $request->file('background')->getClientOriginalExtension();
                $filePath = $request->file('background')->storeAs('GameImg/background', $backgroundName, 'public');
                $images['background'] = url(Storage::url($filePath));
            }

            if ($request->hasFile('galleries')) {
                $galleries = $request->file('galleries');
                foreach ($galleries as $gallery) {
                    // ถ้ามีไฟล์เก่าในแกลเลอรี ลบออก
                    if (isset($game->galleries)) {
                        foreach ($game->galleries as $oldGallery) {
                            $oldGalleryPath = str_replace('http://localhost/storage/', '', $oldGallery);
                            $oldGalleryPath = storage_path('app/public/' . $oldGalleryPath);

                            if (file_exists($oldGalleryPath)) {
                                \Storage::disk('public')->delete($oldGalleryPath);
                            }
                        }
                    }

                    // อัปโหลดไฟล์ gallery ใหม่
                    $galleryName = 'galleries_' . uniqid() . '.' . $gallery->getClientOriginalExtension();
                    $filePath = $gallery->storeAs('GameImg/gallery', $galleryName, 'public');
                    $images['galleries'][] = url(Storage::url($filePath));
                }
            }

            $game->galleries()->update([
                'images' => json_encode($images),
                'theme' => json_encode($themeData),
            ]);

            event(new UserActivityLogged(auth()->id(), 'game', 'update_game', 'ทำการอัปเดตเกม'));

            return $this->Success(data: new GameResource($game));
        } catch (\Exception $e) {
            return $this->Error('An error occurred while updating the game.', error: $e->getMessage(), status: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $game = Game::find($id);

            if (!$game) {
                return $this->Error('Couldn\'t find the game with the provided ID.', status: 500);
            }

            $game->categories()->detach();

            $game->delete();

            return $this->Success('Game deleted successfully.');
        } catch (\Exception $e) {

            return $this->Error('Something went wrong while deleting the game.', $e->getMessage(), 500);
        }
    }
}
