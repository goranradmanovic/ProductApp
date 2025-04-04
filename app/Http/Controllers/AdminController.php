<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Jobs\SendPriceChangeNotification;
use App\Models\Product;

use Illuminate\Support\Str;
class AdminController extends Controller
{
    public function loginPage()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        if (Auth::attempt($request->except('_token'))) {
            return redirect()->route('admin.products');
        }

        return redirect()->back()->with('error', 'Invalid login credentials');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    // --- Products methods ---

    // Show all products list/table
    public function products(Product $product)
    {
        return view('admin.products', ['products' => $product->all()]);
    }

    // Show add form
    public function addProductForm()
    {
        return view('admin.add_product');
    }

    // Add new product
    public function addProduct(Request $request)
    {
        $validated = $this->validateProductRequest($request); // Validate the request

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'image' => $this->handleProductImage($request)
        ]);

        return redirect()->route('admin.products')->with('success', 'Product added successfully');
    }

    // Show edit form with product data
    public function editProduct(Product $product)
    {
        return view('admin.edit_product', compact('product'));
    }

    // Update single product
    public function updateProduct(Request $request, Product $product)
    {
        try {
            $validatedData = $this->validateProductRequest($request);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $oldPrice = $product->price; // Store the old price before updating
        $product->update($validatedData); // Update all product fields

        if ($request->hasFile('image')) {
            $product->image = $this->handleProductImage($request); // Save the product image in the upload folder
        }

        $product->save(); // Save updated product in the DB

        // Check if price has changed
        if ($oldPrice != $product->price) {
            // Get notification email from env
            $notificationEmail = config('price_notification_email');

            try {
                SendPriceChangeNotification::dispatch($product, $oldPrice, $product->price, $notificationEmail);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch price change notification: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.products')->with('success', 'Product updated successfully');
    }

    public function deleteProduct(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products')->with('success', 'Product deleted successfully');
    }

    // --- Helpers Functions ---

    // Helper function for validation of input fields
    private function validateProductRequest(Request $request): array
    {
        return Validator::make($request->all(), [
            'name' => 'required|min:3',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048'
        ])->validate();
    }

    // Helper function for saving the product image
    private function handleProductImage(Request $request): string
    {
        if ($request->hasFile('image') && $request->file('image')->isValid())
        {
            $file = $request->file('image'); // Get the image

            // Generate unique file name to avoid conflicts
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // Move file to the uploads directory
            $file->move(public_path('uploads'), $filename);

            return 'uploads/' . $filename;
        }
        elseif(!isset($product->image))
        {
            return 'product-placeholder.jpg';
        }
    }
}