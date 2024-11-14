<?php

namespace App\Filament\Format;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class NumberingFormat extends NumberFormat
{

    const FORMAT_CURRENCY_IDR_INTEGER = 'Rp #,##0_-';
    const FORMAT_CURRENCY_IDR = 'Rp #,##0.00_-';
    const FORMAT_ACCOUNTING_IDR = '_("Rp "* #,##0.00_);_("Rp "* \(#,##0.00\);_("Rp "* "-"??_);_(@_)';
}
