<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

echo "About to run use DB statement.";
//use DB;

echo "About to check version";
//$results = DB::select( DB::raw("select version()") );
echo "Got version number";
//$mysql_version =  $results[0]->{'version()'};
//$mariadb_version = '';

/*if (strpos($mysql_version, 'Maria') !== false) {
    $mariadb_version = $mysql_version;
    $mysql_version = '';
}
*/

class CreateInitialDatabaseSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('location_search_option', function (Blueprint $table) {
			$table->increments('id');
			$table->string('content', 255);
		});
		Schema::create('faq_item', function (Blueprint $table) {
			$table->increments('id');
			$table->string('question', 255);
			$table->string('answer_html', 1024);
		});
		Schema::create('data_source', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 100);
			$table->string('description', 255)->nullable();
		});
		Schema::create('country', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 255);
			$table->unique('name');
		});
		Schema::create('region', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('country_id')->unsigned();
			$table->foreign('country_id')->references('id')->on('country');
			$table->string('name', 255);
			$table->unique('name');
		});
		Schema::create('user', function (Blueprint $table) {
			$table->increments('id');
			$table->boolean('uses_screen_reader')->default(false);
			$table->string('email', 255)->unique()->nullable();
			$table->string('first_name', 255)->nullable();
			$table->string('last_name', 255)->nullable();
			$table->string('home_city', 255)->nullable();
			$table->string('home_zipcode', 50)->nullable();
			$table->string('home_region', 255)->nullable();
			$table->integer('home_country_id')->unsigned()->nullable();
			$table->foreign('home_country_id')->references('id')->on('country');
			$table->char('password_hash', 60)->nullable();
			$table->string('remember_token', 60)->nullable();
			$table->double('search_radius_km', 11, 6)->nullable();
			$table->double('longitude', 11, 8)->nullable();
			$table->double('latitude', 11, 8)->nullable();
			$table->string('location_search_text', 255)->nullable();
			$table->string('email_verification_token', 255)->nullable();
			$table->string('password_recovery_token', 60)->nullable();
			$table->datetime('email_verification_time')->nullable();
		});
		Schema::create('role', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 100)->unique();
			$table->string('description', 255);
		});
		Schema::create('user_role', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('user');
			$table->integer('role_id')->unsigned();
			$table->foreign('role_id')->references('id')->on('role');
			$table->unique(array('role_id', 'user_id'));
		});
		Schema::create('question_category', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 255);
		});
		Schema::create('question', function (Blueprint $table) {
			$table->increments('id');
			$table->string('question_html');
			$table->integer('question_category_id')->unsigned()->nullable();
			$table->foreign('question_category_id')->references('id')->on('question_category');
			$table->boolean('is_always_required')->default(false);
		});
		Schema::create('user_question', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('question_id')->unsigned();
			$table->foreign('question_id')->references('id')->on('question');
			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('user');
			$table->unique(array('question_id', 'user_id'));
		});
		Schema::create('location_group', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 255);
			$table->string('external_web_url', 255)->nullable();
		});
		Schema::create('location', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('owner_user_id')->unsigned()->nullable();
			$table->foreign('owner_user_id')->references('id')->on('user');
			$table->integer('location_group_id')->unsigned()->nullable();
			$table->foreign('location_group_id')->references('id')->on('location_group');
			$table->integer('data_source_id')->unsigned();
			$table->foreign('data_source_id')->references('id')->on('data_source');
			$table->string('name', 255)->nullable();
			$table->string('external_web_url', 255)->nullable();
			$table->string('address', 255)->nullable();
			$table->string('phone_number', 50)->nullable();
			
			// universal_rating is a cached value.
			// It maintained to avoid doing expensive/slow queries repeatedly.
			$table->double('universal_rating', 11, 8)->nullable();
			$table->double('longitude', 11, 8);
			$table->double('latitude', 11, 8);
		});
		Schema::create('location_duplicate', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('location_id')->unsigned();
			$table->foreign('location_id')->references('id')->on('location');
			$table->integer('data_source_id')->unsigned();
			$table->foreign('data_source_id')->references('id')->on('data_source');
			$table->string('external_id', 255)->nullable();
			$table->string('name', 255);
		});
		Schema::create('user_location', function (Blueprint $table) {
			$table->increments('id');

			$table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('user');
			$table->integer('location_id')->unsigned();
			$table->foreign('location_id')->references('id')->on('location');
			
			// personalized_rating is a cached value.
			// It maintained to avoid doing expensive/slow queries repeatedly.
			$table->double('personalized_rating', 11, 8);
			$table->datetime('when_submitted');
			
			// There is no point to have more than one association between the same user and location.
			$table->unique(array('location_id', 'user_id'));
		});
		Schema::create('user_answer', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('answered_by_user_id')->unsigned();
			$table->foreign('answered_by_user_id')->references('id')->on('user');
			$table->integer('question_id')->unsigned();
			$table->foreign('question_id')->references('id')->on('question');
			$table->integer('location_id')->unsigned();
			$table->foreign('location_id')->references('id')->on('location');
			
			/*
			 0 = No, the location does not meet the feature/requirement in question.
			 1 = Yes, the location has the feature/requirement in question
			 2 = Not required.  The location may or may not have the feature/requirement 
			in question but it doesn't matter because the location doesn't need to have
			it to meet the underlying accessibility needs.
			*/
			$table->string('answer_value', 255);
			$table->datetime('when_submitted');
		});
		Schema::create('review_comment', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('answered_by_user_id')->unsigned();
			$table->foreign('answered_by_user_id')->references('id')->on('user');
			$table->integer('location_id')->unsigned();
			$table->foreign('location_id')->references('id')->on('location');
			$table->integer('question_category_id')->unsigned()->nullable();
			$table->foreign('question_category_id')->references('id')->on('question_category');
			$table->mediumText('content');
			$table->datetime('when_submitted');
		});
		Schema::create('location_tag', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 255);
			$table->unique('name');
			$table->string('description', 255);
		});
		Schema::create('location_location_tag', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('location_id')->unsigned();
			$table->foreign('location_id')->references('id')->on('location');
			$table->integer('location_tag_id')->unsigned();
			$table->foreign('location_tag_id')->references('id')->on('location_tag');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    	
        Schema::dropIfExists('location_location_tag');
        Schema::dropIfExists('location_tag');
        Schema::dropIfExists('review_comment');
        Schema::dropIfExists('user_answer');
        Schema::dropIfExists('user_location');
        Schema::dropIfExists('location_duplicate');
        Schema::dropIfExists('location');
        Schema::dropIfExists('location_group');
        Schema::dropIfExists('user_question');
        Schema::dropIfExists('question');
        Schema::dropIfExists('question_category');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('user');
        Schema::dropIfExists('role');
        Schema::dropIfExists('region');
        Schema::dropIfExists('country');
        Schema::dropIfExists('data_source');
        Schema::dropIfExists('faq_item');
		Schema::dropIfExists('location_search_option');
    }
}
