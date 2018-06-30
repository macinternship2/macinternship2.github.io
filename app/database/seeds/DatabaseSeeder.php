<?php

use Illuminate\Database\Seeder;
use Database\Seeds\UserTableSeeder;
use Database\Seeds\LocationImageSeeder;

require_once('SeedHelper.php');

class DatabaseSeeder extends Seeder
{
	private static function deleteDataFromTables($tableNames)
	{
		foreach ($tableNames as $tableName) {
			DB::table($tableName)->delete();
		}
	}
	
	private static function insertDataToTables($tableNames)
	{
		foreach (array_reverse($tableNames) as $table_name) {
			$data = SeedHelper::readTableData($table_name . '.json');
			foreach (array_chunk($data, 1000) as $t) {
                DB::table($table_name)->insert($t);
            }
		}
	}
		
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$tables_to_seed_using_json = ['location_search_option',
			'faq_item', 'role', 'location_location_tag', 'location_duplicate',
			'location', 'location_group', 'location_tag', 'question',
			'question_category','region', 'country', 'data_source'];
		$user_data_tables = ['user_answer', 'review_comment'];
		
		DatabaseSeeder::deleteDataFromTables($user_data_tables);
		DatabaseSeeder::deleteDataFromTables($tables_to_seed_using_json);
		DatabaseSeeder::insertDataToTables($tables_to_seed_using_json);

		$this->call('UserTableSeeder');
		$this->call('LocationImageSeeder');

		DatabaseSeeder::insertDataToTables($user_data_tables);
    }
}
