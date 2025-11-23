<?php

namespace Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Financial\Enums\CashflowType;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
	use SoftDeletes, LogsActivity;

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = [
		"category_id",
		"wallet_id",
		"date",
		"description",
		"amount",
		"notes",
	];

	protected $casts = [
		"date" => "datetime",
		"amount" => "integer",
	];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$this->table =
			config("financial.table_names.transactions") ?: parent::getTable();
	}

	public function category(): BelongsTo
	{
		return $this->belongsTo(Category::class);
	}

	public function wallet(): BelongsTo
	{
		return $this->belongsTo(Wallet::class);
	}

	public function getTypeAttribute(): CashflowType
	{
		return $this->category->type;
	}

	public function scopeTotalIncome($query, $walletId = null)
	{
		$query = $query
			->whereHas("category", function ($q) {
				$q->where("type", CashflowType::INCOME);
			})
			->sum("amount");
		if ($walletId) {
			$query = $query->where("wallet_id", $walletId);
		}

		return $query;
	}

	public function scopeTotalExpense($query, $walletId = null)
	{
		$query = $query
			->whereHas("category", function ($q) {
				$q->where("type", CashflowType::EXPENSE);
			})
			->sum("amount");
		if ($walletId) {
			$query = $query->where("wallet_id", $walletId);
		}

		return $query;
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->logFillable()
			->logOnlyDirty()
			->setDescriptionForEvent(
				fn(string $eventName) => "Transaction {$eventName}"
			)
			->useLogName("transactions");
	}
}
