<?php

namespace Modules\Financial\Helpers;

use Modules\Financial\Models\Category;
use Modules\Financial\Enums\CashflowType;

class Helper
{
	public static function getColortextAmount(Category $category)
	{
		return match ($category->type) {
			CashflowType::INCOME => "text-success",
			CashflowType::EXPENSE => "text-danger",
		};
	}
}
