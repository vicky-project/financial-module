<?php

namespace Modules\Financial\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Financial\Enums\WalletType;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Financial\Constants\Permissions;

class StoreWalletRequest extends FormRequest
{
	/**
	 * Indicates if the validator should stop on the first rule failure.
	 *
	 * @var bool
	 */
	protected $stopOnFirstFailure = true;

	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			"wallet_name" => "required|string|min:5",
			"wallet_number" => "required|unique:wallets,wallet_number|numeric|min:1",
			"wallet_type" => ["required", Rule::enum(WalletType::class)],
			"initial_balance" => "sometimes|min:0",
			"currency" => "sometimes|string",
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return auth()->check() && $this->user()->can(Permissions::CREATE_WALLETS);
	}

	/**
	 * Get the error messages for the defined validation rules.
	 *
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			"wallet_name.required" => "The wallet name is required",
			"wallet_name.min" => "The wallet name too short",
			"wallet_number.required" => "The wallet number is required",
			"wallet_number.unique" =>
				"Number has been registered. Try another number",
			"wallet_number.min" => "The wallet number to short",
			"wallet_type.required" => "The wallet type is required",
			"initial_balance.min" => "Type zero(0) to initial balance",
		];
	}
}
