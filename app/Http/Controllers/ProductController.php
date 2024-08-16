<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\AuthorizesRole;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use AuthorizesRole;
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getProductDetails']]);
    }

    public function create(Request $request)
    {
        $this->authorizeRole('Super Admin');
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'prices' => 'required|array',  // Accepting prices as an array
            'stock_quantity' => 'required|integer',
        ]);
    
        // Convert the prices array to a JSON string
        $validatedData['prices'] = json_encode($validatedData['prices']);
    
        // Create the product
        $product = Product::create($validatedData);
    
        return response()->json(['product' => $product], 201);
    }
    

    public function getProductDetails($id)
    {
        $this->authorizeRole('Super Admin');
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json(['product' => $product], 200);
    }

    public function updateProduct(Request $request, $id)
    {
        $this->authorizeRole('Super Admin');
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    
        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'description' => 'string',
            'prices' => 'array',  // Accepting prices as an array
            'stock_quantity' => 'integer',
        ]);
    
        // If prices are provided, convert them to JSON before updating
        if (isset($validatedData['prices'])) {
            $validatedData['prices'] = json_encode($validatedData['prices']);
        }
    
        $product->update($validatedData);
    
        return response()->json(['product' => $product], 200);
    }
    
    public function deleteProduct($id)
    {
        $this->authorizeRole('Super Admin');
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
