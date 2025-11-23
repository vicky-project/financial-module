<?php

use Illuminate\Support\Facades\Route;
use Modules\Financial\Http\Controllers\WalletController;
use Modules\Financial\Http\Controllers\TransactionController;
use Modules\Financial\Http\Controllers\CategoryController;
use Modules\Financial\Http\Controllers\ChartController;

Route::middleware(["auth", "web"])
	->prefix("apps")
	->name("financial.")
	->group(function () {
		Route::get("wallets/{wallet}/recalculate", [
			WalletController::class,
			"recalculateBalance",
		])->name("wallets.recalculate");
		Route::get("wallets/reports", ChartController::class)->name("reports");
		Route::get("wallets/recalculate-all", [
			WalletController::class,
			"recalculateAllBalance",
		])->name("wallets.recalculate-all");
		Route::post("wallets/{wallet}/transactions/upload", [
			TransactionController::class,
			"upload",
		])->name("wallets.transactions.upload");
		Route::get("wallets/{wallet}/transactions/detail", [
			TransactionController::class,
			"detail",
		])->name("wallets.transactions.detail");
		Route::delete("wallets/{wallet}/transactions/delete", [
			TransactionController::class,
			"massDestroy",
		])->name("wallets.transactions.mass-destroy");
		Route::get("wallets/{wallet}/transactions/trash", [
			TransactionController::class,
			"trash",
		])->name("wallets.transactions.trash");
		Route::delete("wallets/{wallet}/transaction/force-delete-all", [
			TransactionController::class,
			"forceDeleteAll",
		])->name("wallets.transactions.force-delete-all");
		Route::post("wallets/{wallet}/transaction/restore-all", [
			TransactionController::class,
			"restoreAll",
		])->name("wallets.transactions.restore-all");
		Route::post("wallets/{wallet}/transaction/{transaction}/restore", [
			TransactionController::class,
			"forceDelete",
		])->name("wallets.transactions.restore");
		Route::delete("wallets/{wallet}/transaction/{transaction}/force-delete", [
			TransactionController::class,
			"forceDelete",
		])->name("wallets.transactions.force-delete");

		Route::resource("wallets", WalletController::class);
		Route::resource("wallets.transactions", TransactionController::class);

		Route::get("categories/{category}/transactions", [
			CategoryController::class,
			"transactions",
		])->name("categories.transactions.index");

		Route::resource("categories", CategoryController::class);
	});
