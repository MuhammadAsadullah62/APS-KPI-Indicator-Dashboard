<?php

namespace App\Support;

use App\Enums\MediaType;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * One place for "replace this user's avatar": delete the old file + row,
 * downscale + re-encode the upload, write the new Media row. Replaces the
 * three identical syncAvatar() copies that used to live in the controllers.
 */
final class AvatarService
{
    /** Longest edge kept after the square cover-crop. */
    private const MAX_EDGE = 512;

    private const DISK = 'public';

    /**
     * @return Media|null null when no file was supplied
     */
    public static function replaceFor(User $user, ?UploadedFile $file): ?Media
    {
        if ($file === null) {
            return null;
        }

        $user->mediaItems()
            ->where('collection_name', 'avatar')
            ->get()
            ->each(fn (Media $m) => $m->deleteWithFile());

        [$path, $mime, $size] = self::storeProcessed($file);

        return $user->mediaItems()->create([
            'collection_name' => 'avatar',
            'disk' => self::DISK,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size' => $size,
            'type' => MediaType::Image,
        ]);
    }

    /**
     * @return array{0: string, 1: string, 2: int|null} [path, mime, size]
     */
    private static function storeProcessed(UploadedFile $file): array
    {
        try {
            $image = (new ImageManager(new Driver))->decodeSplFileInfo($file);
            $image->cover(self::MAX_EDGE, self::MAX_EDGE);

            $path = 'avatars/'.Str::ulid()->toBase32().'.webp';
            Storage::disk(self::DISK)->put($path, (string) $image->encode(new WebpEncoder(80)));

            return [$path, 'image/webp', Storage::disk(self::DISK)->size($path) ?: null];
        } catch (\Throwable $e) {
            report($e);

            // Never fail an upload over image processing — fall back to the raw file.
            $path = $file->store('avatars', self::DISK);

            return [$path, $file->getClientMimeType(), $file->getSize() ?: null];
        }
    }
}
