<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HandlesImageUploads
{
    private function uploadToCloudinary($file, string $folder = 'pos/avatars'): string
    {
        $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');
        $timestamp = time();
        $signature = sha1("folder={$folder}&timestamp={$timestamp}{$apiSecret}");

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POSTFIELDS => [
                'file' => new \CURLFile($file->getRealPath(), $file->getMimeType(), $file->getClientOriginalName()),
                'api_key' => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
                'folder' => $folder,
            ],
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("Cloudinary upload failed: {$error}");
        }

        $data = json_decode($response, true);

        if (! isset($data['secure_url'])) {
            throw new \Exception('Cloudinary error: ' . ($data['error']['message'] ?? 'Unknown error'));
        }

        return $data['secure_url'];
    }

    private function handleProductImage(Request $request, $existing = null)
    {
        if ($request->hasFile('image_file')) {
            return $this->uploadToCloudinary($request->file('image_file'));
        }
        return $request->image_url ?? $existing;
    }

    private function handleAvatarImage(Request $request, $existing = null)
    {
        if ($request->hasFile('avatar_file')) {
            return $this->uploadToCloudinary($request->file('avatar_file'));
        }
        return $request->avatar_url ?? $existing;
    }
}
