<?php

use Illuminate\Support\Facades\Http;

function getRandomQuote(): string
{
    try {
        $response = Http::get('http://api.forismatic.com/api/1.0/', [
            'method' => 'getQuote',
            'format' => 'json',
            'lang' => 'en',
        ]);

        if ($response->successful()) {
            $quoteText = $response->json('quoteText');

            if (empty($quoteText)) {
                return 'Stay positive, work hard, make it happen!';
            }

            return $quoteText;
        }
    } catch (\Exception $e) {
        \Log::error('Error fetching quote: ' . $e->getMessage());

        return 'Stay positive, work hard, make it happen!';
    }

    return 'Stay positive, work hard, make it happen!';
}