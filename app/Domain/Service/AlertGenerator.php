<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;

class AlertGenerator
{
    // TODO: refactor the array below and make categories and their budgets configurable in .env
    // Hint: store them as JSON encoded in .env variable, inject them manually in a dedicated service,
    // then inject and use use that service wherever you need category/budgets information.
    private array $categoryBudgets = [
        'Groceries' => 30000,
        'Utilities' => 50000,
        'Transport' => 20000,
        'Entertainment' => 15000,
        'Housing' => 10000,
        'Healthcare' => 5000,
        'Other' => 5000,
    ];

    public function generate(User $user, int $year, int $month): array
    {
        // TODO: implement this to generate alerts for overspending by category

        return [];
    }
}
