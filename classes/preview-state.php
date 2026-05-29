<?php

declare(strict_types=1);

namespace repliq;

/**
 * État de formulaire factice pour le styleguide.
 *
 * Implémente la même interface que les objets `form` attendus par les snippets
 * de champs (old/error/success), mais renvoie des valeurs et erreurs prédéfinies
 * indexées par id de champ. Permet de rendre chaque champ dans n'importe quel état
 * (filled, error…) en réutilisant les vrais snippets, sans soumission réelle.
 */
class RepliqPreviewState
{
    /**
     * @param array<string, string|array<int, string>> $values Valeur old() par id de champ
     * @param array<string, array<int, string>> $errors Messages d'erreur par id de champ
     */
    public function __construct(
        private array $values = [],
        private array $errors = [],
    ) {
    }

    /**
     * @return string|array<int, string>
     */
    public function old(string $id): string|array
    {
        return $this->values[$id] ?? '';
    }

    /**
     * @return array<int, string>
     */
    public function error(string $id): array
    {
        return $this->errors[$id] ?? [];
    }

    public function success(): bool
    {
        return false;
    }
}
