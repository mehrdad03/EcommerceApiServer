<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JetBrains\PhpStorm\NoReturn;

class ProductController extends ApiController
{
    public function index()
    {

        $products = Product::query()->paginate(2);
        return $this->successResponse([
            'brands' => ProductResource::collection($products->load('images')),
            'links' => ProductResource::collection($products)->response()->getData()->links,
            'meta' => ProductResource::collection($products)->response()->getData()->meta,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'primary_image' => 'required|image',
            'price' => 'integer',
            'quantity' => 'integer',
            'delivery_amount' => 'nullable|integer',
            'description' => 'required',
            'images.*' => 'nullable|image'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }
        DB::beginTransaction();

        $primaryImageName = Carbon::now()->microsecond . '.' . $request->primary_image->extension();
        $request->primary_image->storeAs('images/products', $primaryImageName, 'public');

        if ($request->has('images')) {
            $fileNameImages = [];
            foreach ($request->images as $image) {
                $fileNameImage = Carbon::now()->microsecond . '.' . $image->extension();
                $image->storeAs('images/products', $fileNameImage, 'public');
                array_push($fileNameImages, $fileNameImage);
            }
        }

        $product = Product::create([
            'name' => $request->name,
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'primary_image' => $primaryImageName,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'delivery_amount' => $request->delivery_amount,
            'description' => $request->description,
        ]);

        if ($request->has('images')) {
            foreach ($fileNameImages as $fileNameImage) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $fileNameImage
                ]);
            }
        }
        DB::commit();

        return $this->successResponse(new ProductResource($product), 201);
    }

    #[NoReturn] public function update(Request $request, Product $product): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'primary_image' => 'nullable|image',
            'price' => 'integer',
            'quantity' => 'integer',
            'delivery_amount' => 'nullable|integer',
            'description' => 'required',
            'images.*' => 'nullable|image'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }
        DB::beginTransaction();
        if ($request->has('primary_image')) {
            $primaryImageName = Carbon::now()->microsecond . '.' . $request->primary_image->extension();
            $request->primary_image->storeAs('images/products', $primaryImageName, 'public');
        }
        if ($request->has('images')) {
            $fileNameImages = [];
            foreach ($request->images as $image) {
                $fileNameImage = Carbon::now()->microsecond . '.' . $image->extension();
                $image->storeAs('images/products', $fileNameImage, 'public');
                array_push($fileNameImages, $fileNameImage);
            }
        }

        $product->update([
                'name' => $request->name,
                'brand_id' => $request->brand_id,
                'category_id' => $request->category_id,
                'primary_image' => $request->has('primary_image') ? $primaryImageName : $product->primary_image,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'delivery_amount' => $request->delivery_amount,
                'description' => $request->description,
            ]);

        if ($request->has('images')) {

            foreach ($product->images as $productImage) {
                $productImage->delete();
            }
            foreach ($fileNameImages as $fileNameImage) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $fileNameImage
                ]);
            }
        }
        DB::commit();

        return $this->successResponse(new ProductResource($product));
    }

    public function destroy(Product $product): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        $product->delete();
        DB::commit();

        return $this->successResponse(new ProductResource($product));

    }
    public function show(Product $product): \Illuminate\Http\JsonResponse
    {
        return $this->successResponse(new ProductResource($product->load('images')));
    }
}
