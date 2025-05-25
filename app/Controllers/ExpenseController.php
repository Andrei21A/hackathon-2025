<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\ExpenseService;
use App\Infrastructure\Persistence\PdoExpenseRepository;
use App\Infrastructure\Persistence\PdoUserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ExpenseController extends BaseController
{
    private const PAGE_SIZE = 10;

    public function __construct(
        Twig $view,
        private readonly ExpenseService $expenseService,
        private readonly PdoUserRepository $userRepository,
        private readonly PdoExpenseRepository $expenseRepository
    ) {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the expenses page

        // Hints:
        // - use the session to get the current user ID
        // - use the request query parameters to determine the page number and page size
        // - use the expense service to fetch expenses for the current user

        // parse request parameters
        $queryParams = $request->getQueryParams();
        $year = isset($queryParams['year']) ? (int) $queryParams['year'] : (int) date('Y');
        $month = isset($queryParams['month']) ? (int) $queryParams['month'] : (int) date('n');
        $page = isset($queryParams['page']) ? (int) $queryParams['page'] : 1;

        $userId = $_SESSION['user_id'];
        $user = $this->userRepository->find($userId);

        $criteria = [
            'user_id' => $user->id,
            'year' => (string) $year,
            'month' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
        ];

        $limit = self::PAGE_SIZE;
        $offset = ($page - 1) * $limit;

        $expenses = $this->expenseRepository->findBy($criteria, $offset, $limit);
        $total = $this->expenseRepository->countBy($criteria);
        $hasMore = $total > $page * $limit;
        $totalPages = (int) ceil($total / self::PAGE_SIZE);


        // Get years with expenses (for filter dropdown)
        $yearsRaw = $this->expenseRepository->listExpenditureYears($user);
        $years = array_column($yearsRaw, 'year');

        return $this->render($response, 'expenses/index.twig', [
            'expenses' => $expenses,
            'total' => $total,
            'hasMorePages' => $hasMore,
            'page' => $page,
            'years' => $years,
            'year' => $year,
            'month' => $month,
            'totalPages' => $totalPages,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the create expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        $categories = ['Groceries', 'Utilities', 'Transport', 'Entertainment', 'Housing', 'Healthcare', 'Other'];


        return $this->render($response, 'expenses/create.twig', ['categories' => $categories]);
    }

    public function store(Request $request, Response $response): Response
    {
        // TODO: implement this action method to create a new expense

        // Hints:
        // - use the session to get the current user ID
        // - use the expense service to create and persist the expense entity
        // - rerender the "expenses.create" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success
        $data = $request->getParsedBody();

        $amountDollars = isset($data['amount']) ? (float) $data['amount'] : 0;
        $description = isset($data['description']) ? trim($data['description']) : '';
        $category = isset($data['category']) ? trim($data['category']) : '';
        $date = new \DateTimeImmutable($data['date']);

        $userId = $_SESSION['user_id'];
        $user = $this->userRepository->find($userId);

        $errors = [];

        //Validation
        if (!$user) {
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }
        if ($amountDollars <= 0) {
            $errors['amount'] = 'Amount must be a positive number.';
        }
        if ($description === '') {
            $errors['description'] = 'Description is required.';
        }
        if ($category === '') {
            $errors['category'] = 'Category is required.';
        }

        if (count($errors) > 0) {
            $categories = ['Groceries', 'Utilities', 'Transport', 'Entertainment', 'Housing', 'Healthcare', 'Other'];
            return $this->render($response, 'expenses/create.twig', [
                'errors' => $errors,
                'categories' => $categories,
                'old' => $data
            ]);
        }

        try {
            $this->expenseService->create($user, $amountDollars, $description, $date, $category);
            return $response->withHeader('Location', '/expenses')->withStatus(302);
        } catch (\Exception $e) {
            $errors['general'] = 'An error occurred: ' . $e->getMessage();

            return $this->render($response, 'expenses/create.twig', [
                'errors' => $errors,
                'old' => $data,
            ]);
        }

    }

    public function edit(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to display the edit expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not

        $expenseId = (int) $routeParams['id'];
        $userId = $_SESSION['user_id'];
        $expense = $this->expenseRepository->find($expenseId);

        if (!$expense) {
            return $response->withStatus(404);
        }

        if ($expense->userId !== $userId) {
            return $response->withStatus(403);
        }

        $categories = ['Groceries', 'Utilities', 'Transport', 'Entertainment', 'Housing', 'Healthcare', 'Other'];


        return $this->render($response, 'expenses/edit.twig', [
            'expense' => $expense,
            'categories' => $categories
        ]);
    }

    public function update(
        Request $request,
        Response $response,
        array $routeParams
    ): Response {
        // TODO: implement this action method to update an existing expense

        // Hints:
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - get the new values from the request and prepare for update
        // - update the expense entity with the new values
        // - rerender the "expenses.edit" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success
        $expenseId = (int) $routeParams['id'];
        $userId = $_SESSION['user_id'];
        $data = $request->getParsedBody();

        $expense = $this->expenseRepository->find($expenseId);

        if (!$expense) {
            return $response->withStatus(404);
        }

        if ($expense->userId !== $userId) {
            return $response->withStatus(403);
        }

        $amountDollars = isset($data['amount']) ? (float) $data['amount'] : 0;
        $description = isset($data['description']) ? trim($data['description']) : '';
        $category = isset($data['category']) ? trim($data['category']) : '';
        $date = new \DateTimeImmutable($data['date']);

        $errors = [];

        if ($amountDollars <= 0) {
            $errors['amount'] = 'Amount must be a positive number.';
        }
        if ($description === '') {
            $errors['description'] = 'Description is required.';
        }
        if ($category === '') {
            $errors['category'] = 'Category is required.';
        }

        if (count($errors) > 0) {
            $categories = ['Groceries', 'Utilities', 'Transport', 'Entertainment', 'Housing', 'Healthcare', 'Other'];
            return $this->render($response, 'expenses/edit.twig', [
                'expense' => $expense,
                'categories' => $categories,
                'errors' => $errors,
                'old' => $data
            ]);
        }

        try {
            $this->expenseService->update($expense, $amountDollars, $description, $date, $category);
            return $response->withHeader('Location', '/expenses')->withStatus(302);
        } catch (\Exception $e) {
            $errors['general'] = 'An error occurred: ' . $e->getMessage();
            $categories = ['Groceries', 'Utilities', 'Transport', 'Entertainment', 'Housing', 'Healthcare', 'Other'];

            return $this->render($response, 'expenses/edit.twig', [
                'expense' => $expense,
                'categories' => $categories,
                'errors' => $errors,
                'old' => $data
            ]);
        }
    }

    public function destroy(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to delete an existing expense

        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - call the repository method to delete the expense
        // - redirect to the "expenses.index" page
        $expenseId = (int) $routeParams['id'];
        $userId = $_SESSION['user_id'];

        $expense = $this->expenseRepository->find($expenseId);

        if (!$expense) {
            return $response->withStatus(404);
        }

        if ($expense->userId !== $userId) {
            return $response->withStatus(403);
        }

        try {
            $this->expenseRepository->delete($expenseId);

            $_SESSION['flash_success'] = 'Expense deleted successfully.';

            return $response->withHeader('Location', '/expenses')->withStatus(302);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to delete expense. Please try again.';

            return $response->withHeader('Location', '/expenses')->withStatus(302);
        }
    }

    public function import(Request $request, Response $response, array $args): Response
    {
        $uploadedFiles = $request->getUploadedFiles();

        if (!isset($uploadedFiles['csvFile'])) {
            $response->getBody()->write('CSV file is required');
            return $response->withStatus(400);
        }

        $csvFile = $uploadedFiles['csvFile'];

        $userId = $_SESSION['user_id'];
        $user = $this->userRepository->find($userId);

        if (!$user) {
            $response->getBody()->write('User not authenticated');
            return $response->withStatus(401);
        }

        try {
            $importedCount = $this->expenseService->importFromCsv($user, $csvFile);

            $_SESSION['flash_success'] = "Successfully imported $importedCount expenses.";

            return $response->withHeader('Location', '/expenses')->withStatus(302);

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to import expenses: ' . $e->getMessage();
            return $response->withHeader('Location', '/expenses')->withStatus(302);
        }

    }
}
