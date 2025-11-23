<?php

namespace Modules\Financial\Traits;

use Modules\Financial\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasWallets
{
	public function wallets(): HasMany
	{
		return $this->hasMany(Wallet::class, "user_id");
	}
}
