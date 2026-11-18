<?php

namespace App\Providers;

use App\CustomFields\FieldTypeRegistry;
use App\CustomFields\Types\TextType;
use Illuminate\Support\ServiceProvider;

class CustomFieldsServiceProvider extends ServiceProvider
{
    /** Vrste polja dostupne u aplikaciji. Novi tip se dodaje ovdje. */
    protected array $fieldTypes = [
        TextType::class,
        // TextareaType::class,
        // NumberType::class,
        // BooleanType::class,
        // DateType::class,
        // DateTimeType::class,
        // SelectType::class,
        // MultiselectType::class,
        // EmailType::class,
        // UrlType::class,
    ];

    public function register(): void
    {
        $this->app->singleton(FieldTypeRegistry::class, function () {
            $registry = new FieldTypeRegistry();

            foreach ($this->fieldTypes as $class) {
                $registry->register(new $class());
            }

            return $registry;
        });
    }
}