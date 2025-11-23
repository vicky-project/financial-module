<?php

namespace Modules\Financial\Constants;

class Permissions
{
	// Wallet permissions
	const VIEW_WALLETS = "financial.wallets.view";
	const CREATE_WALLETS = "financial.wallets.create";
	const EDIT_WALLETS = "financial.wallets.edit";
	const DELETE_WALLETS = "financial.wallets.delete";
	const MANAGE_WALLETS = "financial.wallets.manage";

	// Transaction permissions
	const VIEW_TRANSACTIONS = "financial.transactions.view";
	const CREATE_TRANSACTIONS = "financial.transactions.create";
	const EDIT_TRANSACTIONS = "financial.transactions.edit";
	const DELETE_TRANSACTIONS = "financial.transactions.delete";
	const RESTORE_TRANSACTIONS = "financial.transactions.restore";
	const UPLOAD_TRANSACTIONS = "financial.transactions.upload";

	// Category permissions
	const VIEW_CATEGORIES = "financial.categories.view";
	const CREATE_CATEGORIES = "financial.categories.create";
	const EDIT_CATEGORIES = "financial.categories.edit";
	const DELETE_CATEGORIES = "financial.categories.delete";

	// Report permissions
	const VIEW_REPORTS = "financial.reports.view";

	public static function all(): array
	{
		return [
			// Wallet
			self::VIEW_WALLETS => "View wallets",
			self::CREATE_WALLETS => "Create wallets",
			self::EDIT_WALLETS => "Edit wallets",
			self::DELETE_WALLETS => "Delete wallets",
			self::MANAGE_WALLETS => "Manage wallets (Re-calculate balance)",

			// Transaction
			self::VIEW_TRANSACTIONS => "View transactions",
			self::CREATE_TRANSACTIONS => "Create transactions",
			self::EDIT_TRANSACTIONS => "Edit transactions",
			self::DELETE_TRANSACTIONS => "Delete transactions",
			self::RESTORE_TRANSACTIONS => "Restore transactions",
			self::UPLOAD_TRANSACTIONS => "Upload transactions",

			// Category
			self::VIEW_CATEGORIES => "View transactions categories",
			self::CREATE_CATEGORIES => "Create transactions categories",
			self::EDIT_CATEGORIES => "Edit transactions categories",
			self::DELETE_CATEGORIES => "Delete transactions categories",

			// Report
			self::VIEW_REPORTS => "View reporting",
		];
	}
}
