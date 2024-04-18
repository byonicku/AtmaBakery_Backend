<?php

namespace App\Http\Controllers\API\Data;

use Illuminate\Http\Request;

class FunctionHelper
{
    /**
     * Upload image to cloudinary
     *
     * @return string
     */
    public function uploadImage($image, string $imageName)
    {
        $response = cloudinary()->upload($image->getRealPath(), [
            'upload_preset' => 'atma-bakery',
            'filename_override' => $imageName,
            'public_id' => $imageName,
        ]);

        $uploadedFileUrl = $response->getSecurePath();

        return $uploadedFileUrl;
    }

    /**
     * Delete image from cloudinary
     *
     * @return string
     */
    public function deleteImage(string $imageName)
    {
        $publicId = 'atma-bakery/' . $imageName;
        $response = cloudinary()->destroy($publicId);

        return $response;
    }

    /**
     * Creating array of data to be updated
     *
     * @return array
     */
    public function updateDataMaker(array $attribute, Request $request)
    {
        $updateData = [];

        foreach ($attribute as $attr) {
            if ($request->has($attr)) {
                $updateData[$attr] = $request->$attr;
            }
        }

        return $updateData;
    }
}