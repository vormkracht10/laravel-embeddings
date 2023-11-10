<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        $this->connection = config('gpt.database.connection');
    }

    public function up()
    {
        Schema::create(config('gpt.database.table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('foreign_id');
            $table->text('content');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE ? ADD COLUMN embedding vector(1536) NULL;', [config('gpt.database.table')]);
    }
};
