<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;

class PdoExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    /**
     * @throws Exception
     */
    public function find(int $id): ?Expense
    {
        $query = 'SELECT * FROM expenses WHERE id = :id';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        if (false === $data) {
            return null;
        }

        return $this->createExpenseFromData($data);
    }

    public function save(Expense $expense): void
    {
        // Update
        if ($expense->id > 0 || $expense->id !== null) {
            $query = 'UPDATE expenses SET user_id = :user_id, date = :date, category = :category, amount_cents = :amount_cents, description = :description WHERE id = :id';
            $statement = $this->pdo->prepare($query);
            $statement->execute([
                'user_id' => $expense->userId,
                'date' => $expense->date->format('Y-m-d'),
                'category' => $expense->category,
                'amount_cents' => $expense->amountCents,
                'description' => $expense->description,
                'id' => $expense->id
            ]);
        } else { // Insert
            $query = 'INSERT INTO expenses (user_id, date, category, amount_cents, description) VALUES (:user_id, :date, :category, :amount_cents, :description)';
            $statement = $this->pdo->prepare($query);
            $statement->execute([
                'user_id' => $expense->userId,
                'date' => $expense->date->format('Y-m-d'),
                'category' => $expense->category,
                'amount_cents' => $expense->amountCents,
                'description' => $expense->description
            ]);
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM expenses WHERE id=?');
        $statement->execute([$id]);
    }

    public function findBy(array $criteria, int $from, int $limit): array
    {
        // TODO: Implement findBy() method.
        $query = 'SELECT * FROM expenses';
        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            if ($key === 'year') {
                $conditions[] = "substr(date, 1, 4) = ?";
            } elseif ($key === 'month') {
                $conditions[] = "substr(date, 6, 2) = ?";
            } else {
                $conditions[] = "$key = ?";
            }
            $params[] = $value;
        }

        if (count($conditions) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY date DESC LIMIT ' . $limit . ' OFFSET ' . $from;


        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        $data = $statement->fetchAll();
        return array_map([$this, 'createExpenseFromData'], $data);
    }


    public function countBy(array $criteria): int
    {
        // TODO: Implement countBy() method.

        $query = 'SELECT COUNT(*) FROM expenses';
        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        if (count($conditions) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $data = $statement->fetch();
        return $data['COUNT(*)'];
    }

    public function listExpenditureYears(User $user): array
    {
        // TODO: Implement listExpenditureYears() method.

        $query = 'SELECT YEAR(date) AS year, SUM(amount_cents) AS anual_total
        FROM expenses
        WHERE user_id = :user_id
        GROUP BY year
        ORDER BY year DESC;
        ';

        $statement = $this->pdo->prepare($query);
        $statement->execute(['user_id' => $user->id]);

        $data = $statement->fetchAll();
        return array_map(function ($row) {
            return [
                'year' => (int) $row['year'],
                'annual_total' => (float) $row['annual_total'],
            ];
        }, $data);
    }

    public function sumAmountsByCategory(array $criteria): array
    {
        $query = 'SELECT category, SUM(amount_cents) AS total_amount FROM expenses';
        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        if (count($conditions) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' GROUP BY category';

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        $data = $statement->fetchAll();

        return array_map(function ($row) {
            return [
                'category' => $row['category'],
                'total_amount' => (int) $row['total_amount'],
            ];
        }, $data);
    }


    public function averageAmountsByCategory(array $criteria): array
    {
        // TODO: Implement averageAmountsByCategory() method.
        $query = 'SELECT category, AVG(amount_cents) AS avg_amount FROM expenses';
        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        if (count($conditions) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' GROUP BY category';

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        $data = $statement->fetchAll();

        return array_map(function ($row) {
            return [
                'category' => $row['category'],
                'avg_amount' => (int) $row['total_amount'],
            ];
        }, $data);
    }

    public function sumAmounts(array $criteria): float
    {
        // TODO: Implement sumAmounts() method.
        $query = 'SELECT SUM(amount_cents) AS total_amount FROM expenses';
        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }

        if (count($conditions) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);

        $data = $statement->fetch();
        return $data['total_amount'] ?? 0;

    }

    /**
     * @throws Exception
     */
    private function createExpenseFromData(mixed $data): Expense
    {
        return new Expense(
            $data['id'],
            $data['user_id'],
            new DateTimeImmutable($data['date']),
            $data['category'],
            $data['amount_cents'],
            $data['description'],
        );
    }
}
