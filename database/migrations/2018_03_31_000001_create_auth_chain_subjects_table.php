<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthChainSubjectsTable extends Migration
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
            'authchain_subjects', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('identifier', 100)->index();
                $table->text('subject')->nullable();

                // some subject are not linked to an user
                $table->uuid('user_id')->nullable();

                $table->string('levels')->nullable();

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
