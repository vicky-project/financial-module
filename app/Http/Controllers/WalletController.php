<?php

namespace Modules\Financial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Financial\Models\Wallet;
use Modules\Financial\Constants\Permissions;
use Modules\Financial\Services\CalculatorService;
use Modules\Financial\Http\Requests\StoreWalletRequest;

class WalletController extends Controller
{
	protected $calculatorService;

	public function __construct(CalculatorService $calculatorService)
	{
		$this->calculatorService = $calculatorService;

		$this->middleware(["permission:" . Permissions::VIEW_WALLETS])->only([
			"index",
			"show",
		]);
		$this->middleware(["permission:" . Permissions::CREATE_WALLETS])->only([
			"create",
			"store",
		]);
		$this->middleware(["permission:" . Permissions::EDIT_WALLETS])->only([
			"edit",
			"update",
		]);
		$this->middleware(["permission:" . Permissions::DELETE_WALLETS])->only([
			"destroy",
		]);
		$this->middleware(["permission:" . Permissions::MANAGE_WALLETS])->only([
			"recalculateBalance",
			"recalculateAllBalance",
		]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		$wallets = Wallet::where("user_id", auth()->id())->get();

		$walletsData = [];
		foreach ($wallets as $wallet) {
			$walletsData[] = $this->calculatorService->getWalletSummary($wallet);
		}

		return view("financial::wallets.index", compact("walletsData"));
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		$currencies = collect(config("money.currencies"))
			->keys()
			->mapWithKeys(
				fn($currency) => [
					$currency =>
						config("money.currencies")[$currency]["name"] .
						" (" .
						config("money.currencies")[$currency]["symbol"] .
						")",
				]
			)
			->toArray();
		return view("financial::wallets.create", compact("currencies"));
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(StoreWalletRequest $request)
	{
		$validated = $request->validated();

		$user = \Auth::user();

		$wallet = $user->wallets()->create($validated);
		if ($this->calculatorService->calculateWalletBalance()) {
			return back()->with("success", "Wallet created successfuly.");
		}

		return back()->withErrors(
			"There is failed when recalculating wallet balance. Please contact admin for detail."
		);
	}

	/**
	 * Show the specified resource.
	 */
	public function show(Wallet $wallet)
	{
		$wallet = $wallet->withCount("transactions")->first();

		return view("financial::wallets.show", compact("wallet"));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Wallet $wallet)
	{
		return view("financial::wallets.edit", compact("wallet"));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Wallet $wallet)
	{
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Wallet $wallet)
	{
	}

	public function recalculateBalance(Wallet $wallet)
	{
		if ($this->calculatorService->calculateWalletBalance($wallet)) {
			return back()->with("success", "Balance updated.");
		}

		return back()->withErrors(
			"Failed to calculate balances. Please contact admin for detail."
		);
	}

	public function recalculateAllBalance()
	{
		if ($this->calculatorService->calculateWalletBalance()) {
			return back()->with("success", "All wallet balance updated.");
		}

		return back()->withErrors(
			"Failed to calculate balances. Please contact admin for detail."
		);
	}
}
