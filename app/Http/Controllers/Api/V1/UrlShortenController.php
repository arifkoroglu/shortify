<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShortenUrlRequest;
use App\Services\UrlShortenerService;
use Exception;
use Illuminate\Http\JsonResponse;
use DateTime;

class UrlShortenController extends Controller
{
    // Yaratıcı gücümüz olan servisi constructor bağımlılığı olarak içeri alıyoruz
    public function __construct(
        private UrlShortenerService $urlShortenerService
    ) {}

    /**
     * POST /api/v1/shorten
     */
    public function __invoke(ShortenUrlRequest $request): JsonResponse
    {
        try {
            // 1. Request içindeki doğrulanmış verileri alıyoruz
            $originalUrl = $request->input('url');
            $expiresAtInput = $request->input('expires_at');
            
            $expiresAt = $expiresAtInput ? new DateTime($expiresAtInput) : null;

            // 2. Arka plandaki iş motorumuzu (Servisimizi) tetikliyoruz
            // (Auth kısmını sonraya bıraktığımız için userId'yi şimdilik null geçiyoruz)
            $urlEntity = $this->urlShortenerService->shorten($originalUrl, null, $expiresAt);

            // 3. Kullanıcıya başarı mesajını ve üretilen kısa linki dönüyoruz
            return response()->json([
                'success' => true,
                'message' => 'Link başarıyla kısaltıldı.',
                'data'    => [
                    'original_url' => $urlEntity->getOriginalUrl(),
                    'short_code'   => $urlEntity->getShortCode(),
                    // Kullanıcının tıklayacağı tam URL'i de dinamik basalım
                    'short_url'    => config('app.url') . '/' . $urlEntity->getShortCode(),
                    'expires_at'   => $urlEntity->getExpiresAt()?->format('Y-m-d H:i:s')
                ]
            ], 201); // 201: Created HTTP kodu

        } catch (Exception $e) {
            // Beklenmedik bir hata oluşursa yakalayıp API standartlarında dönüyoruz
            return response()->json([
                'success' => false,
                'message' => 'Link kısaltılırken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}