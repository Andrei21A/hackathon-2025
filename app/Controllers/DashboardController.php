<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Infrastructure\Persistence\PdoExpenseRepository;
use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController extends BaseController
{
    public function __construct(
        Twig $view,
        private readonly PdoExpenseRepository $expenseRepository,
        private readonly PdoUserRepository $userRepository,
        // TODO: add necessary services here and have them injected by the DI container
    ) {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the dashboard page
        // TODO: use the session to get the current user ID
        // TODO: use the expense service/repository to fetch all required data for the dashboard
        // TODO: compute alerts or warnings to show on dashboard (e.g., over budget)

        $queryParams = $request->getQueryParams();
        $year = isset($queryParams['year']) ? (int) $queryParams['year'] : (int) date('Y');
        $month = isset($queryParams['month']) ? (int) $queryParams['month'] : (int) date('n');

        $userId = $_SESSION['user_id'];
        $user = $this->userRepository->find($userId);
        $criteria = [
            'user_id' => $user->id,
            'year' => (string) $year,
            'month' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
        ];

        $totalForMonth = $this->expenseRepository->sumAmounts($criteria);
        $totalsForCategories = $this->expenseRepository->sumAmountsByCategory($criteria);
        $averagesForCategories = $this->expenseRepository->averageAmountsByCategory($criteria);

        $yearsRaw = $this->expenseRepository->listExpenditureYears($user);
        $years = array_column($yearsRaw, 'year');
        $alerts = [];

        $categoryBudgets = [
            'Groceries' => 30000,
            'Utilities' => 50000,
            'Transport' => 20000,
            'Entertainment' => 15000,
            'Housing' => 10000,
            'Healthcare' => 5000,
            'Other' => 5000,
        ];

        foreach ($totalsForCategories as $category => $data) {
            $normalizedCategory = ucfirst(strtolower((string) $category));

            if (isset($categoryBudgets[$normalizedCategory])) {
                $budget = $categoryBudgets[$normalizedCategory];
                $spent = $data['total'];

                if ($spent > $budget) {
                    $diff = $spent - $budget;
                    $alerts[] = sprintf("⚠ %s budget exceeded by %.2f €", $normalizedCategory, $diff / 100);
                }
            }
        }



        return $this->render($response, 'dashboard.twig', [
            'alerts' => $alerts,
            'totalForMonth' => $totalForMonth,
            'totalsForCategories' => $totalsForCategories,
            'averagesForCategories' => $averagesForCategories,
            'years' => $years,
            'year' => $year,
            'month' => $month,
        ]);
    }

}
