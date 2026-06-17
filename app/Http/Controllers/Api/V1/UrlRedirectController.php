<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UrlShortenerService;
use Exception;
use Illuminate\Http\RedirectResponse;

class UrlRedirectController extends Controller
{
    public function __construct(
        private UrlShortenerService $urlShortenerService
    ) {}

    /**
     * GET /{shortCode}
     * * @param string $shortCode Tarayıcıdan gelen 6 karakterli benzersiz kod
     */
    public function __invoke(string $shortCode): RedirectResponse
    {
        
        try {
            $originalUrl = $this->urlShortenerService->getRedirectUrl($shortCode);
            return redirect()->away($originalUrl, 302);

        } catch (Exception $e) {
            abort(404, $e->getMessage());
        }
    }
}