<?php

namespace App\Http\Controllers;

use App\BaseUser;
use App\Helpers\UserHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class ProfilePhotoController extends Controller
{
    const PROFILE_PATH = 'app/private/user_profile_images';
    const MAX_PHOTO_HEIGHT = 200;
    const MAX_PHOTO_WIDTH = 730;

    /**
     * Returns the user profile photo.
     * @return mixed
     */
    public function getUserPhoto()
    {
        return UserHelper::build()->getProfilePhoto(Auth::user());
    }

    /**
     * Uploads new Profile Photo.
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function upload(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'profile_photo' => 'required|file|image|dimensions:min_width=32,min_height=32'
        ]);

        if ($validator->fails()) {
            return redirect('/profile')->withErrors($validator->errors());
        }

        $user = Auth::user();
        $profilePhoto = $request->file('profile_photo');
        $storagePath = storage_path('app/private/user_profile_images/');

        if (!is_null($profilePhoto)) {
            $image = $request->file('profile_photo');
            $resource = imagecreatefromstring(file_get_contents($image));
            $xDimension = imagesx($resource);
            $yDimension = imagesy($resource);
            $scaledDimensions = $this->getScaledDimensions($xDimension, $yDimension);
            $finalImage = $this->createBackgroundWhiteInPNG($resource, $xDimension, $yDimension, $scaledDimensions);
            imagejpeg($finalImage, $storagePath."user_".$user->id.".jpg");
            return redirect('/profile?show_rotate_feature=true');
        } else {
            return redirect('/profile')->withErrors([
                'profile_photo' => ['Unable to change your photo!']
            ]);
        }
    }

    /**
     * Removes the profile photo of the user.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(Request $request)
    {
        $user = Auth::user();
        $imagePath = storage_path(self::PROFILE_PATH."/user_".$user->id.".jpg");
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        return redirect('/profile');
    }

    /**
     * Rotates the user profile photo.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function rotate()
    {
        $user = Auth::user();
        $image = UserHelper::build()->getProfilePhoto($user);
        $imagePath = storage_path(self::PROFILE_PATH."/user_".$user->id.".jpg");
        $rotatedImage = imagerotate(imagecreatefromstring($image), -90, 0);

        $xDimension = imagesx($rotatedImage);
        $yDimension = imagesy($rotatedImage);
        $scaledDimensions = $this->getScaledDimensions($xDimension, $yDimension);
        $finalImage = $this->createBackgroundWhiteInPNG($rotatedImage, $xDimension, $yDimension, $scaledDimensions);
        imagejpeg($finalImage, $imagePath);
        return redirect('/profile?show_rotate_feature=true');
    }

    /**
     * Helper function to get the scaled dimensions.
     * @param $width
     * @param $height
     * @return array
     */
    private function getScaledDimensions($width, $height)
    {
        $aspectRatio = ($width * 1.0) / $height;
        $actualRatio = (self::MAX_PHOTO_WIDTH * 1.0) / self::MAX_PHOTO_HEIGHT;
        if (($aspectRatio > $actualRatio) && ($width > self::MAX_PHOTO_WIDTH)) {
            // scale down the image to have a 730 pixel width.
            $width = self::MAX_PHOTO_WIDTH;
            $height = round($width/$aspectRatio);
        } elseif ($height > self::MAX_PHOTO_HEIGHT) {
            $height = self::MAX_PHOTO_HEIGHT;
            $width = self::MAX_PHOTO_HEIGHT * $aspectRatio;
        }
        return [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * Helper function to get the PNG image with the white background.
     * @param $image
     * @param $width
     * @param $height
     * @param $scaledDimension
     * @return bool|resource
     */
    private function createBackgroundWhiteInPNG($image, $width, $height, $scaledDimension)
    {
        $output = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($output, 255, 255, 255);
        imagefilledrectangle($output, 0, 0, $width, $height, $white);
        imagecopy($output, $image, 0, 0, 0, 0, $width, $height);
        return imagescale($output, $scaledDimension['width'], $scaledDimension['height']);
    }
}
