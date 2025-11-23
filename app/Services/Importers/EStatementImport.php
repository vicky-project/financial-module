<?php

namespace Modules\Financial\Services\Importers;

use Carbon\Carbon;
use Modules\Financial\Models\Category;
use Modules\Financial\Enums\CashflowType;

class EStatementImport extends BaseTransactionImporter
{
	protected string $locale = "Asia/Jakarta";

	protected array $headerMapping = [];

	public function load(): array
	{
		if ($this->data->isEmpty()) {
			return [];
		}

		return $this->data
			->filter(fn($row) => !empty(array_filter($row)))
			->map(fn($row) => $this->processRow($row))
			->all();
	}

	protected function processRow(array $row): array
	{
		$description = $this->cleanDescriptionParts($row["description"]);
		$category = $this->guessCategoryFromDescription(
			$description,
			$row["amount"]
		);

		return [
			"wallet_id" => $this->wallet->id,
			"category_id" => $this->getCategoryId($category),
			"date" => $this->parseDate($row["date"] . " " . $row["time"]),
			"description" => $description,
			"amount" => $this->normalizeAmount($row["amount"]),
			"notes" => null,
			"created_at" => now(),
			"updated_at" => now(),
		];
	}

	public function setLocale(string $locale): self
	{
		$this->locale = $locale;
		return $this;
	}

	private function getCategoryId(array $category): int
	{
		return Category::firstOrCreate([
			"user_id" => auth()->id(),
			"name" => $category["name"],
			"type" => $category["type"],
		])->id;
	}

	private function parseDate(string $date): string
	{
		return Carbon::createFromFormat(
			"d M Y H:i:s T",
			$date,
			$this->locale
		)->format("Y-m-d H:i:s");
	}

	private function cleanDescriptionParts(string $description): string
	{
		$cleaned = preg_replace("/^\d+\s+dari\s+/", "", $description);
		$cleaned = preg_replace("/\d+\s*/", "", $cleaned);

		if (preg_match("/[A-Z]/u", $cleaned, $matches, PREG_OFFSET_CAPTURE)) {
			$firstCapitalPos = $matches[0][1];
			if ($firstCapitalPos > 0) {
				$cleaned = substr($cleaned, $firstCapitalPos);
			}
		}

		return trim($cleaned);
	}

	private function guessCategoryFromDescription(
		string $description,
		$amount
	): array {
		$description = str($description);
		$guessName = config("financial.guess_category_by_text");

		if ($description->contains($guessName["admin"])) {
			return ["name" => "Admin", "type" => CashflowType::EXPENSE];
		}
		if ($description->contains($guessName["pulsa"])) {
			return ["name" => "Pulsa", "type" => CashflowType::EXPENSE];
		}
		if ($description->contains($guessName["tarik_tunai"])) {
			return ["name" => "Tarik Tunai", "type" => CashflowType::EXPENSE];
		}
		if ($description->contains($guessName["rumah"])) {
			return ["name" => "Rumah", "type" => CashflowType::EXPENSE];
		}
		if ($description->contains($guessName["belanja"])) {
			return ["name" => "Shop/E-Walet", "type" => CashflowType::EXPENSE];
		}
		if ($description->contains($guessName["transfer"])) {
			return [
				"name" =>
					"Transfer " . (str($amount)->startsWith("-") ? "Keluar" : "Masuk"),
				"type" => str($amount)->startsWith("-")
					? CashflowType::EXPENSE
					: CashflowType::INCOME,
			];
		}

		return ["name" => "Unknown", "type" => CashflowType::EXPENSE];
	}

	private function normalizeAmount(string $amount): string
	{
		return str($amount)
			->ltrim("-")
			->ltrim("+")
			->replace(".", "")
			->beforeLast(",");
	}
}
