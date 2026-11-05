<?php
/**
 * Sport Shop – Input Validator
 *
 * Validates an associative array of data against a set of rules.
 * Returns Vietnamese error messages.
 *
 * Supported rules:
 *   required, string, integer, numeric, email,
 *   min:X, max:X (string length), min_value:X, max_value:X (numeric value),
 *   in:a,b,c, confirmed, url, alpha, alpha_num, date, max_file:X (KB), image
 */

namespace App\Core;

class Validator
{
    /** Human-readable field names (Vietnamese). */
    private static array $fieldNames = [];

    /**
     * Validate data against rules.
     *
     * @param array $data   Associative input data.
     * @param array $rules  ['field' => 'required|string|max:255', ...]
     * @param array $names  Optional Vietnamese field labels ['field' => 'Tên sản phẩm']
     * @return array{valid: bool, errors: array<string, string>}
     */
    public static function validate(array $data, array $rules, array $names = []): array
    {
        self::$fieldNames = $names;
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value    = $data[$field] ?? null;
            $label    = self::label($field);

            foreach ($ruleList as $rule) {
                $param = null;

                // Parse rule:parameter
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                $error = self::applyRule($rule, $field, $value, $param, $label, $data);

                if ($error !== null) {
                    // Only keep the first error per field
                    if (!isset($errors[$field])) {
                        $errors[$field] = $error;
                    }
                    // Stop validating this field on first failure (fail-fast per field)
                    break;
                }
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Quick boolean check.
     */
    public static function isValid(array $data, array $rules, array $names = []): bool
    {
        return self::validate($data, $rules, $names)['valid'];
    }

    // -------------------------------------------------------------------
    // Rule handlers
    // -------------------------------------------------------------------

    private static function applyRule(string $rule, string $field, mixed $value, ?string $param, string $label, array $data): ?string
    {
        return match ($rule) {
            'required'   => self::ruleRequired($value, $label),
            'string'     => self::ruleString($value, $label),
            'integer'    => self::ruleInteger($value, $label),
            'numeric'    => self::ruleNumeric($value, $label),
            'email'      => self::ruleEmail($value, $label),
            'min'        => self::ruleMin($value, (int)$param, $label),
            'max'        => self::ruleMax($value, (int)$param, $label),
            'min_value'  => self::ruleMinValue($value, $param, $label),
            'max_value'  => self::ruleMaxValue($value, $param, $label),
            'in'         => self::ruleIn($value, $param, $label),
            'confirmed'  => self::ruleConfirmed($value, $field, $data, $label),
            'url'        => self::ruleUrl($value, $label),
            'alpha'      => self::ruleAlpha($value, $label),
            'alpha_num'  => self::ruleAlphaNum($value, $label),
            'date'       => self::ruleDate($value, $label),
            'image'      => self::ruleImage($value, $label),
            'max_file'   => self::ruleMaxFile($value, (int)$param, $label),
            default      => null, // Unknown rules are ignored
        };
    }

    private static function ruleRequired(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return "{$label} không được để trống.";
        }
        // Handle uploaded files
        if (is_array($value) && isset($value['error']) && $value['error'] === UPLOAD_ERR_NO_FILE) {
            return "{$label} không được để trống.";
        }
        return null;
    }

    private static function ruleString(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null; // Skip if empty (use 'required' to enforce)
        }
        if (!is_string($value)) {
            return "{$label} phải là chuỗi ký tự.";
        }
        return null;
    }

    private static function ruleInteger(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return "{$label} phải là số nguyên.";
        }
        return null;
    }

    private static function ruleNumeric(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_numeric($value)) {
            return "{$label} phải là số.";
        }
        return null;
    }

    private static function ruleEmail(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return "{$label} không phải là địa chỉ email hợp lệ.";
        }
        return null;
    }

    private static function ruleMin(mixed $value, int $min, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value) && mb_strlen($value, 'UTF-8') < $min) {
            return "{$label} phải có ít nhất {$min} ký tự.";
        }
        return null;
    }

    private static function ruleMax(mixed $value, int $max, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value) && mb_strlen($value, 'UTF-8') > $max) {
            return "{$label} không được vượt quá {$max} ký tự.";
        }
        return null;
    }

    private static function ruleMinValue(mixed $value, ?string $min, string $label): ?string
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }
        if ((float)$value < (float)$min) {
            return "{$label} phải có giá trị tối thiểu là {$min}.";
        }
        return null;
    }

    private static function ruleMaxValue(mixed $value, ?string $max, string $label): ?string
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }
        if ((float)$value > (float)$max) {
            return "{$label} phải có giá trị tối đa là {$max}.";
        }
        return null;
    }

    private static function ruleIn(mixed $value, ?string $list, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $allowed = explode(',', $list ?? '');
        if (!in_array((string)$value, $allowed, true)) {
            $options = implode(', ', $allowed);
            return "{$label} phải là một trong các giá trị: {$options}.";
        }
        return null;
    }

    private static function ruleConfirmed(mixed $value, string $field, array $data, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $confirmField = $field . '_confirmation';
        if (!isset($data[$confirmField]) || $value !== $data[$confirmField]) {
            return "{$label} xác nhận không khớp.";
        }
        return null;
    }

    private static function ruleUrl(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return "{$label} không phải là URL hợp lệ.";
        }
        return null;
    }

    private static function ruleAlpha(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!preg_match('/^[\pL\s]+$/u', (string)$value)) {
            return "{$label} chỉ được chứa chữ cái.";
        }
        return null;
    }

    private static function ruleAlphaNum(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!preg_match('/^[\pL\pN\s]+$/u', (string)$value)) {
            return "{$label} chỉ được chứa chữ cái và số.";
        }
        return null;
    }

    private static function ruleDate(mixed $value, string $label): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (strtotime((string)$value) === false) {
            return "{$label} không phải là ngày hợp lệ.";
        }
        return null;
    }

    private static function ruleImage(mixed $value, string $label): ?string
    {
        if (!is_array($value) || !isset($value['tmp_name']) || $value['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($value['tmp_name']);
        if (!in_array($mime, $allowed, true)) {
            return "{$label} phải là hình ảnh (JPEG, PNG, GIF, WEBP).";
        }
        return null;
    }

    private static function ruleMaxFile(mixed $value, int $maxKB, string $label): ?string
    {
        if (!is_array($value) || !isset($value['size']) || $value['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($value['size'] > $maxKB * 1024) {
            return "{$label} không được vượt quá {$maxKB}KB.";
        }
        return null;
    }

    // -------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------

    /**
     * Get a human-readable label for a field.
     */
    private static function label(string $field): string
    {
        return self::$fieldNames[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}
