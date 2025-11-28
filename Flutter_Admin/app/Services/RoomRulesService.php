<?php

namespace App\Services;

class RoomRulesService
{
    public function validateBeds(string $type, int $beds): void
    {
        $rules = [
            'single'    => [1, 1],
            'double'    => [1, 2],
            'quad'      => [2, 2],
            'family'    => [2, 3],
            'suite'     => [1, 2],
            'penthouse' => [2, 4],
        ];

        if (!isset($rules[$type])) {
            throw new \Exception("Unknown room type '{$type}'.");
        }

        [$min, $max] = $rules[$type];

        if ($beds < $min || $beds > $max) {
            throw new \Exception("{$type} rooms must have between {$min} and {$max} beds.");
        }
    }

    public function validateCapacity(string $type, int $capacity): void
    {
        $rules = [
            'single'    => [1, 1],
            'double'    => [2, 2],
            'quad'      => [4, 4],
            'family'    => [4, 6],
            'suite'     => [2, 4],
            'penthouse' => [4, 8],
        ];

        if (!isset($rules[$type])) {
            throw new \Exception("Unknown room type '{$type}'.");
        }

        [$min, $max] = $rules[$type];

        if ($capacity < $min || $capacity > $max) {
            throw new \Exception("{$type} room capacity must be {$min}–{$max}.");
        }
    }
}
