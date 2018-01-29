<?php namespace App\Http\Controllers;

use App\BaseUser;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class ProfilePhotoUploadController extends Controller {

    public function index(Request $request)
    {
        if (BaseUser::isSignedIn())
        {
            return view('pages.profile.photo_upload');
        }
        else
        {
            return redirect()->intended('signin');
        }
    }
	
	private static function getUploadDirectory()
	{
		return realpath(public_path() . '/../storage/app/private/user_profile_images').'\\';
	}
	
	private static function getProfilePhotoPath()
	{
		$user = BaseUser::getDbUser();
		return ProfilePhotoUploadController::getUploadDirectory() . 'user_' . $user->id . '.jpg';
	}
	
	public static function hasProfilePhoto()
	{
		return file_exists(ProfilePhotoUploadController::getProfilePhotoPath());
	}
	
	/**
	Returns a string with the original file extension but named with the specified user id.
	
	For example, getFileNameFromOriginalName('00000000-0000-0000-0000-000000000003', 'Bobby.png') returns 'user_00000000-0000-0000-0000-000000000003.png'.
	*/
	private static function getFileNameFromOriginalName(string $user_id, string $originalFilename)
	{
		$dotIndex = strrpos($originalFilename, '.');
		if ( $dotIndex === FALSE )
			throw new Exception("No extension found");

		$extension = strtolower(substr($originalFilename, $dotIndex + 1));
		if ( strlen($extension) > 4 )
			throw new Exception("Extension is too long");
		
		return 'user_' . $user_id . '.' . $extension;
	}

	/**
	Sends the current user's photo to the client
	*/
	public function photo()
	{
        if (BaseUser::isSignedIn())
        {
			$file_path = ProfilePhotoUploadController::getProfilePhotoPath();
			return response()->file($file_path);
        }
        else
        {
            return redirect()->intended('signin');
        }
	}

	private static function getPhotoDimensionsFromUploadDimensions($width, $height)
	{
		$aspect_ratio = ($width * 1.0) / $height;
		$max_height = 200;
		$max_width = 730;
		
		if ( $aspect_ratio > ($max_width * 1.0) / $max_height )
		{
			if ( $width > $max_width )
			{
				// scale down the image to have a 730 pixel width.
				$width = $max_width;
				$height = round($max_width / $aspect_ratio);
			}
		}
		else if ( $height > $max_height )
		{
			$height = $max_height;
			$width = $max_height * $aspect_ratio;
		}
		return ['width' => $width, 'height' => $height];
	}
	
	public static function save($image)
	{
		$destinationPath = ProfilePhotoUploadController::getUploadDirectory();

		$user = BaseUser::getDbUser();

		$full_path = $destinationPath . 'user_' . $user->id . '.jpg';

		$width = imagesx($image);
		$height = imagesy($image);
		$newDimensions = ProfilePhotoUploadController::getPhotoDimensionsFromUploadDimensions($width, $height);
		
		// png images can be transparent and the transparent areas are defaulted to black.
		// We want the background to default to white.
		// This solution was found at:
		// http://stackoverflow.com/questions/2569970/gd-converting-a-png-image-to-jpeg-and-making-the-alpha-by-default-white-and-not
		$output = imagecreatetruecolor($width, $height);
		$white = imagecolorallocate($output,  255, 255, 255);
		imagefilledrectangle($output, 0, 0, $width, $height, $white);
		imagecopy($output, $image, 0, 0, 0, 0, $width, $height);
		$image = $output;
		
		$scaled_result = imagescale ( $image , $newDimensions['width'], $newDimensions['height']);
		imagejpeg($scaled_result, $full_path);
		
		/**
		Redirect to profile and ask the browser to clear its cache.
		The cache clearing was to fix a problem where the new profile
		photo wouldn't refresh itself properly.
		
		I couldn't find a cache clearing option in Laravel's redirect so I used PHP's header function instead.
		This was adapted from:
		http://stackoverflow.com/questions/1571973/best-way-redirect-reload-pages-in-php
		*/
		header('Location: /profile', true, 302);
		exit(0);
	}

	public function post(Request $request)
	{
        if (BaseUser::isSignedIn())
        {
			$validation_rules = array(
				'profile_photo' => 'required|file|image|dimensions:min_width=32,min_height=32',
			);
			$validator = Validator::make(Input::all(), $validation_rules);
			if ($validator->fails())
			{
				return Redirect::to('profile-photo-upload')->withErrors($validator)->withInput();	
			}

			$temp_filename = $_FILES['profile_photo']['tmp_name'];
			$content = file_get_contents($temp_filename);
			$new_image = imagecreatefromstring($content);

			ProfilePhotoUploadController::save($new_image);
		}
        else
        {
            return redirect()->intended('signin');
        }
	}

	public function delete()
	{
        $file = ProfilePhotoUploadController::getProfilePhotoPath();
        if (!unlink($file))
        {
            echo ("Error deleting $file");
        }
        else
        {
            return redirect()->intended('profile');
        }
	}

	// Rotate Profile Photo 
	public function rotate()
	{
		$current_photo = ProfilePhotoUploadController::getProfilePhotoPath();

		$content = file_get_contents($current_photo);
		$image = imagecreatefromstring($content);

		$new_image = imagerotate($image, -90, 0);
		ProfilePhotoUploadController::save($new_image);
	}

}