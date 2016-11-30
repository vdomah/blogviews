<?php namespace Vdomah\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePostViewsTable extends Migration
{
    public function up()
    {
        Schema::create('vdomah_blogviews_views', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('views');
            $table->integer('post_id')->unsigned()->nullable()->index();
            $table->primary(array('post_id'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('vdomah_blogviews_views');
    }
}
