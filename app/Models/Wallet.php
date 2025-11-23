<?php

namespace Modules\Financial\Models;

use App\Models\User;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Modules\Financial\Enums\WalletType;
use Modules\Financial\Enums\CashflowType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
	use LogsActivity;

	/**
	 * The relationships that should always be loaded.
	 *
	 * @var array
	 */
	protected $with = ["transactions"];

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = [
		"usser_id",
		"wallet_name",
		"wallet_number",
		"wallet_type",
		"balance",
		"initial_balance",
		"currency",
	];

	protected $casts = [
		"wallet_type" => WalletType::class,
		"wallet_number" => "integer",
		"balance" => "integer",
		"initial_balance" => "integer",
	];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$this->table =
			config("financial.table_names.wallets") ?: parent::getTable();
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function transactions(): HasMany
	{
		return $this->hasMany(Transaction::class, "wallet_id");
	}

	/**
	 * Recalculate balance based on all transactions
	 */
	public function scopeRecalculateBalance(): int
	{
		//dd($this->user_id);
		$totalIncome = $this->transactions()
			->whereHas("category", function ($query) {
				$query->where("type", CashflowType::INCOME);
			})
			->whereNull("deleted_at")
			->sum("amount");

		$totalExpense = $this->transactions()
			->whereHas("category", function ($query) {
				$query->where("type", CashflowType::EXPENSE);
			})
			->whereNull("deleted_at")
			->sum("amount");

		$diffAmount = $totalIncome - $totalExpense;
		$this->balance = $this->initial_balance + $diffAmount;
		$this->save();

		return $this->balance;
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->logFillable()
			->logOnlyDirty()
			->setDescriptionForEvent(fn(string $eventName) => "Account {$eventName}")
			->useLogName("wallets");
	}
}
