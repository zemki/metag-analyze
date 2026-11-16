<?php

namespace App\Exports\Mart;

use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Writes text that starts with "=" as text. The default binder makes such a
 * cell a live formula, so words typed into the editor or the app could run
 * as a formula (a HYPERLINK that sends other cells away, say) when a
 * researcher opens the export. Numbers and other values go to the default
 * binder.
 */
class FormulaSafeValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value) && str_starts_with($value, '=')) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
