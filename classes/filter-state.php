<?php

declare(strict_types=1);

namespace repliq;

class RepliqFilterState
{
    public function old(string $id): string|array
    {
        $value = get($id);

        if ($value === null) {
            return '';
        }

        return $value;
    }

    /**
     * @return array<int, string>
     */
    public function error(string $id): array
    {
        return [];
    }

    public function success(): bool
    {
        return false;
    }
}
