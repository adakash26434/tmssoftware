<?php
function e(mixed $v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// नेपालीमा: Asset (CSS/JS/image) ko full URL banaune
function asset(string $path): string {
    return SITE_URL . '/assets/' . ltrim($path, '/');
}

// नेपालीमा: Site relative path lai full URL banaune
function url(string $path): string {
    return SITE_URL . '/' . ltrim($path, '/');
}

// नेपालीमा: Browser lai arko URL ma pathaune
function redirect(string $path): void {
    header("Location: " . url($path));
    exit;
}

// नेपालीमा: Flash message session ma store garne
function setFlash(string $key, string $msg): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'][$key] = $msg;
}

// Legacy alias
function flash(string $key, string $msg): void { setFlash($key, $msg); }

// नेपालीमा: Flash message read garera mitaune (one-shot)
function getFlash(string $key): ?string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

// ── Site settings from key-value table ─────────────────────────
function siteSettings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $defaults = [
        'site_name'        => SITE_NAME,
        'site_tagline'     => 'Cooperative Software for Nepal',
        'logo_url'         => null,
        'favicon_url'      => null,
        'contact_email'    => 'ankurinfotech8@gmail.com',
        'contact_phone'    => '+977-071-438585, 071-437612',
        'address'          => 'Butwal, Rupandehi, Nepal',
        'social_links'     => [],
        'whatsapp_number'  => null,
        'whatsapp_enabled' => true,
        'whatsapp_message' => "Hello Ankur Infotech Pvt. Ltd.! I'm interested in your software.",
        'maintenance_mode' => false,
    ];
    try {
        $rows = query("SELECT setting_key, setting_val FROM site_settings");
        $map  = [];
        foreach ($rows as $r) $map[$r['setting_key']] = $r['setting_val'];
        if (!empty($map)) {
            if (isset($map['social_links'])) {
                $map['social_links'] = json_decode($map['social_links'], true) ?? [];
            }
            $cache = array_merge($defaults, $map);
            return $cache;
        }
    } catch (\Throwable $e) {}
    $cache = $defaults;
    return $cache;
} // यहाँ Bracket बन्द गरिएको छ

// ── CMS bilingual helper ─────────────────────────────────────────
// Returns Nepali site_settings value when user is browsing in Nepali,
// falls back to English value, then to $default.
function cms(array $s, string $key, string $default = ''): string {
    if (isNepali()) {
        $np = trim((string)($s[$key . '_np'] ?? ''));
        if ($np !== '') return $np;
    }
    $en = trim((string)($s[$key] ?? ''));
    return $en !== '' ? $en : $default;
}

// ── CSRF helpers ────────────────────────────────────────────────
function generateCsrf(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// नेपालीमा: POST ko CSRF token check garne
function verifyCsrf(?string $token = null): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $token ?? ($_POST['_csrf'] ?? $_POST['_token'] ?? $_POST['csrf_token'] ?? '');
    $valid = !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    // Rotate instead of burn — avoids back-button CSRF errors
    if ($valid) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        http_response_code(403);
        echo '<div class="alert alert-error">Security token mismatch. Please <a href="javascript:history.back()" style="text-decoration:underline;">go back</a> and try again.</div>';
        exit;
    }
    return true;
}

// Legacy alias used in old pages
function csrfToken(): string { return generateCsrf(); }

// ── Badge helpers (uses theme.css badge classes) ─────────────────
function statusBadge(string $status): string {
    $cls = 'badge-' . ($status ?: 'closed');
    return '<span class="badge ' . $cls . '">' . e(ucwords(str_replace('_', ' ', $status))) . '</span>';
}

// नेपालीमा: Priority ko colored badge HTML banaune
function priorityBadge(string $p): string {
    $cls = 'badge-' . ($p ?: 'normal');
    return '<span class="badge ' . $cls . '">' . e(ucfirst($p)) . '</span>';
}

// ── Time ─────────────────────────────────────────────────────────
function timeAgo(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($dt));
}

// ── Pagination ───────────────────────────────────────────────────
function paginate(int $total, int $perPage, int $current): array {
    $pages = (int) ceil($total / $perPage);
    return [
        'total'   => $total,
        'pages'   => $pages,
        'current' => $current,
        'offset'  => ($current - 1) * $perPage,
        'perPage' => $perPage,
    ];
}

// ── Upload helper ─────────────────────────────────────────────────
function handleUpload(string $field, string $dir = 'uploads'): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $file     = $_FILES[$field];
    $allowed  = ['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
    $maxBytes = 5 * 1024 * 1024;
    if (!in_array($file['type'], $allowed, true) || $file['size'] > $maxBytes) return null;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = bin2hex(random_bytes(12)) . '.' . strtolower($ext);
    $dest = __DIR__ . '/../' . $dir . '/' . $name;
    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
    return SITE_URL . '/' . $dir . '/' . $name;
}

// ── Text truncate ─────────────────────────────────────────────────
function truncate(string $s, int $len = 100, string $suffix = '…'): string {
    return mb_strlen($s) <= $len ? $s : mb_substr($s, 0, $len) . $suffix;
}

// ── Slug generator ────────────────────────────────────────────────
function makeSlug(string $s): string {
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s), '-'));
    return preg_replace('/-+/', '-', $s);
}

// नेपालीमा: csrfField() — yo function le aafno kaam garchha
function csrfField(): string {
    return '<input type="hidden" name="_csrf" value="' . e(generateCsrf()) . '">';
}

// नेपालीमा: Lucide SVG icon ko HTML banaune (size + inline style sahit)
function icon(string $name, int $size = 16, string $style = ''): string {
    $s = "width:{$size}px;height:{$size}px;display:inline-block;vertical-align:middle;flex-shrink:0;";
    if ($style) $s .= $style;
    return '<i data-lucide="' . e($name) . '" style="' . $s . '"></i>';
}

// ── Audit log helper ─────────────────────────────────────────────
// नेपालीमा: Admin action haru lai audit_log table ma record garne
// Usage: logAudit('user.delete', 'Deleted user id=42', ['target_type'=>'user','target_id'=>42])
function logAudit(string $action, string $description = '', array $meta = []): void {
    try {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId   = $_SESSION['user_id'] ?? null;
        $targetT  = $meta['target_type'] ?? null;
        $targetId = isset($meta['target_id']) ? (int)$meta['target_id'] : null;
        $oldVal   = isset($meta['old']) ? json_encode($meta['old']) : null;
        $newVal   = isset($meta['new'])
            ? json_encode($meta['new'])
            : ($description !== '' ? json_encode(['note' => $description]) : null);
        $ip       = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        if ($ip) $ip = trim(explode(',', $ip)[0]);
        $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300);

        // Try full schema (with ip_address + user_agent); fall back to slim schema.
        try {
            execute(
                "INSERT INTO audit_log (user_id, action, target_type, target_id, old_value, new_value, ip_address, user_agent)
                 VALUES (?,?,?,?,?,?,?,?)",
                [$userId, $action, $targetT, $targetId, $oldVal, $newVal, $ip, $ua]
            );
        } catch (\Throwable $e) {
            execute(
                "INSERT INTO audit_log (user_id, action, target_type, target_id, new_value)
                 VALUES (?,?,?,?,?)",
                [$userId, $action, $targetT, $targetId, $newVal]
            );
        }
    } catch (\Throwable $e) {
        // Audit failure le main flow lai block nagarcha
        error_log("logAudit failed: " . $e->getMessage());
    }
}
