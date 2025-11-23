<?php

namespace Modules\Financial\Services;

use Modules\Financial\Models\Wallet;
use Modules\Financial\Services\Importers\FireflyImport;
use Modules\Financial\Services\Importers\VickyserverImport;
use Modules\Financial\Services\Importers\EStatementImport;
use Modules\Financial\Interfaces\FileReaderInterface;
use Modules\Financial\Interfaces\TransactionImporterInterface;
use Modules\Financial\Services\FileReaders\PdfReader;
use Modules\Financial\Services\FileReaders\SpreadsheetReader;

class ImportServiceFactory
{
	public static function createReader(
		string $fileType,
		string $filepath,
		?string $password = null
	): FileReaderInterface {
		return match (strtolower($fileType)) {
			"pdf" => new PdfReader($filepath, $password),
			"xlsx", "xls", "csv", "ods" => new SpreadsheetReader($filepath),
			default => throw new \InvalidArgumentException(
				"Unsupported file type: {$fileType}"
			),
		};
	}

	public static function createImporter(
		string $appName,
		array $data,
		Wallet $wallet
	): TransactionImporterInterface {
		$importerClass = match ($appName) {
			"firefly" => FireflyImport::class,
			"vickyserver" => VickyserverImport::class,
			"e-statement" => EStatementImport::class,
			default => throw new \InvalidArgumentException(
				"Unsupported app: {$appName}"
			),
		};

		return new $importerClass($data, $wallet);
	}
}
