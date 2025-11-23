<?php

return [
	"name" => "Financial",
	"debug" => false,

	/**
	 * Configuration database tables name.
	 */
	"tables_name" => [
		"transactions" => "transactions",
		"categories" => "categories",
		"wallets" => "wallets",
	],

	/**
	 * Guess category name by description text using include text bellow inside description text. This useful when import e-staatement from any bank. You can add more for accurating guess.
	 */
	"guess_category_by_text" => [
		"admin" => ["Biaya", "debit", "kartu kredit"],
		"rumah" => ["PLN", "Telkom/Indihome", "PERUMAHAN"],
		"belanja" => ["ShopeePay", "Shopee", "GRAB", "Danatopup"],
		"transfer" => ["Transfer", "Pembayaran", "QR"],
		"pulsa" => ["IM3", "Telkomsel", "IMOoredoo"],
		"tarik_tunai" => ["Tarik tunai", "tunai", "ATM"],
	],

	/**
	 * List of class that parsing data from banking.
	 */
	"bank_parsers" => [\Modules\Financial\Parsers\MandiriStatementParser::class],
];
