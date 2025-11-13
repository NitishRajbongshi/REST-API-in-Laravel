<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /** * Display a listing of the resource. */
    public function index(Request $request)
    {
        try {
            // Allow dynamic page size with a default of 10 per page
            // $perPage = $request->get('per_page', 10);
            // $products = Product::paginate($perPage);

            $products = Product::all();
            return response()->json([
                'status' => true,
                'message' => 'Products retrieved successfully.',
                'data' => $products
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch products.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /** * Store a newly created resource in storage. */
    public function store(Request $request)
    {
        Log::error($request->all());
        Log::error('headers: ' . json_encode($request->headers->all()));
        Log::error('raw body: ' . $request->getContent());
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
            ]);
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }
            $product = Product::create($validated);
            return response()->json([
                'status' => true,
                'message' => 'Product created successfully.',
                'data' => $product
            ], 201);
        } catch (ValidationException $e) {
            Log::error('Validation error while creating product: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /** * Display the specified resource. */
    public function show(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json([
                'status' => true,
                'message' => 'Product retrieved successfully.',
                'data' => $product
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /** * Search for products by name. */ public function search($name)
    {
        try {
            $products = Product::where('name', 'like', "%{$name}%")->get();
            if ($products->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No products found matching the search term.',
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Products found.',
                'data' => $products
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Search failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /** * Update the specified resource in storage. */
    public function update(Request $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|required|string|unique:products,slug,' . $product->id,
                'price' => 'sometimes|required|numeric|min:0',
                'description' => 'nullable|string',
            ]);
            $product->update($validated);
            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully.',
                'data' => $product
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /** * Remove the specified resource from storage. */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
