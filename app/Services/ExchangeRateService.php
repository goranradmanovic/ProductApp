<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
  public function getExchangeRate()
  {
    $cacheKey = 'exchange_rate_eur'; // Cache key for the exchange rate

    // Check if the exchange rate is already set
    return Cache::remember($cacheKey, now()->addHours(1), function() {
      try {
        $curl = curl_init(); // Initialize cURL session

        // Set cURL options
        curl_setopt_array($curl, [
          CURLOPT_URL => 'https://open.er-api.com/v6/latest/USD',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_TIMEOUT => 5,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET'
        ]);

        $response = curl_exec($curl); // Execute the request
        $err = curl_error($curl); // Get errors if they exists

        curl_close($curl); // Close the cUrl session

        if ($err)
        {
          throw new \Exception("cURL Error Processing Request: $err");
        }

        $data = json_decode($response, true); // Decode JSON response

        // Validate the response structure
        if (isset($data['rates']['EUR']))
        {
          return $data['rates']['EUR'];
        }
        else
        {
          throw new \Exception('Invalid API response: EUR rate not found.');
        }
      } catch (\Exception $e) {
        Log::error('Faild to fetch exchange rate: ' . $e->getMessage());
        return (float) config('app.exchange_rate'); // Return a fallback value from the config/env
      }
    });
  }
}
