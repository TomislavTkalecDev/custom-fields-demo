<?php

namespace App\CustomFields\Contracts;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Schemas\Components\Component;

interface FieldType
{
    /** Vrijednost koja se sprema u custom_fields.type. */
    public function key(): string;

    /** Naziv u izborniku vrsta polja u CMS-u. */
    public function label(): string;

    /** Heroicon za karticu pri odabiru vrste. */
    public function icon(): string;

    /** Stupac u custom_field_values. Null ako vrijednost ide u zasebnu tablicu. */
    public function storageColumn(): ?string;

    /** Filament komponente za postavke u CMS-u. Završavaju u custom_fields.settings. */
    public function settingsSchema(): array;

    /** Polje u formi za unos zapisa u CRM-u. */
    public function formComponent(CustomField $field): Component;

    /** Stupac u tablici zapisa. */
    public function tableColumn(CustomField $field): Column;

    /** Filter nad tim poljem, ili null ako ga vrsta nema. */
    public function tableFilter(CustomField $field): ?BaseFilter;

    /** Laravel validacijska pravila. */
    public function validationRules(CustomField $field): array;

    /** Vrijednost iz forme → vrijednost za stupac. */
    public function toStorage(mixed $value, CustomField $field): mixed;

    /** Red iz baze → vrijednost za formu. */
    public function fromStorage(?CustomFieldValue $value, CustomField $field): mixed;

    /** Smije li se po ovom polju sortirati. */
    public function isSortable(): bool;

    /** Smije li ova vrsta biti naslovno polje objekta. */
    public function canBeTitle(): bool;
}