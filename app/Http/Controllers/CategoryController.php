<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class CategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $brands = Category::query()->paginate(5);
        return $this->successResponse([
            'categories' => CategoryResource::collection($brands),
            'links' => CategoryResource::collection($brands)->response()->getData()->links,
            'meta' => CategoryResource::collection($brands)->response()->getData()->meta,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'parent_id' => 'required|integer',
            'description' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        DB::beginTransaction();
        $category = Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
        ]);
        DB::commit();

        return $this->successResponse(new CategoryResource($category));
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return $this->successResponse(new CategoryResource($category));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {

        $validator = Validator::make($request->all(), [
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        DB::beginTransaction();
        $category ->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
        ]);
        DB::commit();

        return $this->successResponse(new CategoryResource($category));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        DB::beginTransaction();
        $category->delete();
        DB::commit();
        return $this->successResponse(new CategoryResource($category));

    }
}
