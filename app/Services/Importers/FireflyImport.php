<?php

namespace Modules\Financial\Services\Importers;

use Modules\Financial\Models\Category;
use Modules\Financial\Enums\CashflowType;

class FireflyImport extends BaseTransactionImporter
{
	protected array $headerMapping = [
		"amount" => "amount",
		"description" => "description",
		"date" => "date",
		"notes" => "notes",
		"category" => "category",
	];

	public function load(): array
	{
		if ($this->data->isEmpty()) {
			return [];
		}

		$headerRow = $this->data->shift();
		$mapping = $this->mapHeaders($headerRow);

		return $this->data
			->filter(fn($row) => !empty(array_filter($row)))
			->map(function ($row) use ($mapping) {
				$extracted = $this->extractDataWithMapping($row, $mapping);
				return $this->processRow($extracted);
			})
			->all();
	}

	protected function processRow(array $row): array
	{
		$categoryId = $this->getCategoryId($row["category"], $row["amount"]);

		return [
			"wallet_id" => $this->wallet->id,
			"category_id" => $categoryId,
			"date" => now()
				->parse($row["date"])
				->format("Y-m-d H:i:s"),
			"description" => $row["description"] ?? "",
			"amount" => $this->normalizeAmount($row["amount"]),
			"notes" => $row["notes"] ?? null,
			"created_at" => now(),
			"updated_at" => now(),
		];
	}

	private function getCategoryId(string $category, string $amount): int
	{
		$type = str_starts_with($amount, "-")
			? CashflowType::EXPENSE
			: CashflowType::INCOME;

		if ($category === "Transfer") {
			$category = str_starts_with($amount, "-")
				? "Transfer Keluar"
				: "Transfer Masuk";
		}

		return Category::firstOrCreate([
			"user_id" => auth()->id(),
			"name" => $category,
			"type" => $type,
		])->id;
	}

	private function normalizeAmount(string $amount): string
	{
		return str_starts_with($amount, "-") ? substr($amount, 1) : $amount;
	}
}
