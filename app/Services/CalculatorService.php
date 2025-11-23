<?php

namespace Modules\Financial\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Financial\Models\Wallet;
use Modules\Financial\Models\Transaction;
use Modules\Financial\Enums\CashflowType;
use IcehouseVentures\LaravelChartjs\Builder;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;

class CalculatorService
{
	/**
	 * Get comprehensive wallet data in single optimized call
	 */
	public function getWalletSummary(Wallet $wallet): array
	{
		$cacheKey = "wallet_summary";

		return cache()->remember($cacheKey, 3600, function () use ($wallet) {
			// Preload semua data yang diperlukan
			$wallet->load([
				"transactions" => function ($query) {
					$query->whereNull("deleted_at")->with("category");
				},
			]);

			$monthlyIncomeExpense = $this->getMonthlyIncomeExpenseData($wallet);
			$balanceData = $this->getMonthlyBalanceData($wallet);
			$monthlyBalanceChart = $this->getChart(
				"monthlyBalanceChart",
				$balanceData["labels"],
				[
					[
						"label" => "Balance",
						"backgroundColor" => "transparent",
						"borderColor" => "rgba(54, 162, 235, 1)",
						"data" => $balanceData["balances"],
					],
				]
			);

			$incomeChart = $this->getChart(
				"monthlyIncomeChart",
				$monthlyIncomeExpense["labels"],
				[
					[
						"label" => "Balance",
						"backgroundColor" => "transparent",
						"borderColor" => "rgba(54, 162, 235, 1)",
						"data" => $monthlyIncomeExpense["income"],
					],
				]
			);
			$expenseChart = $this->getChart(
				"monthlyExpenseChart",
				$monthlyIncomeExpense["labels"],
				[
					[
						"label" => "Balance",
						"backgroundColor" => "transparent",
						"borderColor" => "rgba(54, 162, 235, 1)",
						"data" => $monthlyIncomeExpense["expense"],
					],
				]
			);

			return [
				"wallet" => [
					"id" => $wallet->id,
					"name" => $wallet->wallet_name,
					"type" => $wallet->wallet_type,
					"currency" => $wallet->currency,
					"current_balance" => $wallet->balance,
				],
				"balance_percentage" => $this->getBalancePercentageChange($wallet),
				"yearly_totals" => $this->getYearlyTransactionTotals($wallet),
				"chart" => [
					"monthly_balance" => $monthlyBalanceChart,
					"income" => $incomeChart,
					"expense" => $expenseChart,
				],
				"transaction_count" => $wallet->transactions->count(),
				"updated_at" => now()->toISOString(),
			];
		});
	}

	/**
	 * Calculate wallet balance in single wallet or all of wallet of user
	 */
	public function calculateWalletBalance(?Wallet $wallet = null): bool
	{
		if ($wallet) {
			return $this->calculateSingleWalletBalance($wallet);
		}

		return $this->calculateUserWalletBalance(auth()->id());
	}

	private function calculateSingleWalletBalance(Wallet $wallet): bool
	{
		$initialBalance = $wallet->initial_balance ?? 0;

		// Single query untuk mendapatkan total income dan expense
		$totals = $this->getWalletTransactionTotals($wallet);

		$currentBalance = $initialBalance + $totals["income"] - $totals["expense"];

		if ($wallet->balance !== $currentBalance) {
			$wallet->update(["balance" => $currentBalance]);
		}

		return true;
	}

	private function calculateUserWalletBalance(int $userId): bool
	{
		$wallets = Wallet::where("user_id", $userId)->get();

		if ($wallets->isEmpty()) {
			return false;
		}

		// Preload semua transactions untuk semua wallet sekaligus
		$wallets->load([
			"transactions" => function ($query) {
				$query->whereNull("deleted_at")->with("category");
			},
		]);

		foreach ($wallets as $wallet) {
			$this->calculateSingleWalletBalance($wallet);
		}

		return true;
	}

	/**
	 * Get wallet transaction totals in single query
	 */
	private function getWalletTransactionTotals(Wallet $wallet): array
	{
		$income =
			$wallet
				->transactions()
				->whereHas(
					"category",
					fn($q) => $q->where("type", CashflowType::INCOME)
				)
				->whereNull("deleted_at")
				->sum("amount") ?? 0;

		$expense =
			$wallet
				->transactions()
				->whereHas(
					"category",
					fn($q) => $q->where("type", CashflowType::EXPENSE)
				)
				->whereNull("deleted_at")
				->sum("amount") ?? 0;

		return [
			"income" => $income,
			"expense" => $expense,
			"net" => $income - $expense,
		];
	}

	public function getBalancePercentageChange(Wallet $wallet): array
	{
		$currentBalance = $wallet->balance;

		// Get all balance data sekaligus untuk perbandingan
		$balanceData = $this->getBalanceComparisonData($wallet);

		$absoluteChange = $currentBalance - $balanceData["last_month_balance"];
		$percentageChange =
			$balanceData["last_month_balance"] != 0
				? ($absoluteChange / abs($balanceData["last_month_balance"])) * 100
				: ($currentBalance > 0
					? 100
					: 0);

		return [
			"current_balance" => $currentBalance,
			"previous_balance" => $balanceData["last_month_balance"],
			"absolute_change" => $absoluteChange,
			"percentage_change" => round($percentageChange, 2),
			"is_increase" => $absoluteChange > 0,
			"is_decrease" => $absoluteChange < 0,
			"current_month" => $balanceData["current_month"],
			"previous_month" => $balanceData["previous_month"],
		];
	}

