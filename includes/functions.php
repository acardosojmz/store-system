<?php
// includes/functions.php

/**
 * Sanitizar datos de entrada
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Formatear moneda
 */
function formatCurrency($amount) {
    return '$' . number_format((float)$amount, 2, '.', ',');
}

/**
 * Formatear fecha en español
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }

    $months = [
        'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
        'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
        'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
        'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
    ];

    $days = [
        'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'
    ];

    $formatted = date($format, strtotime($date));

    // Si el formato incluye nombres de meses/días, traducirlos
    if (strpos($format, 'F') !== false || strpos($format, 'M') !== false) {
        $englishMonth = date('F', strtotime($date));
        $formatted = str_replace($englishMonth, $months[$englishMonth], $formatted);
    }

    if (strpos($format, 'l') !== false || strpos($format, 'D') !== false) {
        $englishDay = date('l', strtotime($date));
        $formatted = str_replace($englishDay, $days[$englishDay], $formatted);
    }

    return $formatted;
}

/**
 * Formatear fecha y hora
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    return formatDate($datetime, $format);
}

/**
 * Redireccionar a una URL
 */
function redirect($url) {
    $fullUrl = BASE_URL . ltrim($url, '/');
    header("Location: " . $fullUrl);
    exit();
}

/**
 * Mostrar alerta en sesión
 */
function showAlert($message, $type = 'info') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type,
        'timestamp' => time()
    ];
}

/**
 * Obtener y limpiar alerta de sesión
 */
function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

/**
 * Cargar clase automáticamente
 */
function loadClass($className) {
    $file = __DIR__ . "/../classes/{$className}.php";
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar número de teléfono (formato mexicano)
 */
function validatePhone($phone) {
    $pattern = '/^(\+52\s?)?(\d{2,4}\s?)?\d{6,8}$/';
    return preg_match($pattern, $phone);
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generar campo oculto para CSRF
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Logging básico
 */
function logMessage($message, $level = 'INFO', $file = 'app.log') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$level}: {$message}" . PHP_EOL;

    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    file_put_contents($logDir . '/' . $file, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Logging de errores
 */
function logError($message, $context = []) {
    $contextStr = !empty($context) ? json_encode($context) : '';
    logMessage($message . ' ' . $contextStr, 'ERROR');
}

/**
 * Obtener información del cliente (IP, User Agent, etc.)
 */
function getClientInfo() {
    return [
        'ip' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'timestamp' => date('Y-m-d H:i:s'),
        'referer' => $_SERVER['HTTP_REFERER'] ?? null
    ];
}

/**
 * Obtener IP real del cliente
 */
function getClientIP() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) {
                $ip = explode(',', $ip)[0];
            }
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Paginación simple
 */
function paginate($total, $perPage = 20, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($totalPages, $currentPage));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'prev_page' => $currentPage > 1 ? $currentPage - 1 : null,
        'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null
    ];
}

/**
 * Generar enlaces de paginación
 */
