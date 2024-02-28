<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BrandController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::query()->paginate(2);
        return $this->successResponse([
            'brands' => BrandResource::collection($brands),
            'links' => BrandResource::collection($brands)->response()->getData()->links,
            'meta' => BrandResource::collection($brands)->response()->getData()->meta,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'display_name' => 'required|unique:brands',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        DB::beginTransaction();
        $brand = Brand::create([
            'name' => $request->name,
            'display_name' => $request->name,
        ]);
        DB::commit();

        return $this->successResponse(new BrandResource($brand), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        return $this->successResponse(new BrandResource($brand));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,Brand $brand)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'display_name' => 'required|unique:brands',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        DB::beginTransaction();
        $brand ->update([
            'name' => $request->name,
            'display_name' => $request->name,
        ]);
        DB::commit();

        return $this->successResponse(new BrandResource($brand));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();
        return $this->successResponse(new BrandResource($brand));
    }
}
