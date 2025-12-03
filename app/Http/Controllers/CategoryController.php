<?php

namespace Modules\Financial\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Financial\Models\Category;
use Modules\Financial\Models\Wallet;
use Modules\Financial\Enums\CashflowType;
use Modules\Financial\Constants\Permissions;

class CategoryController extends Controller
{
	public function __construct()
	{
		$this->middleware(["permission:" . Permissions::VIEW_CATEGORIES])->only([
			"index",
			"show",
		]);
		$this->middleware(["permission:" . Permissions::CREATE_CATEGORIES])->only([
			"create",
			"store",
		]);
		$this->middleware(["permission:" . Permissions::EDIT_CATEGORIES])->only([
			"edit",
			"update",
		]);
		$this->middleware(["permission:" . Permissions::DELETE_CATEGORIES])->only([
			"destroy",
		]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		$categories = Category::where("user_id", auth()->id())
			->withCount(["transactions"])
			->paginate(10);

		$inCount = Category::where("user_id", auth()->id())
			->where("type", CashflowType::INCOME)
			->withCount(["transactions"])
			->get()
			->sum("transactions_count");
		$exCount = Category::where("user_id", auth()->id())
			->where("type", CashflowType::EXPENSE)
			->withCount(["transactions"])
			->get()
			->sum("transactions_count");

		return view(
			"financial::categories.index",
			compact("categories", "inCount", "exCount")
		);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		return view("financial::categories.create");
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
	}

	/**
	 * Show the specified resource.
	 */
	public function show(Category $category)
	{
		return view("financial::categories.show", compact("category"));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Category $category)
	{
		return view("financial::categories.edit", compact("category"));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Category $category)
	{
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Category $category)
	{
	}

	public function transactions(Request $request, Category $category)
	{
		$transactions = $category
			->transactions()
			->with(["wallet"])
			->paginate(10)
			->withQueryString();

		return view(
			"financial::categories.transactions",
			compact("category", "transactions")
		);
	}
}
