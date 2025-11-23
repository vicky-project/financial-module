<?php

namespace Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Financial\Enums\CashflowType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
	use LogsActivity;

	protected $with = ["transactions"];

	/**
	 * The attributes that are mass assignable.
	 */
	protected $fillable = ["user_id", "name", "icon", "type"];

	protected $casts = [
		"type" => CashflowType::class,
	];

	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes);

		$this->table =
			config("financial.table_names.categories") ?: parent::getTable();
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function transactions(): HasMany
	{
		return $this->hasMany(Transaction::class, "category_id");
	}

	public function scopeByType($query, CashflowType $type)
	{
		return $query->where("type", $type);
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->logFillable()
			->logOnlyDirty()
			->setDescriptionForEvent(fn(string $eventName) => "Category {$eventName}")
			->useLogName("categories");
	}
}
