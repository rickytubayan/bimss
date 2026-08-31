<?php
class Validator {
    private $errors = [];

    public function validate(array $data, array $rules) {
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }

        return empty($this->errors) ? true : $this->errors;
    }

    private function applyRule($field, $value, $rule, $allData) {
        $params = [];
        if (str_contains($rule, ':')) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0' && $value !== 0) {
                    $this->errors[$field][] = "{$label} is required.";
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "{$label} must be a valid email address.";
                }
                break;

            case 'min':
                if ($value && mb_strlen($value) < (int)$params[0]) {
                    $this->errors[$field][] = "{$label} must be at least {$params[0]} characters.";
                }
                break;

            case 'max':
                if ($value && mb_strlen($value) > (int)$params[0]) {
                    $this->errors[$field][] = "{$label} must not exceed {$params[0]} characters.";
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->errors[$field][] = "{$label} must be a number.";
                }
                break;

            case 'integer':
                if ($value && !ctype_digit((string)$value)) {
                    $this->errors[$field][] = "{$label} must be a whole number.";
                }
                break;

            case 'date':
                if ($value && !strtotime($value)) {
                    $this->errors[$field][] = "{$label} must be a valid date.";
                }
                break;

            case 'in':
                if ($value && !in_array($value, $params)) {
                    $this->errors[$field][] = "{$label} must be one of: " . implode(', ', $params) . ".";
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (($allData[$confirmField] ?? null) !== $value) {
                    $this->errors[$field][] = "{$label} confirmation does not match.";
                }
                break;

            case 'unique':
                if ($value && count($params) >= 2) {
                    $db = Database::getInstance()->getConnection();
                    $table = $params[0];
                    $column = $params[1] ?? $field;
                    $excludeId = $params[2] ?? null;
                    $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ? AND deleted_at IS NULL";
                    $sqlParams = [$value];
                    if ($excludeId) {
                        $sql .= " AND id != ?";
                        $sqlParams[] = $excludeId;
                    }
                    $stmt = $db->prepare($sql);
                    $stmt->execute($sqlParams);
                    if ((int)$stmt->fetch()['count'] > 0) {
                        $this->errors[$field][] = "{$label} is already taken.";
                    }
                }
                break;

            case 'alpha':
                if ($value && !ctype_alpha(str_replace(' ', '', $value))) {
                    $this->errors[$field][] = "{$label} must contain only letters.";
                }
                break;

            case 'alpha_space':
                if ($value && !preg_match('/^[a-zA-Z\s]+$/', $value)) {
                    $this->errors[$field][] = "{$label} must contain only letters and spaces.";
                }
                break;

            case 'phone':
                if ($value && !preg_match('/^[\d\+\-\s\(\)]{7,20}$/', $value)) {
                    $this->errors[$field][] = "{$label} must be a valid phone number.";
                }
                break;

            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field][] = "{$label} must be a valid URL.";
                }
                break;

            case 'file':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
                    $allowedTypes = $params;
                    if (!empty($allowedTypes)) {
                        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowedTypes)) {
                            $this->errors[$field][] = "{$label} must be one of: " . implode(', ', $allowedTypes);
                        }
                    }
                }
                break;

            case 'max_size':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
                    $maxBytes = (int)$params[0] * 1024 * 1024;
                    if ($_FILES[$field]['size'] > $maxBytes) {
                        $this->errors[$field][] = "{$label} must not exceed {$params[0]}MB.";
                    }
                }
                break;
        }
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError($field) {
        return $this->errors[$field][0] ?? null;
    }
}
