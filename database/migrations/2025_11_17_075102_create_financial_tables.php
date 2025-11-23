<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$tablesName = config("financial.tables_name");
		Schema::create($tablesName["wallets"], function (Blueprint $table) {
			$table->id();
			$table
				->foreignId("user_id")
				->constrained("users")
				->onDelete("RESTRICT")
				->cascadeOnUpdate();
			$table->string("wallet_name");
			$table->bigInteger("wallet_number")->unique();
			$table->string("wallet_type")->default("savings");
			$table->bigInteger("balance")->default(0);
			$table->bigInteger("initial_balance")->default(0);
			$table->string("currency")->default("IDR");
			$table->timestamps();
		});

		Schema::create($tablesName["categories"], function (Blueprint $table) {
			$table->id();
			$table
				->foreignId("user_id")
				->constrained("users")
				->onDelete("RESTRICT")
				->cascadeOnUpdate();
			$table->string("name")->unique();
			$table->string("icon")->nullable();
			$table->string("type")->default("income");
			$table->timestamps();
		});

		Schema::create($tablesName["transactions"], function (Blueprint $table) {
			$table->id();
			$table
				->foreignId("category_id")
				->constrained("categories")
				->onDelete("RESTRICT")
				->cascadeOnUpdate();
			$table
				->foreignId("wallet_id")
				->constrained("wallets")
				->onDelete("RESTRICT")
				->cascadeOnUpdate();
			$table->dateTime("date");
			$table->text("description")->fullText();
			$table->bigInteger("amount")->default(0);
			$table
				->text("notes")
				->nullable()
				->fullText();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$tablesName = config("financial.tables_name");
		foreach ($tablesName as $table) {
			Schema::dropIfExists($table);
		}
	}
};
