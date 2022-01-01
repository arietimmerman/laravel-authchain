<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthChainSubjectLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /**
         * Storage is needed for OIDC because of the UserInfo endpoint. Storing all information in the access token is sub optimal
         */
        Schema::create(
            'authchain_subject_links', function (Blueprint $table) {
                $table->increments('id');

                $table->uuid('user_id')->index();

                $table->string('subject_type', 100);
                $table->string('subject_module', 100)->nullable();

                // $table->string('subject_attribute', 100);
                $table->uuid('subject_id');

                $table->unique(['subject_type', 'subject_module', 'subject_id']);

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oauth_auth_codes');
    }
}
