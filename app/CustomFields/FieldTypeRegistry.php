<?php

namespace App\CustomFields;

use App\CustomFields\Contracts\FieldType;
use InvalidArgumentException;

class FieldTypeRegistry
{
    /** @var array<string, FieldType> */
    protected array $types = [];

    public function register(FieldType $type): void
    {
        $this->types[$type->key()] = $type;
    }

    public function get(string $key): FieldType
    {
        return $this->types[$key]
            ?? throw new InvalidArgumentException("Nepoznata vrsta polja: {$key}");
    }

    public function has(string $key): bool
    {
        return isset($this->types[$key]);
    }

    /** @return array<string, FieldType> */
    public function all(): array
    {
        return $this->types;
    }

    /** Za dropdown vrste polja u CMS-u. */
    public function options(): array
    {
        return array_map(fn (FieldType $t) => $t->label(), $this->types);
    }

    /** Vrste koje smiju biti naslovno polje objekta. */
    public function titleCapableKeys(): array
    {
        return array_keys(array_filter(
            $this->types,
            fn (FieldType $t) => $t->canBeTitle()
        ));
    }
}