	/**
	 * Get balance comparison data in optimized way
	 */
	private function getBalanceComparisonData(Wallet $wallet): array
	{
		$currentMonth = now();
		$lastMonth = now()->subMonth();

		// Get initial balance
		$initialBalance = $wallet->initial_balance ?? 0;

		// Get all transactions up to current date
		$allTransactions = $wallet
			->transactions()
			->whereNull("deleted_at")
			->with("category")
			->get();

		// Calculate balances for different dates
		$lastMonthBalance = $initialBalance;
		$currentBalance = $initialBalance;

		foreach ($allTransactions as $transaction) {
			$transactionDate = $transaction->date;

			// Calculate current balance
			$currentBalance += $this->getTransactionAmount($transaction);

			// Calculate last month balance (only include transactions up to last month)
			if ($transactionDate <= $lastMonth->endOfMonth()) {
				$lastMonthBalance += $this->getTransactionAmount($transaction);
			}
		}

		return [
			"last_month_balance" => $lastMonthBalance,
			"current_balance" => $currentBalance,
			"current_month" => $currentMonth->format("F Y"),
			"previous_month" => $lastMonth->format("F Y"),
		];
	}

	/**
	 * Get transaction amount with proper sign based on category type
	 */
	private function getTransactionAmount(Transaction $transaction): int
	{
		return $transaction->category->type === CashflowType::INCOME
			? $transaction->amount
			: -$transaction->amount;
	}

	/**
	 * Get yearly transaction totals dengan perbandingan tahun sebelumnya
	 */
	public function getYearlyTransactionTotals(Wallet $wallet): array
	{
		$currentYear = now()->year;
		$previousYear = $currentYear - 1;

		// Get transaction data for both years in single query
		$yearlyData = $this->getMultipleYearsTransactionData($wallet, [
			$currentYear,
			$previousYear,
		]);

		$currentYearData = $yearlyData[$currentYear] ?? [
			"income" => 0,
			"expense" => 0,
		];
		$previousYearData = $yearlyData[$previousYear] ?? [
			"income" => 0,
			"expense" => 0,
		];

		// Calculate changes
		$incomeChange = $this->calculatePercentageChange(
			$previousYearData["income"],
			$currentYearData["income"]
		);

		$expenseChange = $this->calculatePercentageChange(
			$previousYearData["expense"],
			$currentYearData["expense"]
		);

		return [
			"income" => [
				"current" => $currentYearData["income"],
				"previous" => $previousYearData["income"],
				"percentage" => $incomeChange,
				"is_increase" => $incomeChange > 0,
				"absolute_change" =>
					$currentYearData["income"] - $previousYearData["income"],
			],
			"expense" => [
				"current" => $currentYearData["expense"],
				"previous" => $previousYearData["expense"],
				"percentage" => $expenseChange,
				"is_increase" => $expenseChange > 0,
				"absolute_change" =>
					$currentYearData["expense"] - $previousYearData["expense"],
			],
			"years" => [
				"current" => $currentYear,
				"previous" => $previousYear,
			],
		];
	}

	/**
	 * Get transaction data for multiple years in single query
	 */
	private function getMultipleYearsTransactionData(
		Wallet $wallet,
		array $years
	): array {
		$result = [];

		// Get all transactions for the specified years
		$transactions = $wallet
			->transactions()
			->whereNull("deleted_at")
			->with("category")
			->whereIn(\DB::raw("YEAR(date)"), $years)
			->get();

		// Initialize result array
		foreach ($years as $year) {
			$result[$year] = ["income" => 0, "expense" => 0];
		}

		// Group and sum transactions by year and type
		foreach ($transactions as $transaction) {
			$year = $transaction->date->year;

			if (!in_array($year, $years)) {
				continue;
			}

			if ($transaction->category->type === CashflowType::INCOME) {
				$result[$year]["income"] += $transaction->amount;
			} else {
				$result[$year]["expense"] += $transaction->amount;
			}
		}

		return $result;
	}

	/**
	 * Helper method untuk calculate percentage change
	 */
	private function calculatePercentageChange(
		float $oldValue,
		float $newValue
	): float {
		if ($oldValue == 0) {
			return $newValue != 0 ? 100 : 0;
		}

		return (($newValue - $oldValue) / abs($oldValue)) * 100;
	}

