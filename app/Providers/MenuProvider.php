<?php

namespace Modules\Financial\Providers;

use Modules\MenuManagement\Interfaces\MenuProviderInterface;
use Modules\Financial\Constants\Permissions;

class MenuProvider implements MenuProviderInterface
{
	/**
	 * Get Menu for LogManagement Module.
	 */
	public static function getMenus(): array
	{
		return [
			[
				"id" => "financial",
				"name" => "Financial",
				"order" => 10,
				"icon" => "dollar",
				"role" => "user",
				"type" => "group",
				"children" => [
					[
						"id" => "financial-wallet",
						"name" => "Wallets",
						"order" => 10,
						"icon" => "wallet",
						"route" => "financial.wallets.index",
						"role" => "user",
						"permission" => Permissions::VIEW_WALLETS,
					],
					[
						"id" => "financial-category",
						"name" => "Categories",
						"order" => 11,
						"icon" => "tags",
						"route" => "financial.categories.index",
						"role" => "user",
						"permission" => Permissions::VIEW_CATEGORIES,
					],
					[
						"id" => "financial-report",
						"name" => "Reports",
						"order" => 12,
						"icon" => "chart-line",
						"route" => "financial.reports",
						"role" => "user",
						"permission" => Permissions::VIEW_REPORTS,
					],
				],
			],
		];
	}
}
