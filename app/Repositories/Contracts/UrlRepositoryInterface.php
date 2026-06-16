<?php

namespace App\Repositories\Contracts;

use App\Entities\Url;

interface UrlRepositoryInterface
{
    /**
     * Kısa koda göre URL entity'sini bulur.
     */
    public function findByShortCode(string $shortCode): ?Url;

    /**
     * Yeni bir URL kaydeder veya günceller.
     */
    public function save(Url $url): Url;
}