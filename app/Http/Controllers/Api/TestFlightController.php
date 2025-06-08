<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestFlightController extends Controller
{
    public function search(Request $request)
    {
        return response()->json([
            [
                'carrier' => 'WB',
                'price' => '320.00 USD',
                'class' => 'ECONOMY',
                'tripType' => 'Round Trip',
                'itineraries' => [
                    [
                        'duration' => 'PT1H40M',
                        'segments' => [
                            [
                                'departure' => [
                                    'iataCode' => 'KGL',
                                    'at' => now()->addHours(1)->toDateTimeString()
                                ],
                                'arrival' => [
                                    'iataCode' => 'NBO',
                                    'at' => now()->addHours(3)->toDateTimeString()
                                ],
                                'cabin' => 'ECONOMY',
                            ]
                        ]
                    ],
                    [
                        'duration' => 'PT1H45M',
                        'segments' => [
                            [
                                'departure' => [
                                    'iataCode' => 'NBO',
                                    'at' => now()->addDays(5)->addHours(1)->toDateTimeString()
                                ],
                                'arrival' => [
                                    'iataCode' => 'KGL',
                                    'at' => now()->addDays(5)->addHours(3)->toDateTimeString()
                                ],
                                'cabin' => 'ECONOMY',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'carrier' => 'ET',
                'price' => '250.00 USD',
                'class' => 'BUSINESS',
                'tripType' => 'One Way',
                'itineraries' => [
                    [
                        'duration' => 'PT2H00M',
                        'segments' => [
                            [
                                'departure' => [
                                    'iataCode' => 'KGL',
                                    'at' => now()->addHours(4)->toDateTimeString()
                                ],
                                'arrival' => [
                                    'iataCode' => 'ADD',
                                    'at' => now()->addHours(6)->toDateTimeString()
                                ],
                                'cabin' => 'BUSINESS',
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
