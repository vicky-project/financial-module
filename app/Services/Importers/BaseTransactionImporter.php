<?php

namespace Modules\Financial\Services\Importers;

use Illuminate\Support\Collection;
use Modules\Financial\Models\Wallet;
use Modules\Financial\Interfaces\TransactionImporterInterface;

abstract class BaseTransactionImporter implements TransactionImporterInterface
{
	protected Collection $data;
	protected Wallet $wallet;
	protected array $headerMapping = [];

	public function __construct(array $data, Wallet $wallet)
	{
		$this->data = collect($data);
		$this->wallet = $wallet;
	}

	protected function mapHeaders(array $headerRow): array
	{
		$mapping = [];
		foreach ($this->headerMapping as $internalKey => $externalKey) {
			$mapping[$internalKey] = array_search($externalKey, $headerRow);
		}
		return $mapping;
	}

	protected function extractDataWithMapping(array $row, array $mapping): array
	{
		$extracted = [];
		foreach ($mapping as $internalKey => $externalIndex) {
			if ($externalIndex !== false && isset($row[$externalIndex])) {
				$extracted[$internalKey] = $row[$externalIndex];
			}
		}
		return $extracted;
	}

	abstract protected function processRow(array $row): array;
}
