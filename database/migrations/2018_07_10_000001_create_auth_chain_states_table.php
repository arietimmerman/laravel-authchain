<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthChainStatesTable extends Migration
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

        //if (!Schema::hasTable('authchain_ui_settings'))
        Schema::create(
            'authchain_states', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->text('state');

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
