<?php

namespace Modules\Financial\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Financial\Models\{Category, Transaction, Wallet};
use Modules\Financial\Constants\Permissions;
use Modules\Financial\Services\{CalculatorService, ImportServiceFactory};
use Modules\Financial\Http\Requests\{
	StoreTransactionRequest,
	UpdateTransactionRequest,
	UploadTransactionRequest
};

class TransactionController extends Controller
{
	protected $calculatorService;

	public function __construct(CalculatorService $calculatorService)
	{
		$this->calculatorService = $calculatorService;

		$this->middleware(["permission:" . Permissions::VIEW_TRANSACTIONS])->only([
			"index",
			"show",
			"detail",
		]);
		$this->middleware(["permission:" . Permissions::CREATE_TRANSACTIONS])->only(
			["create", "store"]
		);
		$this->middleware(["permission:" . Permissions::EDIT_TRANSACTIONS])->only([
			"edit",
			"update",
		]);
		$this->middleware(["permission:" . Permissions::DELETE_TRANSACTIONS])->only(
			["destroy"]
		);
		$this->middleware([
			"permission:" . Permissions::RESTORE_TRANSACTIONS,
		])->only(["restore"]);
		$this->middleware(["permission:" . Permissions::UPLOAD_TRANSACTIONS])->only(
			["upload"]
		);
		$this->middleware([
			"permission:" . Permissions::RESTORE_TRANSACTIONS,
		])->only(["restore"]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request, Wallet $wallet)
	{
		$year = $request->has("year")
			? now()
				->create($request->input("year") ?? 0)
				->format("Y")
			: null;

		$walletSummary = $this->calculatorService->getWalletSummary($wallet);
		$transactions = $wallet
			->transactions()
			->with(["category"])
			->when($year, fn($query, $year) => $query->whereYear("date", $year))
			->latest("date")
			->get()
			->groupBy(fn(Transaction $transaction) => $transaction->date->format("Y"))
			->mapWithKeys(
				fn($items, $key) => [
					$key => $items
						->groupBy(
							fn(Transaction $transaction) => $transaction->date->format("F")
						)
						->sortBy("date"),
				]
			)
			->all();

		$widgets = [
			"balance" => $walletSummary["balance_percentage"],
			"income" => $walletSummary["yearly_totals"]["income"],
			"expense" => $walletSummary["yearly_totals"]["expense"],
			"chart" => $walletSummary["chart"],
			"transaction_count" => $walletSummary["transaction_count"],
		];

		return view(
			"financial::transactions.index",
			compact("transactions", "widgets", "wallet", "year")
		);
	}

	public function detail(Request $request, Wallet $wallet)
	{
		$year = $request->input("year") ?? now()->year;
		$month = $request->input("month") ?? now()->month;

		$balanceChange = $this->calculatorService->getBalancePercentageChange(
			$wallet
		);
		$transactions = $wallet
			->transactions()
			->with(["category"])
			->whereYear(
				"date",
				now()
					->parse($year)
					->format("Y")
			)
			->whereMonth(
				"date",
				is_string($month)
					? now()
						->create($month)
						->format("m")
					: now($month)->format("m")
			)
			->orderByDesc("date")
			->paginate(10)
			->withQueryString();

		$chartBalance = $this->calculatorService->getChart(
			"balanceWidget",
			$transactions
				->pluck("date")
				->map(fn($date) => $date->format("d-m-Y H:i:s"))
				->toArray(),
			[
				[
					"label" => "Balance",
					"backgroundColor" => "transparent",
					"borderColor" => "rgba(54, 162, 235, 1)",
					"data" => $transactions->pluck("amount")->toArray(),
				],
			]
		);

		return view(
			"financial::transactions.detail",
			compact(
				"wallet",
				"year",
				"month",
				"transactions",
				"balanceChange",
				"chartBalance"
			)
		);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(Request $request, Wallet $wallet)
	{
		$year = $request->input("year") ?? null;
		$month = $request->input("month") ?? null;
		$userId = \Auth::id();
		$wallets = Wallet::where("user_id", $userId)->get();
		$categories = Category::where("user_id", $userId)->get();

		return view(
			"financial::transactions.create",
			compact("wallet", "wallets", "categories", "year", "month")
		);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(StoreTransactionRequest $request, Wallet $wallet)
	{
		dd($request);
	}

	/**
	 * Show the specified resource.
	 */
	public function show(
		Request $request,
		Wallet $wallet,
		Transaction $transaction
	) {
		$year = $request->input("year") ?? null;
		$month = $request->input("month") ?? null;

		return view(
			"financial::transactions.show",
			compact("wallet", "transaction", "year", "month")
		);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(
		Request $request,
		Wallet $wallet,
		Transaction $transaction
	) {
		$year = $request->input("year") ?? now()->year;
		$month = $request->input("month") ?? now()->month;

		$categories = Category::where("user_id", auth()->id())->get();

		return view(
			"financial::transactions.edit",
			compact("wallet", "transaction", "categories", "year", "month")
		);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(
		UpdateTransactionRequest $request,
		Wallet $wallet,
		Transaction $transaction
	) {
		dd($request);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(
		Request $request,
		Wallet $wallet,
		Transaction $transaction
	) {
		dd($wallet, $transaction);
	}

	public function massDestroy(Request $request, Wallet $wallet)
	{
		$month = $request->input("month") ?? null;
		$year = $request->input("year") ?? null;

		$transactions = $wallet
			->transactions()
			->when($year, fn($query) => $query->whereYear("date", $year))
			->when(
				$month,
				fn($query) => $query->whereMonth(
					"date",
					is_string($month)
						? now()
							->create($month)
							->format("m")
						: now($month)->month
				)
			)
			->orderByDesc("date")
			->get();
		Transaction::destroy($transactions->pluck("id"));

		return back()->with("success", "Berhasil menghapus data transaksi.");
	}

	public function trash(Request $request, Wallet $wallet)
	{
		$year = $request->input("year") ?? null;
		$month = $request->input("month") ?? null;

		$trashs = $wallet
			->transactions()
			->with(["category"])
			->onlyTrashed()
			->whereNotNull("deleted_at")
			->when(
				$year,
				fn($query, $year) => $query->whereYear(
					"date",
					is_string($year)
						? now()
							->create($year)
							->format("Y")
						: now($year)->year
				)
			)
			->when(
				$month,
				fn($query, $month) => $query->whereMonth(
					"date",
					is_string($month)
						? now()
							->create($month)
							->parse("m")
						: now($month)->month
				)
			)
			->latest("date")
			->paginate(10)
			->withQueryString();

		return view(
			"financial::transactions.trash",
			compact("wallet", "trashs", "year", "month")
		);
	}

	public function restore(Wallet $wallet, Transaction $transaction)
	{
		$year = $request->input("year") ?? null;
		$month = $request->input("month") ?? null;

		$transaction->restore();

		return back()->with("success", "Transaction was restored");
	}

	public function restoreAll(Request $request, Wallet $wallet)
	{
		$year = $request->input("year") ?? null;
		$month = $request->input("month") ?? null;

		$wallet
			->transactions()
			->when(
				$year,
				fn($query, $year) => $query->whereYear(
					"date",
					is_string($year)
						? now()
							->create($year)
							->format("Y")
						: now($year)->year
				)
			)
			->when(
				$month,
				fn($query, $month) => $query->whereMonth(
					"date",
					is_string($month)
						? now()
							->create($month)
							->format("m")
						: now($year)->month
				)
			)
			->each(fn($t) => $t->history()->restore());
	}

	public function forceDeleteAll(Request $request, Wallet $wallet)
	{
		$year = $request->input("year") ?? null;
		$month = $request->input("month") ?? null;

		$wallet
			->transactions()
			->when(
				$year,
				fn($query, $year) => $query->whereYear(
					"date",
					is_string($year)
						? now()
							->create($year)
							->format("Y")
						: now($year)->year
				)
			)
			->when(
				$month,
				fn($query, $month) => $query->whereMonth(
					"date",
					is_string($month)
						? now()
							->create($month)
							->format("m")
						: now($year)->month
				)
			)
			->each(fn($t) => $t->history()->forceDelete());

		return back()->with("success", "Trash deleted successful");
	}

	public function forceDelete(
		Request $request,
		Wallet $wallet,
		Transaction $transaction
	) {
		$transaction->forceDelete();

		return back()->with("success", "Trash deleted successful");
	}

	public function upload(UploadTransactionRequest $request, Wallet $wallet)
	{
		$validated = $request->validated();
		$appsName = $validated["apps_name"];
		$file = $validated["file"];
		$password = $validated["password"] ?? null;

		$filestore = $file->store("upload", "public");
		$filepath = Storage::disk("public")->path($filestore);
		$fileType = $file->getClientOriginalExtension();

		try {
			$factory = ImportServiceFactory::createReader(
				$fileType,
				$filepath,
				$password
			);
			$data = $factory->read();

			$importer = ImportServiceFactory::createImporter(
				$appsName,
				$data,
				$wallet
			);
			$result = $importer->load();
			Transaction::insert($result);
			return back()->with("success", "Data was imported successful.");
		} catch (\Exception $e) {
			logger()->error($e->getMessage(), [
				"message" => $e->getMessage(),
				"trace" => $e->getTrace(),
			]);
			return back()->withErrors(["password" => $e->getMessage()]);
		}
	}
}
