<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ExchangeRateService;
use App\Models\Product;

class ProductController extends Controller
{
    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
      $this->exchangeRateService = $exchangeRateService;
    }

    public function index(Product $product)
    {
        return view('products.list', ['products' => $product->all(), 'exchangeRate' => $this->exchangeRateService->getExchangeRate()]);
    }

    public function show(Request $request, Product $product)
    {
        return view('products.show', ['product' => $product, 'exchangeRate' => $this->exchangeRateService->getExchangeRate()]);
    }
}
