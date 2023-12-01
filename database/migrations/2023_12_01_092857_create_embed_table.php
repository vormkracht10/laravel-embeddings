<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        $this->connection = config('embed.database.connection');
    }

    public function up()
    {
        if (Schema::connection($this->connection)->hasTable(config('embed.database.table'))) {
            return;
        }

        Schema::create(config('embed.database.table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('foreign_id');
            $table->text('content');
            $table->timestamps();
        });

        $tableName = config('embed.database.table');

        DB::statement("ALTER TABLE {$tableName} ADD COLUMN embedding vector(1536) NULL;");
    }
};
