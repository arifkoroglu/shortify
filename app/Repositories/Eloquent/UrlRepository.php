<?php

namespace App\Repositories\Eloquent;

use App\Entities\Url;
use App\Models\Url as UrlModel; // Laravel'in varsayılan Eloquent modeli
use App\Repositories\Contracts\UrlRepositoryInterface;
use DateTime;

class UrlRepository implements UrlRepositoryInterface
{
    public function findByShortCode(string $shortCode): ?Url
    {
        $model = UrlModel::where('short_code', $shortCode)->first();

        if (!$model) {
            return null;
        }

        // Eloquent Model'i, Domain Entity'sine haritalıyoruz (Mapping)
        return $this->mapToEntity($model);
    }

    public function save(Url $url): Url
    {
        // Entity'den gelen verileri Eloquent'in anlayacağı formata sokuyoruz
        $model = UrlModel::updateOrCreate(
            ['id' => $url->getId()],
            [
                'original_url' => $url->getOriginalUrl(),
                'short_code'   => $url->getShortCode(),
                'user_id'      => $url->getUserId(),
                'is_active'    => $url->isActive(),
                'expires_at'   => $url->getExpiresAt(),
            ]
        );

        return $this->mapToEntity($model);
    }

    /**
     * Eloquent çıktısını saf Domain Entity nesnesine dönüştürür.
     */
    private function mapToEntity(UrlModel $model): Url
    {
        return new Url(
            id: $model->id,
            originalUrl: $model->original_url,
            shortCode: $model->short_code,
            userId: $model->user_id,
            isActive: (bool) $model->is_active,
            expiresAt: $model->expires_at ? new DateTime($model->expires_at) : null,
            createdAt: $model->created_at ? new DateTime($model->created_at) : null
        );
    }
}