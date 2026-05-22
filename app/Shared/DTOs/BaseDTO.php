<?php

namespace App\Shared\DTOs;

/**
 * Class BaseDTO
 *
 * Abstract base class for Data Transfer Objects (DTOs).
 * Provides a convenient static factory method to create DTO instances from associative arrays.
 *
 * @package App\Shared\DTOs
 */
abstract class BaseDTO
{
    /**
     * Create a new DTO instance from an associative array.
     *
     * Uses the spread operator to pass array values as constructor arguments.
     * Assumes the array keys match the constructor parameter names in order.
     *
     * @param array $data Associative array containing DTO property values.
     * @return static A new instance of the calling DTO class.
     */
    public static function fromArray(array $data): static
    {
        return new static(...$data);
    }
}