function paginationLinks($pagination, $baseUrl, $params = []) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }

    $html = '<nav aria-label="Paginación"><ul class="pagination">';

    // Enlace anterior
    if ($pagination['has_prev']) {
        $prevUrl = $baseUrl . '?' . http_build_query(array_merge($params, ['page' => $pagination['prev_page']]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">‹ Anterior</a></li>';
    }

    // Enlaces de páginas
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);

    if ($start > 1) {
        $firstUrl = $baseUrl . '?' . http_build_query(array_merge($params, ['page' => 1]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $firstUrl . '">1</a></li>';
        if ($start > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $pageUrl = $baseUrl . '?' . http_build_query(array_merge($params, ['page' => $i]));
        $active = $i == $pagination['current_page'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $pageUrl . '">' . $i . '</a></li>';
    }

    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $lastUrl = $baseUrl . '?' . http_build_query(array_merge($params, ['page' => $pagination['total_pages']]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $lastUrl . '">' . $pagination['total_pages'] . '</a></li>';
    }

    // Enlace siguiente
    if ($pagination['has_next']) {
        $nextUrl = $baseUrl . '?' . http_build_query(array_merge($params, ['page' => $pagination['next_page']]));
        $html .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">Siguiente ›</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Convertir bytes a formato legible
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Truncar texto
 */
function truncateText($text, $limit = 100, $suffix = '...') {
    if (strlen($text) <= $limit) {
        return $text;
    }
    return substr($text, 0, $limit) . $suffix;
}

/**
 * Generar slug a partir de texto
 */
function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[áàäâã]/u', 'a', $text);
    $text = preg_replace('/[éèëê]/u', 'e', $text);
    $text = preg_replace('/[íìïî]/u', 'i', $text);
    $text = preg_replace('/[óòöôõ]/u', 'o', $text);
    $text = preg_replace('/[úùüû]/u', 'u', $text);
    $text = preg_replace('/[ñ]/u', 'n', $text);
    $text = preg_replace('/[ç]/u', 'c', $text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Verificar si es petición AJAX
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Enviar respuesta JSON
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Obtener configuración del sistema
 */
function getConfig($key = null, $default = null) {
    static $config = null;

    if ($config === null) {
        $configFile = __DIR__ . '/../config/app_config.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
        } else {
            $config = [];
        }
    }

    if ($key === null) {
        return $config;
    }

    return $config[$key] ?? $default;
}

/**
 * Validar datos según reglas
 */
function validateData($data, $rules) {
    $errors = [];

    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? null;
        $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

        foreach ($fieldRules as $rule) {
            $ruleParts = explode(':', $rule, 2);
            $ruleName = $ruleParts[0];
            $ruleParam = $ruleParts[1] ?? null;

            switch ($ruleName) {
                case 'required':
                    if (empty($value)) {
                        $errors[$field][] = "El campo {$field} es obligatorio";
                    }
                    break;

                case 'email':
                    if (!empty($value) && !validateEmail($value)) {
                        $errors[$field][] = "El campo {$field} debe ser un email válido";
                    }
                    break;

                case 'min':
                    if (!empty($value) && strlen($value) < $ruleParam) {
                        $errors[$field][] = "El campo {$field} debe tener al menos {$ruleParam} caracteres";
                    }
                    break;

                case 'max':
                    if (!empty($value) && strlen($value) > $ruleParam) {
                        $errors[$field][] = "El campo {$field} no puede tener más de {$ruleParam} caracteres";
                    }
                    break;

                case 'numeric':
                    if (!empty($value) && !is_numeric($value)) {
                        $errors[$field][] = "El campo {$field} debe ser numérico";
                    }
                    break;

                case 'integer':
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                        $errors[$field][] = "El campo {$field} debe ser un número entero";
                    }
                    break;

                case 'positive':
                    if (!empty($value) && (float)$value <= 0) {
                        $errors[$field][] = "El campo {$field} debe ser un valor positivo";
                    }
                    break;
            }
        }
    }

    return $errors;
}

/**
 * Generar breadcrumbs
 */
function generateBreadcrumbs($items) {
    if (empty($items)) {
        return '';
    }

    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';

    $total = count($items);
    foreach ($items as $index => $item) {
        $isLast = ($index === $total - 1);

        if ($isLast) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $url = isset($item['url']) ? $item['url'] : '#';
            $html .= '<li class="breadcrumb-item"><a href="' . $url . '">' . htmlspecialchars($item['title']) . '</a></li>';
        }
    }

    $html .= '</ol></nav>';
    return $html;
}

/**
 * Escapar output HTML
 */
function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Debug: imprimir variable de forma legible
 */
function dd($var) {
    echo '<pre style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 5px;">';
    var_dump($var);
    echo '</pre>';
}

/**
 * Incluir vista con datos
 */
function view($viewPath, $data = []) {
    extract($data);
    $viewFile = __DIR__ . "/../views/{$viewPath}.php";

    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        throw new Exception("Vista no encontrada: {$viewPath}");
    }
}

/**
 * Generar hash seguro para contraseñas
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificar contraseña
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Tiempo transcurrido en formato humano
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'hace menos de un minuto';
    if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
    if ($time < 2628000) return 'hace ' . floor($time/86400) . ' días';
    if ($time < 31536000) return 'hace ' . floor($time/2628000) . ' meses';
    return 'hace ' . floor($time/31536000) . ' años';
}

/**
 * Obtener extensión de archivo
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Verificar si archivo es imagen
 */
function isImageFile($filename) {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    return in_array(getFileExtension($filename), $allowedExtensions);
}

/**
 * Generar nombre único para archivo
 */
function generateUniqueFilename($originalName) {
    $extension = getFileExtension($originalName);
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    $timestamp = time();
    $random = substr(md5(uniqid()), 0, 8);

    return "{$basename}_{$timestamp}_{$random}.{$extension}";
}

/**
 * Crear directorio si no existe
 */
function ensureDirectoryExists($path) {
    if (!is_dir($path)) {
        return mkdir($path, 0777, true);
    }
    return true;
}
?>