<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function __construct()
    {
        $this->connection = config('gpt.database.connection');
    }

    public function up()
    {
        if (Schema::connection($this->connection)->hasTable(config('gpt.database.table'))) {
            return;
        }
        
        Schema::create(config('gpt.database.table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('foreign_id');
            $table->text('content');
            $table->timestamps();
        });

        $tableName = config('gpt.database.table');
        
        DB::statement("ALTER TABLE {$tableName} ADD COLUMN embedding vector(1536) NULL;");
    }
};
