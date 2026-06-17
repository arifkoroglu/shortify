<?php

namespace App\Services;

use App\Entities\Url;
use App\Repositories\Contracts\UrlRepositoryInterface;
use App\Services\UrlHashService;
use Illuminate\Support\Facades\Cache;
use DateTime;
use Exception;

class UrlShortenerService
{
    // Redis anahtar ön eki (Best practice: benzersiz ve düzenli olmalı)
    private const CACHE_PREFIX = 'shortify:url:';
    
    // Önbellek süresi (Örn: 24 saat boyunca Redis'te saklansın)
    private const CACHE_TTL = 86400; 

    public function __construct(
        private UrlRepositoryInterface $urlRepository,
        private UrlHashService $urlHashService
    ) {}

    /**
     * Yeni bir kısa link oluşturma senaryosu + Redis Kaydı
     */
    public function shorten(string $originalUrl, ?int $userId = null, ?DateTime $expiresAt = null): Url
    {
        
        $existingUrl = $this->isExistShortenUrl($originalUrl);

        if ($existingUrl !== null) {
            return $existingUrl;
        }
        

        $draftUrl = new Url(
            id: null,
            originalUrl: $originalUrl,
            shortCode: '',
            userId: $userId,
            isActive: true,
            expiresAt: $expiresAt
        );

        $savedUrl = $this->urlRepository->save($draftUrl);
        $shortCode = $this->urlHashService->encode($savedUrl->getId());

        $finalUrl = new Url(
            id: $savedUrl->getId(),
            originalUrl: $savedUrl->getOriginalUrl(),
            shortCode: $shortCode,
            userId: $savedUrl->getUserId(),
            isActive: $savedUrl->isActive(),
            expiresAt: $savedUrl->getExpiresAt(),
            createdAt: $savedUrl->getCreatedAt()
        );

        $resultUrl = $this->urlRepository->save($finalUrl);

        // 🔥 [REDIS] Yeni oluşturulan linki sıcağı sıcağına cache'e atıyoruz (Write-Through)
        $this->putInCache($resultUrl);

        return $resultUrl;
    }

    public function isExistShortenUrl(string $originalUrl): ?Url 
    {
        return $this->urlRepository->findByOriginalUrl($originalUrl);    
    }

    /**
     * Kısa kod ile orijinal linke ulaşma senaryosu (Önce Redis, yoksa DB)
     */
    public function getRedirectUrl(string $shortCode): string
    {
        $cacheKey = self::CACHE_PREFIX . $shortCode;

        // 🔍 1. Önce Redis'e bakıyoruz (Cache Aside Pattern)
        $cachedData = Cache::get($cacheKey);

        if ($cachedData) {
            // Eğer cache'de varsa, array verisini hemen Domain Entity'sine çeviriyoruz
            $url = $this->mapArrayToEntity($cachedData);
        } else {
            // 2. Redis'te yoksa mecburen Veritabanına gidiyoruz
            $url = $this->urlRepository->findByShortCode($shortCode);

            if (!$url) {
                throw new Exception("Kısaltılmış link sistemde bulunamadı.");
            }

            // 3. Veritabanından bulduğumuz linki bir sonraki istekler için Redis'e yazıyoruz
            $this->putInCache($url);
        }

        // 4. İş mantığı kontrollerini yapıyoruz
        if (!$this->isRedirectable($url)) {
            throw new Exception("Bu linkin süresi dolmuş veya link pasife alınmış.");
        }

        return $url->getOriginalUrl();
    }

    // --- YARDIMCI METOTLAR (HELPER METHODS) ---

    /**
     * Entity nesnesini array'e çevirip Redis'e kaydeder.
     */
    private function putInCache(Url $url): void
    {
        $cacheKey = self::CACHE_PREFIX . $url->getShortCode();
        
        $data = [
            'id' => $url->getId(),
            'original_url' => $url->getOriginalUrl(),
            'short_code' => $url->getShortCode(),
            'user_id' => $url->getUserId(),
            'is_active' => $url->isActive(),
            'expires_at' => $url->getExpiresAt()?->format('Y-m-d H:i:s'),
            'created_at' => $url->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];

        Cache::put($cacheKey, $data, self::CACHE_TTL);
    }

    /**
     * Redis'ten gelen array verisini saf Entity nesnesine dönüştürür.
     */
    private function mapArrayToEntity(array $data): Url
    {
        return new Url(
            id: $data['id'],
            originalUrl: $data['original_url'],
            shortCode: $data['short_code'],
            userId: $data['user_id'],
            isActive: (bool) $data['is_active'],
            expiresAt: $data['expires_at'] ? new DateTime($data['expires_at']) : null,
            createdAt: $data['created_at'] ? new DateTime($data['created_at']) : null
        );
    }

    public function isExpired(Url $url): bool
    {
        if ($url->getExpiresAt() === null) {
            return false;
        }
        return $url->getExpiresAt() < new DateTime();
    }

    public function isRedirectable(Url $url): bool
    {
        return $url->isActive() && !$this->isExpired($url);
    }
}