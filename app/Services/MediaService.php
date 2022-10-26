<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function decodebase64($request)
    {
        $image_64 = $request; //your base64 encoded data
        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
        // find substring fro replace here eg: data:image/png;base64,
        $image = str_replace($replace, '', $image_64);
        $image = str_replace(' ', '+', $image);
        $imageName = uniqid() . '.' . $extension;
        $type = $this->detectType($extension);

        Storage::disk('product')->put($imageName, base64_decode($image));
        return [
            'path' => $imageName ,
            'type' => $type
        ];
    }
    

    private function detectType($file)
    {
        $image = ['jpg', 'jpeg', 'png'];
        $video = ['mp4', 'avi'];
        $is_image = in_array($file, $image);
        $is_video = in_array($file, $video);

        if (!$is_video && !$is_image) {
            return 'file';
        }

        if ($is_image) {
            return 'image';
        }
        if ($is_video) {
            return 'video';
        }
    }
}
