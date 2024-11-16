<?php

namespace App\Http\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    private const AVATAR_PATH = 'avatars';

    /**
     * Store the avatar file and return the file path
     * @param UploadedFile $avatar
     * @return string
     */
    public function storeAvatar(UploadedFile $avatar): string
    {
        $fileName = $avatar->hashName();
        $filePath = self::AVATAR_PATH . DIRECTORY_SEPARATOR  . $fileName;

        if(Storage::disk('public')->exists($filePath)){
            return Storage::url($filePath);
        }

        $storedPath = Storage::disk('public')->putFile(self::AVATAR_PATH, $avatar,'public');

        return Storage::url($storedPath);
    }
}
