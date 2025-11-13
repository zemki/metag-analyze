<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrictEmail implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check for null bytes
        if (strpos($value, "\0") !== false || strpos($value, '%00') !== false) {
            return false;
        }

        // Remove quotes to check for embedded whitespace
        $unquotedEmail = str_replace(['"', "'"], '', $value);

        // Check for any whitespace characters
        if (preg_match('/\s/', $unquotedEmail)) {
            return false;
        }

        // Basic email format validation
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Extract domain part
        $parts = explode('@', $value);
        if (count($parts) !== 2) {
            return false;
        }

        $domain = $parts[1];

        // Check for TLD (domain must have at least one dot)
        if (strpos($domain, '.') === false) {
            return false;
        }

        // Check for consecutive dots
        if (strpos($value, '..') !== false) {
            return false;
        }

        // Check for double @
        if (substr_count($value, '@') > 1) {
            return false;
        }

        // Check that local part and domain are not empty
        if (empty($parts[0]) || empty($parts[1])) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid email address.';
    }
}
