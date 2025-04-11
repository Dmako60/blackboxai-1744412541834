<?php
require_once __DIR__ . '/Middleware.php';
require_once __DIR__ . '/../utils/Response.php';

class ValidationMiddleware extends Middleware {
    private $rules;

    public function __construct($next = null, $rules = []) {
        parent::__construct($next);
        $this->rules = $rules;
    }

    public function handle($request) {
        $errors = [];
        $input = $request['input'] ?? [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            
            foreach ($rules as $rule) {
                $error = $this->validateField($field, $rule, $input);
                if ($error) {
                    $errors[$field] = $error;
                    break; // Stop checking other rules for this field if one fails
                }
            }
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        return $this->handleNext($request);
    }

    private function validateField($field, $rule, $input) {
        // Extract rule name and parameters
        $params = [];
        if (strpos($rule, ':') !== false) {
            list($rule, $paramString) = explode(':', $rule);
            $params = explode(',', $paramString);
        }

        $value = $input[$field] ?? null;

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0' && $value !== 0) {
                    return "$field is required";
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "Invalid email format";
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $params[0]) {
                    return "$field must be at least {$params[0]} characters";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $params[0]) {
                    return "$field must not exceed {$params[0]} characters";
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return "$field must be numeric";
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    return "$field must be an integer";
                }
                break;

            case 'float':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_FLOAT)) {
                    return "$field must be a decimal number";
                }
                break;

            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    return "Invalid date format for $field";
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    return "Invalid URL format for $field";
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    return "$field must be one of: " . implode(', ', $params);
                }
                break;

            case 'regex':
                if (!empty($value) && !preg_match($params[0], $value)) {
                    return "Invalid format for $field";
                }
                break;

            case 'confirmed':
                $confirmation = $input[$field . '_confirmation'] ?? null;
                if ($value !== $confirmation) {
                    return "$field confirmation does not match";
                }
                break;

            case 'phone':
                if (!empty($value) && !preg_match('/^\+?[1-9]\d{1,14}$/', preg_replace('/[^0-9+]/', '', $value))) {
                    return "Invalid phone number format";
                }
                break;

            case 'password':
                if (!empty($value)) {
                    if (!preg_match('/[A-Z]/', $value)) {
                        return "$field must contain at least one uppercase letter";
                    }
                    if (!preg_match('/[a-z]/', $value)) {
                        return "$field must contain at least one lowercase letter";
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        return "$field must contain at least one number";
                    }
                }
                break;
        }

        return null;
    }

    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
}
?>
