<?php

namespace Modules\Financial\Enums;

enum WalletType: string
{
	case SAVINGS = "savings";
	case CHECKING = "checking";
	case INVESTMENT = "investment";
	case DIGITAL = "digital";
}
