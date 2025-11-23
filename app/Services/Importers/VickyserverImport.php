<?php

namespace Modules\Financial\Services\Importers;

use Carbon\Carbon;
use Modules\Financial\Models\Category;
use Modules\Financial\Enums\CashflowType;

class VickyserverImport extends BaseTransactionImporter
{
	protected array $headerMapping = [
		"Tanggal" => "Tanggal",
		"Jenis" => "Jenis",
		"Kategori" => "Kategori",
		"Deskripsi" => "Deskripsi",
		"Jumlah" => "Jumlah",
		"Catatan" => "Catatan",
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
		$categoryId = $this->getCategoryId($row["Kategori"], $row["Jenis"]);

		return [
			"wallet_id" => $this->wallet->id,
			"category_id" => $categoryId,
			"date" => $this->parseDate($row["Tanggal"]),
			"description" => $row["Deskripsi"] ?? "",
			"amount" => $this->parseAmount($row["Jumlah"]),
			"notes" => $row["Catatan"] ?? null,
			"created_at" => now(),
			"updated_at" => now(),
		];
	}

	private function getCategoryId(string $category, string $type): int
	{
		return Category::firstOrCreate([
			"user_id" => auth()->id(),
			"name" => $category,
			"type" =>
				$type === "expense" ? CashflowType::EXPENSE : CashflowType::INCOME,
		])->id;
	}

	private function parseDate(string $date): string
	{
		return Carbon::createFromFormat("d/m/Y", $date)->format("Y-m-d H:i:s");
	}

	private function parseAmount(string $amount): string
	{
		$amountParts = explode(" ", $amount);
		$amountValue = $amountParts[1] ?? $amountParts[0];
		return str_replace(".", "", $amountValue);
	}
}
