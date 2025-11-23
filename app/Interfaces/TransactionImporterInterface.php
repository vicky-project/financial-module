<?php

namespace Modules\Financial\Interfaces;

use Modules\Financial\Models\Wallet;

interface TransactionImporterInterface
{
	public function __construct(array $data, Wallet $wallet);

	public function load(): array;
}
