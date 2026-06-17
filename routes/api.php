<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UrlShortenController;

Route::post('/v1/shorten', UrlShortenController::class);