<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Categories;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;


class CategoryController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Categories::all();
        return $this->Success(data: CategoryResource::collection($categories));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name'
        ]);

        try {
            $category = Categories::create([
                'name' => $request->name,
            ]);
            return $this->Success('Category created successfully.', new CategoryResource($category));
        } catch (\Exception $e) {
            return $this->Error('Something went wrong while creating the category.', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $category)
    {
        try {
            $category = Categories::findOrFail($category);
            // Return the category as a resource
            return $this->Success(data: new CategoryResource($category));
        } catch (\Exception $e) {
            return $this->Error('Something went wrong while finding the category.', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name'
            ]);

            $category = Categories::find($id);

            if (!$category) {
                return $this->Error('ไม่พบหมวดหมู่');
            }

            $category->update([
                'name' => $request->name,
            ]);

            return $this->Success('Category updated successfully.');
        } catch (\Exception $e) {
            return $this->Error('Something went wrong while updating the category.', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $category)
    {
        try {

            $category = Categories::findOrFail($category);


            $category->delete();


            return $this->Success('Category deleted successfully.');
        } catch (\Exception $e) {

            return $this->Error('Something went wrong while deleting the category.', $e->getMessage(), 500);
        }
    }
}
