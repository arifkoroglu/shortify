<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UrlShortenController;
use App\Http\Controllers\Api\V1\UrlRedirectController;


Route::post('/v1/shorten', UrlShortenController::class);
Route::get('/v1/{shortCode}', UrlRedirectController::class)->where('shortCode', '[a-zA-Z0-9]{6}');;