<?php

namespace App\Repositories;

use Exception;
use GuzzleHttp\Client;

class RajaongkirRepository
{
    public function getDestinations($search)
    {
        $cacheKey = 'rajaongkir_destination_' . md5($search);
        $destinations = cache()->remember(
            $cacheKey,
            60 * 60 * 6, // Cache for 6 hours
            function () use ($search) {
                $client = new Client();
                $response = $client->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                    'query' => [
                        'search' => $search,
                        'limit' => 20,
                    ],
                    'headers' => [
                        'key' => env('RAJAONGKIR_API_KEY'),
                    ],
                ]);
                return json_decode($response->getBody()->getContents())->data;
            }
        );

        return $destinations;
    }

    public function getShipping($originId, $destinationId, $weight, $courier)
    {
        $cacheKey = "rajaongkir_shipping_cost_{$originId}_{$destinationId}_{$weight}_{$courier}";
        $shippings = cache()->remember(
            $cacheKey,
            60 * 60 * 6, // Cache for 6 hours
            function () use ($originId, $destinationId, $weight, $courier) {
                $client = new Client();
                $response = $client->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                    'headers' => [
                        'key' => env('RAJAONGKIR_API_KEY'),
                    ],
                    'form_params' => [
                        'origin' => $originId,
                        'destination' => $destinationId,
                        'weight' => $weight,
                        'courier' => $courier,
                        'price' => 'lowest'
                    ],
                ]);

                return json_decode($response->getBody()->getContents(), true)['data'][0];
            }
        );

        if (!$shippings) {
            cache()->forget($cacheKey);
            throw new Exception('Gagal mendapatkan biaya pengiriman');
        }

        return $shippings;
    }
}
