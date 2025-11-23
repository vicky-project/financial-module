<?php

namespace Modules\Financial\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Financial\Constants\Permissions;

class StoreTransactionRequest extends FormRequest
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
			"category_id" => "required|exists:categories,id",
			"wallet_id" => "required|exists:wallets,id",
			"date" => "required|date",
			"description" => "required|min:5",
			"amount" => "required|min:0",
			"notes" => "sometimes|string",
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return auth()->check() &&
			$this->user()->can(Permissions::CREATE_TRANSACTIONS);
	}

	/**
	 * Get the error messages for the defined validation rules.
	 *
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			"category_id.required" => "The category is required",
			"category_id.exists" => "Category name not found",
		];
	}
}
