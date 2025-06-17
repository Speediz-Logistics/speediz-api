<?php

namespace App\Traits;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

trait UploadImage
{
    public function upload(Request $request, string $fieldName = 'image')
    {
        if ($request->hasFile($fieldName)) {
            // Handle file upload
            $imagePath = $request->file($fieldName)->getRealPath();
            $uploadedImage = Cloudinary::upload($imagePath)->getSecurePath();
        } elseif ($request->{$fieldName}) {
            // Handle base64 upload
            $imageBase64 = $request->{$fieldName};
            $uploadedImage = Cloudinary::upload("data:image/png;base64," . $imageBase64)->getSecurePath();
        } else {
            return null;
        }

        return $uploadedImage;
    }

    public function updateImage(Request $request, $model, string $fieldName = 'image')
    {

        if ($request->hasFile($fieldName) || $request->{$fieldName}) {
            // Check if old image exists
            if ($model->{$fieldName}) {
                $oldImage = Cloudinary::getImage($model->{$fieldName});
                if ($oldImage && $oldImage->getPublicId() !== null) {
                    Cloudinary::destroy($oldImage->getPublicId());
                }
            }

            // Handle new image upload
            if ($request->hasFile($fieldName)) {
                $imagePath = $request->file($fieldName)->getRealPath();
                $uploadedImage = Cloudinary::upload($imagePath)->getSecurePath();
            } elseif ($request->{$fieldName}) {
                $imageBase64 = $request->{$fieldName};
                $uploadedImage = Cloudinary::upload("data:image/png;base64," . $imageBase64)->getSecurePath();
            }

            $model->{$fieldName} = $uploadedImage;
            $model->save();

            return $uploadedImage;
        } else {
            return null;
        }
    }
}
