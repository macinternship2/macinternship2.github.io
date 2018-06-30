<?php
use Illuminate\Database\Seeder;
use App\User;
use App\Image;

class LocationImageSeeder extends Seeder
{
	private function getGuidFromFilename($filename)
	{
		// chop the extension of the filename.
		return substr($filename, 0, 36);
	}

	private function getLocationImages($default_user_id, $dir_name, $location_id)
	{
		$location_dir_name = $dir_name . '/' . $location_id;
		$images = [];
		if ($handle = opendir($location_dir_name)) {
			// loop through images.
			while (false !== ($entry = readdir($handle))) {
				if ( strpos($entry, '.jpg') !== false ) {
					$filename = $location_dir_name . '/' . $entry;
					$image = new Image();
					$image->id = $this->getGuidFromFilename($entry);
					$image->uploader_user_id = $default_user_id;
					$image->location_id = $location_id;
					$image->raw_data = file_get_contents($filename);
					$image->save();
				}
			}
			closedir($handle);
		}
		return $images;
	}
	
	public function run()
	{
		DB::table('image')->delete();
		// Get default upload user.
		$default_uploader_user_id = '00000000-0000-0000-0000-000000000001';
		$images = [];
		$dir_name = 'database/seeds/data/location_images';
		// loop through locations with images.
		if ($handle = opendir($dir_name)) {
			// loop through images.
			while (false !== ($entry = readdir($handle))) {
				if ($entry !== "." && $entry !== ".." && is_dir($dir_name . '/' . $entry) ) {
					$location_images = $this->getLocationImages($default_uploader_user_id, $dir_name, $entry);
				}
			}
			closedir($handle);
		}
	}
}