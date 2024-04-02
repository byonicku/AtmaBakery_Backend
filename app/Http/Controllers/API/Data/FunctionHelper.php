<?php

namespace App\Http\Controllers\API\Data;

class FunctionHelper
{
    /**
     * Upload image to cloudinary
     *
     * @return string
     */
    public function uploadImage($image, $imageName)
    {
        $response = cloudinary()->upload($image->getRealPath(), [
            'upload_preset' => 'atma-bakery',
            'filename_override' => $imageName,
            'public_id' => $imageName,
        ]);

        $uploadedFileUrl = $response->getSecurePath();

        return $uploadedFileUrl;
    }
}
