<?php

namespace Modules\Financial\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Financial\Constants\Permissions;

class UploadTransactionRequest extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 */
	public function rules(): array
	{
		return [
			"apps_name" => [
				"required",
				Rule::in(["firefly", "vickyserver", "e-statement"]),
			],
			"file" => [
				"required",
				"file",
				Rule::file()->types([
					"text/csv",
					"text/plain",
					"application/pdf",
					"application/vnd.ms-excel",
					"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
				]),
			],
			"password" => "nullable|string",
		];
	}

	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return auth()->check() &&
			$this->user()->can(Permissions::UPLOAD_TRANSACTIONS);
	}

	/**
	 * Get the error messages for the defined validation rules.
	 *
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			"apps_name.required" => "The application name is required",
			"apps_name.in" => "Now support only Firefly, vickyserver and e-statement",
			"file.required" => "The file is required",
			"file.file" => "The file was upload unsuccessfuly",
			"file.mimetypes" =>
				"File support available now: PDF, Excel, Spreadsheet, and CSV types",
		];
	}
}
