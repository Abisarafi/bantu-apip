<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TokenService
{
    // Endpoint untuk mendapatkan token (misalnya dari Jibble API)
    private $tokenUrl = 'https://identity.prod.jibble.io/connect/token';

    // Tentukan key cache untuk menyimpan token
    private $tokenCacheKey = 'access_token';
    private $expireCacheKey = 'access_token_expire_time';

    /**
     * Mendapatkan access token yang valid.
     * Jika token sudah kadaluarsa, maka token baru akan diambil.
     *
     * @return string
     * @throws \Exception
     */
    public function getAccessToken()
    {
        // Cek apakah token masih valid
        if ($this->isTokenExpired()) {
            // Token sudah kadaluarsa, ambil token baru
            return $this->fetchNewAccessToken();
        }

        // Ambil token dari cache
        return Cache::get($this->tokenCacheKey);
    }

    /**
     * Mengecek apakah token sudah kadaluarsa.
     *
     * @return bool
     */
    private function isTokenExpired()
    {
        // Cek apakah ada token yang disimpan dan apakah token tersebut sudah kadaluarsa
        $expireTime = Cache::get($this->expireCacheKey);
        return !$expireTime || Carbon::now()->gt(Carbon::parse($expireTime));
    }

    /**
     * Mengambil token baru dan menyimpannya dalam cache.
     *
     * @return string
     * @throws \Exception
     */
    private function fetchNewAccessToken()
    {
        // Kirim request untuk mendapatkan token baru

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'client_credentials', // Sesuaikan dengan grant_type yang digunakan
            'client_id' => config('jibble.client_id'),  // Gantilah dengan client_id dari environment
            'client_secret' => config('jibble.client_secret'),  // Gantilah dengan client_secret dari environment
        ]);

        // Periksa apakah permintaan berhasil
        if ($response->failed()) {
            throw new \Exception('Failed to retrieve access token');
        }

        // Ambil data token dan durasi berlaku
        $data = $response->json();

        // Simpan token dan waktu kadaluarsa di cache
        $expiresIn = $data['expires_in'];
        $accessToken = $data['access_token'];

        // Hitung waktu kadaluarsa
        $expireTime = Carbon::now()->addSeconds($expiresIn);

        Cache::put($this->tokenCacheKey, $accessToken, $expireTime);
        Cache::put($this->expireCacheKey, $expireTime, $expireTime);

        return $accessToken;
    }
}