	public function getChart(
		string $name,
		array $labels,
		array $datasets
	): Builder {
		return Chartjs::build()
			->name($name)
			->type("line")
			->labels($labels)
			->datasets($datasets)
			->options([
				"plugins" => ["legend" => ["display" => false]],
				"maintainAspectRatio" => false,
				"scales" => [
					"x" => [
						"border" => ["display" => false],
						"grid" => ["display" => false, "drawBorder" => false],
						"ticks" => ["display" => false],
					],
					"y" => [
						"min" => 0,
						"beginAtZero" => true,
						"display" => false,
						"grid" => ["display" => false],
						"ticks" => ["display" => false],
					],
				],
				"elements" => [
					"line" => ["borderWidth" => 1, "tension" => 0.4],
					"point" => ["radius" => 4, "hitRadius" => 10, "hoverRadius" => 4],
				],
			]);
	}

	/**
	 * Get monthly balance data with optimized single query
	 */
	private function getMonthlyBalanceData(Wallet $wallet): array
	{
		$endDate = now();
		$startDate = now()
			->subDays(29)
			->startOfDay();

		// Get all transactions for the period
		$transactions = $wallet
			->transactions()
			->whereBetween("date", [$startDate, $endDate])
			->whereNull("deleted_at")
			->with("category")
			->orderBy("date")
			->get();

		// Get initial balance before the period
		$initialBalance = $this->getBalanceAtDate(
			$wallet,
			$startDate->copy()->subDay()
		);

		// Group transactions by date
		$dailyTransactions = [];
		foreach ($transactions as $transaction) {
			$dateKey = $transaction->date->format("Y-m-d");
			if (!isset($dailyTransactions[$dateKey])) {
				$dailyTransactions[$dateKey] = ["income" => 0, "expense" => 0];
			}

			if ($transaction->category->type === CashflowType::INCOME) {
				$dailyTransactions[$dateKey]["income"] += $transaction->amount;
			} else {
				$dailyTransactions[$dateKey]["expense"] += $transaction->amount;
			}
		}

		// Calculate daily balances
		$balances = [];
		$labels = [];
		$currentBalance = $initialBalance;

		$currentDate = $startDate->copy();
		while ($currentDate <= $endDate) {
			$dateKey = $currentDate->format("Y-m-d");

			// Apply daily transactions if any
			if (isset($dailyTransactions[$dateKey])) {
				$currentBalance += $dailyTransactions[$dateKey]["income"];
				$currentBalance -= $dailyTransactions[$dateKey]["expense"];
			}

			$balances[] = $currentBalance;
			$labels[] = $currentDate->format("M j");

			$currentDate->addDay();
		}

		return [
			"labels" => $labels,
			"balances" => $balances,
			"period" => [
				"start" => $startDate->format("Y-m-d"),
				"end" => $endDate->format("Y-m-d"),
				"days" => 30,
			],
		];
	}

	/**
	 * Get balance at specific date (optimized version)
	 */
	private function getBalanceAtDate(Wallet $wallet, Carbon $date): int
	{
		$initialBalance = $wallet->initial_balance ?? 0;

		$incomeToDate =
			$wallet
				->transactions()
				->whereHas(
					"category",
					fn($q) => $q->where("type", CashflowType::INCOME)
				)
				->where("date", "<=", $date)
				->whereNull("deleted_at")
				->sum("amount") ?? 0;

		$expenseToDate =
			$wallet
				->transactions()
				->whereHas(
					"category",
					fn($q) => $q->where("type", CashflowType::EXPENSE)
				)
				->where("date", "<=", $date)
				->whereNull("deleted_at")
				->sum("amount") ?? 0;

		return $initialBalance + $incomeToDate - $expenseToDate;
	}

	/**
	 * Get monthly income and expense data for the last 12 months
	 */
	public function getMonthlyIncomeExpenseData(Wallet $wallet): array
	{
		$endDate = now();
		$startDate = now()
			->subMonths(11)
			->startOfMonth();

		// Get all transactions for the last 12 months in single query
		$transactions = $wallet
			->transactions()
			->whereBetween("date", [$startDate, $endDate])
			->whereNull("deleted_at")
			->with("category")
			->get();

		// Initialize monthly data
		$monthlyData = [];
		$currentDate = $startDate->copy();

		while ($currentDate <= $endDate) {
			$monthKey = $currentDate->format("Y-m");
			$monthlyData[$monthKey] = [
				"income" => 0,
				"expense" => 0,
				"label" => $currentDate->format("M Y"),
			];
			$currentDate->addMonth();
		}

		// Group transactions by month and type
		foreach ($transactions as $transaction) {
			$monthKey = $transaction->date->format("Y-m");

			if (!isset($monthlyData[$monthKey])) {
				continue;
			}

			if ($transaction->category->type === CashflowType::INCOME) {
				$monthlyData[$monthKey]["income"] += $transaction->amount;
			} else {
				$monthlyData[$monthKey]["expense"] += $transaction->amount;
			}
		}

		// Prepare data for chart
		$labels = [];
		$incomeData = [];
		$expenseData = [];
		$netData = [];

		foreach ($monthlyData as $month) {
			$labels[] = $month["label"];
			$incomeData[] = $month["income"];
			$expenseData[] = $month["expense"];
			$netData[] = $month["income"] - $month["expense"];
		}

		return [
			"labels" => $labels,
			"income" => $incomeData,
			"expense" => $expenseData,
			"net" => $netData,
		];
	}
}
