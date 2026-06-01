<?php
require_once __DIR__ . '/config.php';

// नेपालीमा: Database connection paunne (singleton PDO)
function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    // ── SQLite (Replit dev environment) ─────────────────────
    if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
        $dbPath = DB_SQLITE_PATH;
        if (!is_dir(dirname($dbPath))) mkdir(dirname($dbPath), 0755, true);
        $pdo = new PDO('sqlite:' . $dbPath, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec("PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;");
        // Auto-init schema + seed data if DB is empty
        require_once __DIR__ . '/sqlite-init.php';
        if (!sqliteIsInitialized($pdo)) sqliteInit($pdo);
        return $pdo;
    }

    // ── MySQL / MariaDB (cPanel production) ─────────────────
    $socket = defined('DB_SOCKET') ? DB_SOCKET : (getenv('MYSQL_SOCKET') ?: '/tmp/mysql.sock');
    if ($socket && file_exists($socket)) {
        $dsn = "mysql:unix_socket={$socket};dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    } else {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    }
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
}

// नेपालीमा: Multiple rows return garne query helper
function query(string $sql, array $params = []): array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// नेपालीमा: Ek row matra return garne query helper
function queryOne(string $sql, array $params = []): ?array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

// नेपालीमा: INSERT/UPDATE/DELETE chalauney ra last insert ID return garne
function execute(string $sql, array $params = []): int {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return (int) getDB()->lastInsertId();
}

// नेपालीमा: Table ma kati row chha — count garne
function count_rows(string $table, string $where = '1', array $params = []): int {
    $row = queryOne("SELECT COUNT(*) as c FROM `$table` WHERE $where", $params);
    return (int)($row['c'] ?? 0);
}

// नेपालीमा: Random UUID generate garne
function uuid(): string {
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}

// नेपालीमा: SQLite ra MySQL duwai kaam garne RAND()/RANDOM() helper
function sqlRand(): string {
    return (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') ? 'RANDOM()' : 'RAND()';
}
