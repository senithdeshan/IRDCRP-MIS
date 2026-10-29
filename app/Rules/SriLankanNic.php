<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SriLankanNic implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Enter a valid Sri Lankan NIC number.');
            return;
        }

        $nic = strtoupper(trim($value));
        if (! preg_match('/^(?:\d{9}[VX]|\d{12})$/', $nic)) {
            $fail('Use a valid NIC format: 9 digits followed by V or X, or 12 digits.');
            return;
        }

        $dayOfYear = (int) substr($nic, strlen($nic) === 10 ? 2 : 4, 3);
        if (! (($dayOfYear >= 1 && $dayOfYear <= 366) || ($dayOfYear >= 501 && $dayOfYear <= 866))) {
            $fail('The date code in the NIC number is invalid.');
        }
    }
}
