<?php

namespace App\Http\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

readonly class FileService
{
    private const AVATAR_PATH = 'avatars';
    private const ICON_PATH = 'icons';

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

    public function storeIcon(UploadedFile $icon): string
    {
        $fileName = $icon->hashName();
        $filePath = self::ICON_PATH . DIRECTORY_SEPARATOR  . $fileName;

        if(Storage::disk('public')->exists($filePath)){
            return Storage::url($filePath);
        }

        $storedPath = Storage::disk('public')->putFile(self::ICON_PATH, $icon,'public');

        return Storage::url($storedPath);
    }

    public function deleteIcon(string $iconPath): bool
    {
        $iconPath = str_replace(Storage::url(''), '', $iconPath);
        if(!Storage::disk('public')->exists($iconPath)){
            return true;
        }
        return Storage::disk('public')->delete($iconPath);
    }
}
