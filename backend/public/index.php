<?php
// Simple PHP backend for Escape Room Script Platform

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Admin-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Database connection
$host = '127.0.0.1';
$dbname = 'think';
$username = 'think';
$password = '123456';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['code' => 500, 'msg' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

function jsonInput(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function normalizeScriptTypeLabel(string $type): string
{
    $type = trim($type);
    if ($type === '') {
        return '';
    }

    $map = [
        'RPG' => '角色扮演',
        'rpg' => '角色扮演',
        '沉浸' => '沉浸演绎',
    ];

    return $map[$type] ?? $type;
}

function hasTable(PDO $pdo, string $table): bool
{
    static $cache = [];

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $cache[$table] = false;
    }

    return $cache[$table];
}

function hasColumn(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    if (!hasTable($pdo, $table)) {
        $cache[$key] = false;
        return false;
    }

    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        $cache[$key] = (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $cache[$key] = false;
    }

    return $cache[$key];
}

function ensureLocalSchema(PDO $pdo): void
{
    static $initialized = false;

    if ($initialized) {
        return;
    }

    try {
        $pdo->exec("ALTER TABLE admin MODIFY role VARCHAR(20) NOT NULL DEFAULT 'normal'");
    } catch (Throwable $e) {
    }

    if (!hasColumn($pdo, 'admin', 'brand_id')) {
        try {
            $pdo->exec("ALTER TABLE admin ADD COLUMN brand_id INT UNSIGNED NULL DEFAULT NULL AFTER role");
        } catch (Throwable $e) {
        }
    }

    if (!hasColumn($pdo, 'admin', 'constructor_user_id')) {
        try {
            $pdo->exec("ALTER TABLE admin ADD COLUMN constructor_user_id INT UNSIGNED NULL DEFAULT NULL AFTER brand_id");
        } catch (Throwable $e) {
        }
    }

    if (!hasColumn($pdo, 'brand', 'description')) {
        try {
            $pdo->exec("ALTER TABLE brand ADD COLUMN description TEXT NULL AFTER logo");
        } catch (Throwable $e) {
        }
    }

    if (!hasColumn($pdo, 'script', 'user_id')) {
        try {
            $pdo->exec("ALTER TABLE script ADD COLUMN user_id INT UNSIGNED NULL DEFAULT NULL AFTER category_id");
        } catch (Throwable $e) {
        }
    }

    if (!hasTable($pdo, 'construction_permission')) {
        try {
            $pdo->exec("
                CREATE TABLE construction_permission (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id INT UNSIGNED NOT NULL,
                    role_type VARCHAR(30) NOT NULL DEFAULT 'member',
                    brand_name VARCHAR(191) NOT NULL DEFAULT '',
                    company_name VARCHAR(191) NOT NULL DEFAULT '',
                    contact_name VARCHAR(100) NOT NULL DEFAULT '',
                    contact_phone VARCHAR(50) NOT NULL DEFAULT '',
                    reason TEXT NULL,
                    description TEXT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'pending',
                    review_note TEXT NULL,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (Throwable $e) {
        }
    }

    if (!hasColumn($pdo, 'construction_permission', 'description')) {
        try {
            $pdo->exec("ALTER TABLE construction_permission ADD COLUMN description TEXT NULL AFTER reason");
        } catch (Throwable $e) {
        }
    }

    if (!hasTable($pdo, 'construction_case')) {
        try {
            $pdo->exec("
                CREATE TABLE construction_case (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id INT UNSIGNED NOT NULL,
                    brand_name VARCHAR(191) NOT NULL DEFAULT '',
                    project_name VARCHAR(191) NOT NULL DEFAULT '',
                    phase VARCHAR(100) NOT NULL DEFAULT '',
                    cover VARCHAR(255) NOT NULL DEFAULT '',
                    description TEXT NULL,
                    notes LONGTEXT NULL,
                    images LONGTEXT NULL,
                    videos LONGTEXT NULL,
                    reject_reason TEXT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'pending',
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        } catch (Throwable $e) {
        }
    }

    $scriptColumns = [
        'script_type' => "ALTER TABLE script ADD COLUMN script_type VARCHAR(100) NULL DEFAULT '' AFTER description",
        'horror_level' => "ALTER TABLE script ADD COLUMN horror_level TINYINT UNSIGNED NULL DEFAULT 0 AFTER script_type",
        'difficulty' => "ALTER TABLE script ADD COLUMN difficulty VARCHAR(50) NULL DEFAULT '' AFTER horror_level",
        'room_size' => "ALTER TABLE script ADD COLUMN room_size VARCHAR(50) NULL DEFAULT '' AFTER difficulty",
        'feature_tags' => "ALTER TABLE script ADD COLUMN feature_tags TEXT NULL AFTER room_size",
        'area_size' => "ALTER TABLE script ADD COLUMN area_size INT UNSIGNED NULL DEFAULT 0 AFTER feature_tags",
        'room_count' => "ALTER TABLE script ADD COLUMN room_count VARCHAR(20) NULL DEFAULT '' AFTER area_size",
        'rotation_count' => "ALTER TABLE script ADD COLUMN rotation_count VARCHAR(20) NULL DEFAULT '' AFTER room_count",
        'npc_count' => "ALTER TABLE script ADD COLUMN npc_count VARCHAR(20) NULL DEFAULT '' AFTER rotation_count",
        'corridor_count' => "ALTER TABLE script ADD COLUMN corridor_count VARCHAR(20) NULL DEFAULT '' AFTER npc_count",
        'suitable_players' => "ALTER TABLE script ADD COLUMN suitable_players TEXT NULL AFTER corridor_count",
        'auth_status' => "ALTER TABLE script ADD COLUMN auth_status VARCHAR(30) NULL DEFAULT '' AFTER suitable_players",
        'auth_services' => "ALTER TABLE script ADD COLUMN auth_services TEXT NULL AFTER auth_status",
        'authorized_cities' => "ALTER TABLE script ADD COLUMN authorized_cities TEXT NULL AFTER auth_services",
        'auth_cities' => "ALTER TABLE script ADD COLUMN auth_cities TEXT NULL AFTER authorized_cities",
        'gallery_images' => "ALTER TABLE script ADD COLUMN gallery_images LONGTEXT NULL AFTER auth_cities",
        'video_url' => "ALTER TABLE script ADD COLUMN video_url VARCHAR(255) NULL DEFAULT '' AFTER gallery_images",
        'detail_content' => "ALTER TABLE script ADD COLUMN detail_content LONGTEXT NULL AFTER video_url",
        'authorizer' => "ALTER TABLE script ADD COLUMN authorizer VARCHAR(100) NULL DEFAULT '' AFTER detail_content",
        'price_tier1' => "ALTER TABLE script ADD COLUMN price_tier1 DECIMAL(10,2) NULL DEFAULT 0 AFTER authorizer",
    ];

    foreach ($scriptColumns as $column => $sql) {
        if (!hasColumn($pdo, 'script', $column)) {
            try {
                $pdo->exec($sql);
            } catch (Throwable $e) {
            }
        }
    }

    $initialized = true;
}

function decodeJsonArray($value): array
{
    if (is_array($value)) {
        return array_values(array_filter($value, static fn($item) => $item !== '' && $item !== null));
    }

    if (!is_string($value) || trim($value) === '') {
        return [];
    }

    $decoded = json_decode($value, true);
    if (is_array($decoded)) {
        return array_values(array_filter($decoded, static fn($item) => $item !== '' && $item !== null));
    }

    return [];
}

function encodeJsonArray($value): string
{
    return json_encode(decodeJsonArray($value), JSON_UNESCAPED_UNICODE);
}

function normalizeScriptCountValue($value): string
{
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    if ($raw === '0' || strtolower($raw) === 'none') {
        return '0';
    }

    if (preg_match('/10\+/', $raw)) {
        return '10+';
    }

    if (preg_match('/(\d+)/', $raw, $matches)) {
        return $matches[1];
    }

    return $raw;
}

function normalizeScriptPayload(array $data): array
{
    $gallery = decodeJsonArray($data['gallery_images'] ?? []);
    $coverImage = trim((string) ($data['cover_image'] ?? ''));
    if ($coverImage !== '' && !in_array($coverImage, $gallery, true)) {
        array_unshift($gallery, $coverImage);
    }

    return [
        'name' => trim((string) ($data['name'] ?? '')),
        'brand_id' => (int) ($data['brand_id'] ?? 0),
        'category_id' => (int) ($data['category_id'] ?? 0),
        'min_players' => max(1, (int) ($data['min_players'] ?? 2)),
        'max_players' => max(1, (int) ($data['max_players'] ?? 8)),
        'duration' => max(0, (int) ($data['duration'] ?? 120)),
        'status' => trim((string) ($data['status'] ?? 'draft')),
        'view_count' => max(0, (int) ($data['view_count'] ?? 0)),
        'like_count' => max(0, (int) ($data['like_count'] ?? 0)),
        'cover_image' => $coverImage,
        'description' => trim((string) ($data['description'] ?? '')),
        'script_type' => normalizeScriptTypeLabel((string) ($data['script_type'] ?? $data['type'] ?? '')),
        'horror_level' => max(0, min(5, (int) ($data['horror_level'] ?? 0))),
        'difficulty' => trim((string) ($data['difficulty'] ?? '')),
        'room_size' => trim((string) ($data['room_size'] ?? '')),
        'feature_tags' => encodeJsonArray($data['feature_tags'] ?? $data['features'] ?? []),
        'area_size' => max(0, (int) ($data['area_size'] ?? 0)),
        'room_count' => normalizeScriptCountValue($data['room_count'] ?? ''),
        'rotation_count' => normalizeScriptCountValue($data['rotation_count'] ?? ''),
        'npc_count' => normalizeScriptCountValue($data['npc_count'] ?? ''),
        'corridor_count' => normalizeScriptCountValue($data['corridor_count'] ?? ''),
        'suitable_players' => encodeJsonArray($data['suitable_players'] ?? []),
        'auth_status' => trim((string) ($data['auth_status'] ?? '')),
        'auth_services' => encodeJsonArray($data['auth_services'] ?? []),
        'authorized_cities' => encodeJsonArray($data['authorized_cities'] ?? []),
        'auth_cities' => encodeJsonArray($data['auth_cities'] ?? []),
        'gallery_images' => json_encode(array_values(array_unique($gallery)), JSON_UNESCAPED_UNICODE),
        'video_url' => trim((string) ($data['video_url'] ?? '')),
        'detail_content' => trim((string) ($data['detail_content'] ?? '')),
        'authorizer' => trim((string) ($data['authorizer'] ?? '')),
        'price_tier1' => (float) ($data['price_tier1'] ?? 0),
        'collect_count' => max(0, (int) ($data['collect_count'] ?? 0)),
        'purchase_count' => max(0, (int) ($data['purchase_count'] ?? 0)),
        'is_home_featured' => !empty($data['is_home_featured']) ? 1 : 0,
        'home_featured_sort' => max(0, (int) ($data['home_featured_sort'] ?? 0)),
        'is_script_featured' => !empty($data['is_script_featured']) ? 1 : 0,
        'script_featured_sort' => max(0, (int) ($data['script_featured_sort'] ?? 0)),
    ];
}

function buildScriptSummary(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'brand_id' => isset($row['brand_id']) ? (int) $row['brand_id'] : 0,
        'category_id' => isset($row['category_id']) ? (int) $row['category_id'] : 0,
        'thumbnail' => $row['thumbnail'] ?? $row['cover_image'] ?? '',
        'min_players' => (int) ($row['min_players'] ?? 0),
        'max_players' => (int) ($row['max_players'] ?? 0),
        'duration' => (int) ($row['duration'] ?? 0),
        'like_count' => (int) ($row['like_count'] ?? 0),
        'view_count' => (int) ($row['view_count'] ?? 0),
        'collect_count' => (int) ($row['collect_count'] ?? 0),
        'purchase_count' => (int) ($row['purchase_count'] ?? 0),
        'home_featured_sort' => (int) ($row['home_featured_sort'] ?? 0),
        'is_script_featured' => (int) ($row['is_script_featured'] ?? 0),
        'script_featured_sort' => (int) ($row['script_featured_sort'] ?? 0),
        'type' => normalizeScriptTypeLabel((string) ($row['script_type'] ?? '')),
        'horror_level' => (int) ($row['horror_level'] ?? 0),
        'difficulty' => $row['difficulty'] ?? '',
        'room_size' => $row['room_size'] ?? '',
        'price_tier1' => (float) ($row['price_tier1'] ?? 0),
        'is_home_featured' => (int) ($row['is_home_featured'] ?? 0),
        'area_size' => (int) ($row['area_size'] ?? 0),
        'room_count' => $row['room_count'] ?? '',
        'rotation_count' => $row['rotation_count'] ?? '',
        'npc_count' => $row['npc_count'] ?? '',
        'corridor_count' => $row['corridor_count'] ?? '',
        'auth_status' => $row['auth_status'] ?? '',
        'features' => decodeJsonArray($row['feature_tags'] ?? []),
        'suitable_players' => decodeJsonArray($row['suitable_players'] ?? []),
        'auth_services' => decodeJsonArray($row['auth_services'] ?? []),
        'authorized_cities' => decodeJsonArray($row['authorized_cities'] ?? []),
        'auth_cities' => decodeJsonArray($row['auth_cities'] ?? []),
    ];
}

function parseRangePreset(string $preset): array
{
    if ($preset === '') {
        return [0, 0];
    }

    if (preg_match('/(\d+)\D+(\d+)/', $preset, $matches)) {
        return [(int) $matches[1], (int) $matches[2]];
    }

    if (preg_match('/(\d+)\D*\+/', $preset, $matches)) {
        return [(int) $matches[1], PHP_INT_MAX];
    }

    return [0, 0];
}

function buildCountRangeExpression(string $column): string
{
    return "CASE "
        . "WHEN $column LIKE '%不可%' THEN 0 "
        . "WHEN $column LIKE '%10+%' THEN 10 "
        . "ELSE CAST($column AS UNSIGNED) END";
}

function buildScriptFilterSql(array $query, array &$params): array
{
    $conditions = [];

    $keyword = trim((string) ($query['keyword'] ?? ''));
    if ($keyword !== '') {
        $conditions[] = '(name LIKE ? OR description LIKE ? OR detail_content LIKE ?)';
        $like = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if (($query['priceMin'] ?? '') !== '') {
        $conditions[] = 'price_tier1 >= ?';
        $params[] = (float) $query['priceMin'];
    }
    if (($query['priceMax'] ?? '') !== '') {
        $conditions[] = 'price_tier1 <= ?';
        $params[] = (float) $query['priceMax'];
    }

    if (($query['playersMin'] ?? '') !== '') {
        $conditions[] = 'max_players >= ?';
        $params[] = (int) $query['playersMin'];
    }
    if (($query['playersMax'] ?? '') !== '') {
        $conditions[] = 'min_players <= ?';
        $params[] = (int) $query['playersMax'];
    }

    $types = array_filter(array_map('normalizeScriptTypeLabel', array_map('trim', explode(',', (string) ($query['types'] ?? '')))));
    if ($types) {
        $typeParts = [];
        foreach ($types as $type) {
            $typeParts[] = 'script_type LIKE ?';
            $params[] = '%' . $type . '%';
        }
        $conditions[] = '(' . implode(' OR ', $typeParts) . ')';
    }

    $players = array_filter(array_map('trim', explode(',', (string) ($query['players'] ?? ''))));
    if ($players) {
        $parts = [];
        foreach ($players as $player) {
            $normalized = normalizeScriptCountValue($player);
            if ($normalized === '10+') {
                $parts[] = 'max_players >= 10';
            } elseif ($normalized !== '') {
                $parts[] = '(min_players <= ? AND max_players >= ?)';
                $params[] = (int) $normalized;
                $params[] = (int) $normalized;
            }
        }
        if ($parts) {
            $conditions[] = '(' . implode(' OR ', $parts) . ')';
        }
    }

    $durationRange = trim((string) ($query['durationRange'] ?? ''));
    if ($durationRange !== '') {
        [$min, $max] = parseRangePreset($durationRange);
        if ($min || $max) {
            $conditions[] = 'duration BETWEEN ? AND ?';
            $params[] = $min;
            $params[] = $max;
        }
    }

    $horror = trim((string) ($query['horrorLevel'] ?? ''));
    if ($horror !== '') {
        if ($horror === '重恐') {
            $conditions[] = 'horror_level >= 5';
        } elseif ($horror === '中恐') {
            $conditions[] = 'horror_level = 4';
        } elseif ($horror === '微恐') {
            $conditions[] = 'horror_level BETWEEN 2 AND 3';
        } elseif ($horror === '非恐') {
            $conditions[] = 'horror_level <= 1';
        }
    }

    foreach (['difficulty', 'roomSize' => 'room_size', 'authStatus' => 'auth_status'] as $key => $column) {
        if (is_int($key)) {
            $key = $column;
        }
        $value = trim((string) ($query[$key] ?? ''));
        if ($value !== '') {
            $conditions[] = "$column = ?";
            $params[] = $value;
        }
    }

    $areaPreset = trim((string) ($query['areaPreset'] ?? ''));
    if ($areaPreset !== '') {
        [$min, $max] = parseRangePreset($areaPreset);
        if ($max === PHP_INT_MAX) {
            $conditions[] = 'area_size >= ?';
            $params[] = $min;
        } elseif ($min || $max) {
            $conditions[] = 'area_size BETWEEN ? AND ?';
            $params[] = $min;
            $params[] = $max;
        }
    }
    if (($query['areaMin'] ?? '') !== '') {
        $conditions[] = 'area_size >= ?';
        $params[] = (int) $query['areaMin'];
    }
    if (($query['areaMax'] ?? '') !== '') {
        $conditions[] = 'area_size <= ?';
        $params[] = (int) $query['areaMax'];
    }

    foreach ([
        'roomCount' => 'room_count',
        'rotation' => 'rotation_count',
        'npc' => 'npc_count',
        'corridorCount' => 'corridor_count',
    ] as $prefix => $column) {
        $minKey = $prefix . 'Min';
        $maxKey = $prefix . 'Max';
        $expression = buildCountRangeExpression($column);

        if (($query[$minKey] ?? '') !== '') {
            $conditions[] = "$expression >= ?";
            $params[] = (int) $query[$minKey];
        }

        if (($query[$maxKey] ?? '') !== '') {
            $conditions[] = "$expression <= ?";
            $params[] = (int) $query[$maxKey];
        }
    }

    foreach ([
        'roomCounts' => 'room_count',
        'rotation' => 'rotation_count',
        'npcs' => 'npc_count',
        'corridorCounts' => 'corridor_count',
    ] as $key => $column) {
        $values = array_filter(array_map('normalizeScriptCountValue', explode(',', (string) ($query[$key] ?? ''))));
        if ($values) {
            $parts = [];
            foreach ($values as $value) {
                if ($value === '10+') {
                    $parts[] = "$column = ?";
                    $params[] = '10+';
                } else {
                    $parts[] = "$column = ?";
                    $params[] = $value;
                }
            }
            $conditions[] = '(' . implode(' OR ', $parts) . ')';
        }
    }

    foreach ([
        'features' => 'feature_tags',
        'suitablePlayers' => 'suitable_players',
        'authServices' => 'auth_services',
        'authorizedCities' => 'authorized_cities',
        'authCities' => 'auth_cities',
    ] as $key => $column) {
        $values = array_filter(array_map('trim', explode(',', (string) ($query[$key] ?? ''))));
        if ($values) {
            $parts = [];
            foreach ($values as $value) {
                $parts[] = "$column LIKE ?";
                $params[] = '%' . $value . '%';
            }
            $conditions[] = '(' . implode(' OR ', $parts) . ')';
        }
    }

    return $conditions;
}

function collectDynamicFilterOptions(array $scripts): array
{
    $dynamic = [
        'types' => [],
        'features' => [],
        'roomCounts' => [],
        'rotation' => [],
        'npcs' => [],
        'corridorCounts' => [],
        'suitablePlayers' => [],
        'authServices' => [],
        'authorizedCities' => [],
        'authCities' => [],
    ];

    foreach ($scripts as $script) {
        foreach ([
            'types' => [$script['type'] ?? ''],
            'features' => $script['features'] ?? [],
            'roomCounts' => [$script['room_count'] ?? ''],
            'rotation' => [$script['rotation_count'] ?? ''],
            'npcs' => [$script['npc_count'] ?? ''],
            'corridorCounts' => [$script['corridor_count'] ?? ''],
            'suitablePlayers' => $script['suitable_players'] ?? [],
            'authServices' => $script['auth_services'] ?? [],
            'authorizedCities' => $script['authorized_cities'] ?? [],
            'authCities' => $script['auth_cities'] ?? [],
        ] as $key => $values) {
            foreach ((array) $values as $value) {
                $value = trim((string) $value);
                if ($value !== '' && strpos($value, '?') === false && strpos($value, '�') === false && !in_array($value, $dynamic[$key], true)) {
                    $dynamic[$key][] = $value;
                }
            }
        }
    }

    foreach ($dynamic as $key => $values) {
        sort($values);
        $dynamic[$key] = array_values($values);
    }

    return $dynamic;
}

function normalizeConstructionCasePayload(array $data): array
{
    $images = decodeJsonArray($data['images'] ?? []);
    $videos = decodeJsonArray($data['videos'] ?? []);
    $notes = decodeJsonArray($data['notes'] ?? []);
    $cover = trim((string) ($data['cover'] ?? ''));

    if ($cover === '' && $images) {
        $cover = $images[0];
    }

    return [
        'brand_name' => trim((string) ($data['brand_name'] ?? '')),
        'project_name' => trim((string) ($data['project_name'] ?? $data['title'] ?? '')),
        'phase' => trim((string) ($data['phase'] ?? '')),
        'cover' => $cover,
        'description' => trim((string) ($data['description'] ?? '')),
        'notes' => json_encode($notes, JSON_UNESCAPED_UNICODE),
        'images' => json_encode($images, JSON_UNESCAPED_UNICODE),
        'videos' => json_encode($videos, JSON_UNESCAPED_UNICODE),
        'status' => trim((string) ($data['status'] ?? 'pending')),
        'reject_reason' => trim((string) ($data['reject_reason'] ?? '')),
    ];
}

function buildConstructionCaseItem(array $row): array
{
    $images = decodeJsonArray($row['images'] ?? []);
    $videos = decodeJsonArray($row['videos'] ?? []);
    $notes = decodeJsonArray($row['notes'] ?? []);
    $cover = trim((string) ($row['cover'] ?? ''));

    if ($cover === '' && $images) {
        $cover = $images[0];
    }

    return [
        'id' => (int) $row['id'],
        'user_id' => isset($row['user_id']) ? (int) $row['user_id'] : 0,
        'brand_name' => $row['brand_name'] ?? '',
        'project_name' => $row['project_name'] ?? '',
        'phase' => $row['phase'] ?? '',
        'cover' => $cover,
        'description' => $row['description'] ?? '',
        'notes' => $notes,
        'images' => $images,
        'videos' => $videos,
        'status' => $row['status'] ?? 'pending',
        'reject_reason' => $row['reject_reason'] ?? '',
        'created_at' => $row['created_at'] ?? '',

        // Compatibility for existing mini program pages.
        'projectName' => $row['project_name'] ?? '',
        'brandName' => $row['brand_name'] ?? '',
        'createdAt' => $row['created_at'] ?? '',
        'isOfficial' => ($row['status'] ?? '') === 'approved',
        'title' => $row['project_name'] ?? '',
    ];
}

function buildConstructorSummary(array $permission, array $cases = []): array
{
    $latestCase = $cases[0] ?? null;
    $galleryCount = 0;
    $videoCount = 0;
    foreach ($cases as $case) {
        $galleryCount += count($case['images'] ?? []);
        $videoCount += count($case['videos'] ?? []);
    }

    return [
        'id' => (int) ($permission['user_id'] ?? 0),
        'name' => $permission['company_name'] ?: ('施工方 #' . ($permission['user_id'] ?? 0)),
        'company_name' => $permission['company_name'] ?? '',
        'brand_name' => $permission['brand_name'] ?? '',
        'description' => $permission['description'] ?? ($permission['reason'] ?? ''),
        'role_type' => $permission['role_type'] ?? 'constructor',
        'status' => $permission['status'] ?? 'pending',
        'cover' => $latestCase['cover'] ?? '',
        'case_count' => count($cases),
        'image_count' => $galleryCount,
        'video_count' => $videoCount,
        'latest_case' => $latestCase,
        'cases' => $cases,
    ];
}

function getBearerToken(): string
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (!$authorization) {
        return '';
    }

    return preg_replace('/^Bearer\s+/i', '', $authorization);
}

function getAdminToken(): string
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    return trim((string) ($headers['Admin-Token'] ?? $headers['admin-token'] ?? ''));
}

function getBaseOrigin(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host;
}

function getCurrentUserId(PDO $pdo): ?int
{
    $token = getBearerToken();
    if (!$token) {
        return null;
    }

    if (preg_match('/^user_(\d+)$/', $token, $matches)) {
        return (int) $matches[1];
    }

    if (is_numeric($token)) {
        return (int) $token;
    }

    return null;
}

function getCurrentUser(PDO $pdo): ?array
{
    $userId = getCurrentUserId($pdo);
    if (!$userId || !hasTable($pdo, 'user')) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, openid, nickname, avatar, created_at FROM user WHERE id = ?');
    $stmt->execute([$userId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function requireUser(PDO $pdo): array
{
    $user = getCurrentUser($pdo);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['code' => 401, 'msg' => 'Unauthorized']);
        exit;
    }

    return $user;
}

function getCurrentAdmin(PDO $pdo): ?array
{
    $token = getAdminToken();
    if ($token === '') {
        return null;
    }

    if (!preg_match('/^admin_(\d+)$/', $token, $matches)) {
        return null;
    }

    $stmt = $pdo->prepare('
        SELECT a.id, a.username, a.role, a.brand_id, a.constructor_user_id, a.created_at, b.name AS brand_name,
               cp.company_name AS constructor_company_name,
               cp.description AS constructor_description
        FROM admin a
        LEFT JOIN brand b ON b.id = a.brand_id
        LEFT JOIN (
            SELECT cp1.*
            FROM construction_permission cp1
            INNER JOIN (
                SELECT user_id, MAX(id) AS max_id
                FROM construction_permission
                GROUP BY user_id
            ) latest_cp ON latest_cp.max_id = cp1.id
        ) cp ON cp.user_id = a.constructor_user_id
        WHERE a.id = ?
    ');
    $stmt->execute([(int) $matches[1]]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function requireAdmin(PDO $pdo): array
{
    $admin = getCurrentAdmin($pdo);
    if (!$admin) {
        http_response_code(401);
        echo json_encode(['code' => 401, 'msg' => 'Unauthorized']);
        exit;
    }

    return $admin;
}

function requireSuperAdmin(array $admin): void
{
    if (in_array(($admin['role'] ?? ''), ['brand', 'constructor'], true)) {
        http_response_code(403);
        echo json_encode(['code' => 403, 'msg' => 'Only platform admin can access this resource']);
        exit;
    }
}

function requireBrandAccount(array $admin): void
{
    if (($admin['role'] ?? '') !== 'brand' || empty($admin['brand_id'])) {
        http_response_code(403);
        echo json_encode(['code' => 403, 'msg' => 'Brand account required']);
        exit;
    }
}

function requireConstructorAccount(array $admin): void
{
    if (($admin['role'] ?? '') !== 'constructor' || empty($admin['constructor_user_id'])) {
        http_response_code(403);
        echo json_encode(['code' => 403, 'msg' => 'Constructor account required']);
        exit;
    }
}

function createOrUpdateBrandAdmin(PDO $pdo, int $brandId, array $data): void
{
    $username = trim((string) ($data['account_username'] ?? ''));
    $password = (string) ($data['account_password'] ?? '');

    if ($username === '') {
        return;
    }

    $stmt = $pdo->prepare('SELECT id FROM admin WHERE username = ? AND brand_id <> ?');
    $stmt->execute([$username, $brandId]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(422);
        echo json_encode(['code' => 422, 'msg' => 'Brand account username already exists']);
        exit;
    }

    $existingStmt = $pdo->prepare("SELECT id FROM admin WHERE brand_id = ? AND role = 'brand' LIMIT 1");
    $existingStmt->execute([$brandId]);
    $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        if ($password !== '') {
            $updateStmt = $pdo->prepare('UPDATE admin SET username = ?, password = ? WHERE id = ?');
            $updateStmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $existing['id']]);
            return;
        }

        $updateStmt = $pdo->prepare('UPDATE admin SET username = ? WHERE id = ?');
        $updateStmt->execute([$username, $existing['id']]);
        return;
    }

    $nextPassword = $password !== '' ? $password : 'admin123';
    $insertStmt = $pdo->prepare("INSERT INTO admin (username, password, role, brand_id) VALUES (?, ?, 'brand', ?)");
    $insertStmt->execute([$username, password_hash($nextPassword, PASSWORD_DEFAULT), $brandId]);
}

function createOrUpdateConstructorAdmin(PDO $pdo, int $constructorUserId, array $permission): void
{
    $baseUsername = 'constructor_' . $constructorUserId;
    $username = $baseUsername;

    $existingStmt = $pdo->prepare("SELECT id, username FROM admin WHERE constructor_user_id = ? AND role = 'constructor' LIMIT 1");
    $existingStmt->execute([$constructorUserId]);
    $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $updateStmt = $pdo->prepare('UPDATE admin SET username = ? WHERE id = ?');
        $updateStmt->execute([$existing['username'] ?: $username, $existing['id']]);
        return;
    }

    $suffix = 1;
    while (true) {
        $conflictStmt = $pdo->prepare('SELECT id FROM admin WHERE username = ? LIMIT 1');
        $conflictStmt->execute([$username]);
        if (!$conflictStmt->fetch(PDO::FETCH_ASSOC)) {
            break;
        }
        $suffix++;
        $username = $baseUsername . '_' . $suffix;
    }

    $insertStmt = $pdo->prepare("INSERT INTO admin (username, password, role, constructor_user_id) VALUES (?, ?, 'constructor', ?)");
    $insertStmt->execute([$username, password_hash('admin123', PASSWORD_DEFAULT), $constructorUserId]);
}

function loadChinaRegions(): array
{
    static $regions = null;

    if ($regions !== null) {
        return $regions;
    }

    $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'china-regions.json';
    if (!is_file($path)) {
        $regions = [];
        return $regions;
    }

    $json = file_get_contents($path);
    $data = json_decode($json ?: '[]', true);
    $regions = is_array($data) ? $data : [];

    return $regions;
}

ensureLocalSchema($pdo);

$approvedConstructorStmt = $pdo->query("SELECT * FROM construction_permission WHERE role_type = 'constructor' AND status = 'approved' ORDER BY id DESC");
if ($approvedConstructorStmt) {
    foreach ($approvedConstructorStmt->fetchAll(PDO::FETCH_ASSOC) as $approvedConstructor) {
        if (!empty($approvedConstructor['user_id'])) {
            createOrUpdateConstructorAdmin($pdo, (int) $approvedConstructor['user_id'], $approvedConstructor);
        }
    }
}

// Routing
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Handle root path
if ($path === '/') {
    echo json_encode([
        'code' => 200,
        'msg' => 'Escape Room Script Platform API',
        'data' => [
            'name' => 'Escape Room Script Platform',
            'version' => '1.0.0',
            'endpoints' => [
                'GET /api/home' => 'Home data',
                'GET /api/categories' => 'Category list',
                'POST /api/admin/login' => 'Admin login'
            ]
        ]
    ]);
    exit;
}

// Handle API routes
if (strpos($path, '/api/') === 0) {
    $apiPath = substr($path, 5);

    if (strpos($apiPath, 'admin/') === 0 && $apiPath !== 'admin/login') {
        $currentAdmin = requireAdmin($pdo);

        if (($currentAdmin['role'] ?? '') === 'brand' && !preg_match('#^admin/(profile|logout|scripts(?:/.*)?|categories|stats/overview)$#', $apiPath)) {
            http_response_code(403);
            echo json_encode(['code' => 403, 'msg' => 'Brand account is not allowed to access this resource']);
            exit;
        }

        if (($currentAdmin['role'] ?? '') === 'constructor' && !preg_match('#^admin/(profile|logout|construction/profile|construction/cases(?:/.*)?|stats/overview)$#', $apiPath)) {
            http_response_code(403);
            echo json_encode(['code' => 403, 'msg' => 'Constructor account is not allowed to access this resource']);
            exit;
        }
    }

    if ($apiPath === 'user/login' && $method === 'POST') {
        $data = jsonInput();
        $openid = trim((string) ($data['openid'] ?? $data['code'] ?? ''));

        if ($openid === '') {
            $openid = 'demo-' . uniqid();
        }

        $nickname = trim((string) ($data['nickname'] ?? '演示用户'));
        $avatar = trim((string) ($data['avatar'] ?? ''));

        if (!hasTable($pdo, 'user')) {
            echo json_encode(['code' => 500, 'msg' => 'User table not found']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT id, openid, nickname, avatar, created_at FROM user WHERE openid = ? LIMIT 1');
        $stmt->execute([$openid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $insert = $pdo->prepare('INSERT INTO user (openid, nickname, avatar) VALUES (?, ?, ?)');
            $insert->execute([$openid, $nickname, $avatar]);
            $stmt->execute([$openid]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $nextNickname = $nickname !== '' ? $nickname : $user['nickname'];
            $nextAvatar = $avatar !== '' ? $avatar : $user['avatar'];
            $update = $pdo->prepare('UPDATE user SET nickname = ?, avatar = ? WHERE id = ?');
            $update->execute([$nextNickname, $nextAvatar, $user['id']]);
            $user['nickname'] = $nextNickname;
            $user['avatar'] = $nextAvatar;
        }

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'token' => 'user_' . $user['id'],
                'user' => [
                    'id' => (int) $user['id'],
                    'openid' => $user['openid'],
                    'nickname' => $user['nickname'],
                    'avatar' => $user['avatar'],
                    'role' => 'member',
                ],
            ],
        ]);
        exit;
    }

    if ($apiPath === 'brand/login' && $method === 'POST') {
        $data = jsonInput();
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $stmt = $pdo->prepare("SELECT a.id, a.username, a.password, a.role, a.brand_id, b.name AS brand_name
            FROM admin a
            LEFT JOIN brand b ON b.id = a.brand_id
            WHERE a.username = ? AND a.role = 'brand'
            LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password'])) {
            echo json_encode(['code' => 401, 'msg' => 'Invalid username or password']);
            exit;
        }

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'token' => 'admin_' . $admin['id'],
                'admin' => [
                    'id' => (int) $admin['id'],
                    'username' => $admin['username'],
                    'role' => $admin['role'],
                    'brand_id' => isset($admin['brand_id']) ? (int) $admin['brand_id'] : null,
                    'brand_name' => $admin['brand_name'] ?? '',
                ]
            ]
        ]);
        exit;
    }

    if ($apiPath === 'constructor/login' && $method === 'POST') {
        $data = jsonInput();
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $stmt = $pdo->prepare("
            SELECT a.id, a.username, a.password, a.role, a.constructor_user_id,
                   cp.company_name, cp.brand_name, cp.description
            FROM admin a
            LEFT JOIN (
                SELECT cp1.*
                FROM construction_permission cp1
                INNER JOIN (
                    SELECT user_id, MAX(id) AS max_id
                    FROM construction_permission
                    GROUP BY user_id
                ) latest_cp ON latest_cp.max_id = cp1.id
            ) cp ON cp.user_id = a.constructor_user_id
            WHERE a.username = ? AND a.role = 'constructor'
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password'])) {
            echo json_encode(['code' => 401, 'msg' => 'Invalid username or password']);
            exit;
        }

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'token' => 'admin_' . $admin['id'],
                'admin' => [
                    'id' => (int) $admin['id'],
                    'username' => $admin['username'],
                    'role' => $admin['role'],
                    'constructor_user_id' => isset($admin['constructor_user_id']) ? (int) $admin['constructor_user_id'] : null,
                    'company_name' => $admin['company_name'] ?? '',
                    'brand_name' => $admin['brand_name'] ?? '',
                    'description' => $admin['description'] ?? '',
                ]
            ]
        ]);
        exit;
    }

    if ($apiPath === 'user/profile' && $method === 'GET') {
        $user = requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'id' => (int) $user['id'],
                'openid' => $user['openid'],
                'nickname' => $user['nickname'],
                'avatar' => $user['avatar'],
                'role' => 'member',
                'created_at' => $user['created_at'],
            ],
        ]);
        exit;
    }

    if ($apiPath === 'user/favorites' && $method === 'GET') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => []],
        ]);
        exit;
    }

    if ($apiPath === 'user/follows' && $method === 'GET') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => []],
        ]);
        exit;
    }

    // File upload
    if ($apiPath === 'upload' && $method === 'POST') {
        if (isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'webm'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedTypes)) {
                echo json_encode(['code' => 400, 'msg' => '只支持 JPG、PNG、GIF、WEBP 图片和 MP4、MOV、WEBM 视频']);
                exit;
            }

            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $fileUrl = '/uploads/' . $fileName;
                echo json_encode([
                    'code' => 200,
                    'msg' => 'Success',
                    'data' => ['url' => $fileUrl]
                ]);
            } else {
                echo json_encode(['code' => 500, 'msg' => '文件上传失败']);
            }
        } else {
            echo json_encode(['code' => 400, 'msg' => '请选择文件']);
        }
        exit;
    }

    if ($apiPath === 'meta/cities' && $method === 'GET') {
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => loadChinaRegions(),
            ],
        ]);
        exit;
    }

    // Home API
    if ($apiPath === 'home' && $method === 'GET') {
        // Get banners
        $stmt = $pdo->query('SELECT id, image, link FROM home_banner ORDER BY sort_order ASC');
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get ads
        $stmt = $pdo->query('SELECT id, image, link FROM home_ad ORDER BY sort_order ASC');
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get featured scripts
        $stmt = $pdo->query('SELECT id, name, cover_image as thumbnail, min_players, max_players, duration, like_count, view_count, collect_count, purchase_count, home_featured_sort, script_type, horror_level, difficulty, room_size, price_tier1, area_size, room_count, rotation_count, npc_count, corridor_count, auth_status, feature_tags, suitable_players, auth_services, authorized_cities, auth_cities, is_home_featured FROM script WHERE status = "approved" ORDER BY is_home_featured DESC, home_featured_sort ASC, like_count DESC, view_count DESC LIMIT 8');
        $scripts = array_map('buildScriptSummary', $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'banners' => $banners,
                'ads' => $ads,
                'scripts' => $scripts
            ]
        ]);
        exit;
    }

    // Categories API
    if ($apiPath === 'categories' && $method === 'GET') {
        $stmt = $pdo->query('SELECT id, name, (SELECT COUNT(*) FROM script WHERE category_id = category.id) as count FROM category ORDER BY sort_order ASC');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => $categories]
        ]);
        exit;
    }

    // Category scripts API
    if (preg_match('/^categories\/(\d+)\/scripts$/', $apiPath, $matches) && $method === 'GET') {
        $categoryId = (int) $matches[1];
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $params = [$categoryId];
        $conditions = ['category_id = ?', "status = 'approved'"];
        $conditions = array_merge($conditions, buildScriptFilterSql($_GET, $params));
        $where = 'WHERE ' . implode(' AND ', $conditions);

        $selectSql = "SELECT id, name, cover_image as thumbnail, min_players, max_players, duration, like_count, view_count, collect_count, purchase_count, script_type, horror_level, difficulty, room_size, price_tier1, area_size, room_count, rotation_count, npc_count, corridor_count, auth_status, feature_tags, suitable_players, auth_services, authorized_cities, auth_cities FROM script $where ORDER BY like_count DESC, view_count DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($selectSql);
        $stmt->execute($params);
        $scripts = array_map('buildScriptSummary', $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM script $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $filterStmt = $pdo->prepare("SELECT id, name, cover_image as thumbnail, min_players, max_players, duration, like_count, view_count, collect_count, purchase_count, script_type, horror_level, difficulty, room_size, price_tier1, area_size, room_count, rotation_count, npc_count, corridor_count, auth_status, feature_tags, suitable_players, auth_services, authorized_cities, auth_cities FROM script WHERE category_id = ? AND status = 'approved'");
        $filterStmt->execute([$categoryId]);
        $filterSource = array_map('buildScriptSummary', $filterStmt->fetchAll(PDO::FETCH_ASSOC));

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'scripts' => $scripts,
                'total' => $total,
                'filters' => [
                    'dynamic' => collectDynamicFilterOptions($filterSource),
                ]
            ]
        ]);
        exit;
    }

    if ($apiPath === 'trend/report' && $method === 'GET') {
        $startDate = trim((string) ($_GET['start_date'] ?? ''));
        $endDate = trim((string) ($_GET['end_date'] ?? ''));

        if ($endDate === '') {
            $endDate = date('Y-m-d');
        }
        if ($startDate === '') {
            $startDate = date('Y-m-d', strtotime($endDate . ' -29 days'));
        }

        $statsStmt = $pdo->prepare("
            SELECT
                COUNT(*) AS total_scripts,
                COUNT(DISTINCT brand_id) AS active_brands,
                COALESCE(SUM(view_count), 0) AS total_views,
                COALESCE(SUM(like_count), 0) AS total_likes,
                COALESCE(AVG(duration), 0) AS avg_duration,
                COALESCE(AVG((min_players + max_players) / 2), 0) AS avg_players
            FROM script
            WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ?
        ");
        $statsStmt->execute([$startDate, $endDate]);
        $overview = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $dailyStmt = $pdo->prepare("
            SELECT DATE(created_at) AS day_name, COUNT(*) AS total
            FROM script
            WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ");
        $dailyStmt->execute([$startDate, $endDate]);
        $dailyRows = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);
        $dailyMap = [];
        foreach ($dailyRows as $row) {
            $dailyMap[$row['day_name']] = (int) $row['total'];
        }
        $dailyTrend = [];
        $cursor = strtotime($startDate);
        $endCursor = strtotime($endDate);
        while ($cursor <= $endCursor) {
            $day = date('Y-m-d', $cursor);
            $dailyTrend[] = [
                'name' => $day,
                'label' => date('m-d', $cursor),
                'value' => $dailyMap[$day] ?? 0,
            ];
            $cursor = strtotime('+1 day', $cursor);
        }

        $cityRows = [];
        foreach (['authorized_cities', 'auth_cities'] as $column) {
            if (hasColumn($pdo, 'script', $column)) {
                $cityStmt = $pdo->prepare("SELECT $column FROM script WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ?");
                $cityStmt->execute([$startDate, $endDate]);
                $cityRows = array_merge($cityRows, $cityStmt->fetchAll(PDO::FETCH_COLUMN));
            }
        }
        $cityDistribution = [];
        foreach ($cityRows as $value) {
            foreach (decodeJsonArray($value) as $city) {
                $cityDistribution[$city] = ($cityDistribution[$city] ?? 0) + 1;
            }
        }
        arsort($cityDistribution);
        $cityDistribution = array_map(
            static fn($name, $value) => ['name' => $name, 'value' => $value],
            array_keys($cityDistribution),
            array_values($cityDistribution)
        );

        $brandRankStmt = $pdo->prepare("
            SELECT b.name, COUNT(*) AS total
            FROM script s
            LEFT JOIN brand b ON b.id = s.brand_id
            WHERE s.status = 'approved' AND DATE(s.created_at) BETWEEN ? AND ?
            GROUP BY s.brand_id, b.name
            ORDER BY total DESC
            LIMIT 10
        ");
        $brandRankStmt->execute([$startDate, $endDate]);
        $brandRankings = array_map(static fn($row) => ['name' => $row['name'] ?: '未命名品牌', 'value' => (int) $row['total']], $brandRankStmt->fetchAll(PDO::FETCH_ASSOC));

        $categoryRankStmt = $pdo->prepare("
            SELECT c.name, COUNT(*) AS total
            FROM script s
            LEFT JOIN category c ON c.id = s.category_id
            WHERE s.status = 'approved' AND DATE(s.created_at) BETWEEN ? AND ?
            GROUP BY s.category_id, c.name
            ORDER BY total DESC
            LIMIT 10
        ");
        $categoryRankStmt->execute([$startDate, $endDate]);
        $categoryRankings = array_map(static fn($row) => ['name' => $row['name'] ?: '未分类', 'value' => (int) $row['total']], $categoryRankStmt->fetchAll(PDO::FETCH_ASSOC));

        $typeDistStmt = $pdo->prepare("SELECT script_type, COUNT(*) AS total FROM script WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ? GROUP BY script_type ORDER BY total DESC");
        $typeDistStmt->execute([$startDate, $endDate]);
        $typeDistribution = array_map(static fn($row) => ['name' => $row['script_type'] ?: '未填写', 'value' => (int) $row['total']], $typeDistStmt->fetchAll(PDO::FETCH_ASSOC));

        $horrorDistribution = [];
        foreach ([
            ['name' => '非恐', 'where' => 'horror_level <= 1'],
            ['name' => '微恐', 'where' => 'horror_level BETWEEN 2 AND 3'],
            ['name' => '中恐', 'where' => 'horror_level = 4'],
            ['name' => '重恐', 'where' => 'horror_level >= 5'],
        ] as $bucket) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM script WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ? AND {$bucket['where']}");
            $stmt->execute([$startDate, $endDate]);
            $horrorDistribution[] = ['name' => $bucket['name'], 'value' => (int) $stmt->fetchColumn()];
        }

        $durationDistribution = [];
        foreach ([
            ['name' => '0-60分钟', 'min' => 0, 'max' => 60],
            ['name' => '61-90分钟', 'min' => 61, 'max' => 90],
            ['name' => '91-120分钟', 'min' => 91, 'max' => 120],
            ['name' => '121分钟以上', 'min' => 121, 'max' => 9999],
        ] as $bucket) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM script WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ? AND duration BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate, $bucket['min'], $bucket['max']]);
            $durationDistribution[] = ['name' => $bucket['name'], 'value' => (int) $stmt->fetchColumn()];
        }

        $playerDistribution = [];
        foreach ([
            ['name' => '2-4人', 'min' => 2, 'max' => 4],
            ['name' => '5-6人', 'min' => 5, 'max' => 6],
            ['name' => '7-8人', 'min' => 7, 'max' => 8],
            ['name' => '9人以上', 'min' => 9, 'max' => 99],
        ] as $bucket) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM script WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ? AND max_players BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate, $bucket['min'], $bucket['max']]);
            $playerDistribution[] = ['name' => $bucket['name'], 'value' => (int) $stmt->fetchColumn()];
        }

        $priceDistribution = [];
        foreach ([
            ['name' => '0-2999', 'min' => 0, 'max' => 2999],
            ['name' => '3000-5999', 'min' => 3000, 'max' => 5999],
            ['name' => '6000-9999', 'min' => 6000, 'max' => 9999],
            ['name' => '10000+', 'min' => 10000, 'max' => 999999],
        ] as $bucket) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM script WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ? AND price_tier1 BETWEEN ? AND ?");
            $stmt->execute([$startDate, $endDate, $bucket['min'], $bucket['max']]);
            $priceDistribution[] = ['name' => $bucket['name'], 'value' => (int) $stmt->fetchColumn()];
        }

        $tagPool = [];
        $tagSourceStmt = $pdo->prepare("SELECT script_type, feature_tags, suitable_players, room_size, difficulty FROM script WHERE status = 'approved' AND DATE(created_at) BETWEEN ? AND ?");
        $tagSourceStmt->execute([$startDate, $endDate]);
        foreach ($tagSourceStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            foreach ([$row['script_type'] ?? '', $row['room_size'] ?? '', $row['difficulty'] ?? ''] as $tag) {
                $tag = trim((string) $tag);
                if ($tag !== '') {
                    $tagPool[$tag] = ($tagPool[$tag] ?? 0) + 1;
                }
            }
            foreach (array_merge(decodeJsonArray($row['feature_tags'] ?? []), decodeJsonArray($row['suitable_players'] ?? [])) as $tag) {
                $tagPool[$tag] = ($tagPool[$tag] ?? 0) + 1;
            }
        }
        arsort($tagPool);
        $tags = array_map(
            static fn($name, $value) => ['name' => $name, 'value' => $value],
            array_keys(array_slice($tagPool, 0, 20, true)),
            array_values(array_slice($tagPool, 0, 20, true))
        );

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'overview' => [
                    'total_scripts' => (int) ($overview['total_scripts'] ?? 0),
                    'active_brands' => (int) ($overview['active_brands'] ?? 0),
                    'total_views' => (int) ($overview['total_views'] ?? 0),
                    'total_likes' => (int) ($overview['total_likes'] ?? 0),
                    'avg_duration' => (int) round((float) ($overview['avg_duration'] ?? 0)),
                    'avg_players' => (int) round((float) ($overview['avg_players'] ?? 0)),
                ],
                'daily_trend' => $dailyTrend,
                'city_distribution' => $cityDistribution,
                'rankings' => [
                    'brands' => $brandRankings,
                    'categories' => $categoryRankings,
                ],
                'distributions' => [
                    'horror' => $horrorDistribution,
                    'types' => $typeDistribution,
                    'duration' => $durationDistribution,
                    'players' => $playerDistribution,
                    'price' => $priceDistribution,
                ],
                'tags' => $tags,
            ],
        ]);
        exit;
    }

    // Script detail API
    if (preg_match('/^scripts\/(\d+)$/', $apiPath, $matches) && $method === 'GET') {
        $scriptId = (int) $matches[1];

        $stmt = $pdo->prepare("SELECT s.id, s.name, s.description, s.cover_image as thumbnail, s.min_players, s.max_players, s.duration, s.like_count, s.view_count, s.collect_count, s.purchase_count,
                           s.script_type, s.horror_level, s.difficulty, s.room_size, s.feature_tags, s.area_size, s.room_count, s.rotation_count, s.npc_count, s.corridor_count,
                           s.suitable_players, s.auth_status, s.auth_services, s.authorized_cities, s.auth_cities, s.gallery_images, s.video_url, s.detail_content, s.authorizer, s.price_tier1,
                           c.name as category_name, b.name as brand_name, b.id as brand_id
                           FROM script s
                           LEFT JOIN category c ON s.category_id = c.id
                           LEFT JOIN brand b ON s.brand_id = b.id
                           WHERE s.id = ? AND s.status = 'approved'");
        $stmt->execute([$scriptId]);
        $script = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$script) {
            echo json_encode(['code' => 404, 'msg' => 'Script not found']);
            exit;
        }

        $viewStmt = $pdo->prepare("UPDATE script SET view_count = view_count + 1 WHERE id = ?");
        $viewStmt->execute([$scriptId]);

        $galleryImages = decodeJsonArray($script['gallery_images'] ?? []);
        if ($script['thumbnail'] && !in_array($script['thumbnail'], $galleryImages, true)) {
            array_unshift($galleryImages, $script['thumbnail']);
        }

        $features = decodeJsonArray($script['feature_tags'] ?? []);
        $suitablePlayers = decodeJsonArray($script['suitable_players'] ?? []);
        $authServices = decodeJsonArray($script['auth_services'] ?? []);
        $authorizedCities = decodeJsonArray($script['authorized_cities'] ?? []);
        $authCities = decodeJsonArray($script['auth_cities'] ?? []);

        $cityPrices = [];
        foreach (($authorizedCities ?: $authCities) as $city) {
            $cityPrices[$city] = (float) ($script['price_tier1'] ?? 0);
        }

        // Build response
        $response = [
            'id' => $script['id'],
            'name' => $script['name'],
            'description' => $script['description'],
            'thumbnail' => $script['thumbnail'],
            'min_players' => $script['min_players'],
            'max_players' => $script['max_players'],
            'duration' => $script['duration'],
            'like_count' => $script['like_count'],
            'view_count' => $script['view_count'] + 1,
            'collect_count' => (int) ($script['collect_count'] ?? 0),
            'purchase_count' => (int) ($script['purchase_count'] ?? 0),
            'type' => $script['script_type'] ?: $script['category_name'],
            'horror_level' => (int) ($script['horror_level'] ?? 0),
            'difficulty' => $script['difficulty'] ?? '',
            'room_size' => $script['room_size'] ?? '',
            'price_tier1' => (float) ($script['price_tier1'] ?? 0),
            'category' => ['name' => $script['category_name']],
            'brand' => $script['brand_id'] ? ['id' => $script['brand_id'], 'name' => $script['brand_name']] : null,
            'video_url' => $script['video_url'] ?? '',
            'detail_content' => $script['detail_content'] ?? '',
            'images' => array_map(static fn($url) => ['url' => $url], $galleryImages),
            'theme_attrs' => [
                '剧本类型' => $script['script_type'] ?: '待补充',
                '恐怖等级' => (string) ((int) ($script['horror_level'] ?? 0)),
                '难度' => $script['difficulty'] ?: '待补充',
                '密室大小' => $script['room_size'] ?: '待补充',
                '特色标签' => $features ? implode(' / ', $features) : '待补充',
            ],
            'detail_attrs' => [
                '人数区间' => $script['min_players'] . '-' . $script['max_players'] . '人',
                '游戏时长' => $script['duration'] . '分钟',
                '面积' => ((int) ($script['area_size'] ?? 0)) ? ((int) $script['area_size']) . '㎡' : '待补充',
                '房间数量' => ($script['room_count'] ?? '') !== '' ? $script['room_count'] : '待补充',
                '滚场' => ($script['rotation_count'] ?? '') !== '' ? $script['rotation_count'] : '待补充',
                '走廊数量' => ($script['corridor_count'] ?? '') !== '' ? $script['corridor_count'] : '待补充',
                'NPC数量' => ($script['npc_count'] ?? '') !== '' ? $script['npc_count'] : '待补充',
                '适合玩家' => $suitablePlayers ? implode(' / ', $suitablePlayers) : '待补充',
            ],
            'auth_info' => [
                'status' => $script['auth_status'] ?? '',
                'services' => $authServices,
                'auth_cities' => $authCities,
                'authorized_cities' => $authorizedCities,
                'city_prices' => $cityPrices,
            ],
            'authorizer' => $script['authorizer'] ?: ($script['brand_name'] ?? '待补充'),
        ];
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => $response
        ]);
        exit;
    }

    // Script search API
    if (preg_match('/^scripts\/search(\?.*)?$/', $apiPath) && $method === 'GET') {
        $keyword = trim((string) ($_GET['keyword'] ?? ''));
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $where = "WHERE status = 'approved'";
        $params = [];
        if ($keyword !== '') {
            $where .= ' AND name LIKE ?';
            $params[] = '%' . $keyword . '%';
        }

        $stmt = $pdo->prepare("SELECT id, name, brand_id, category_id, cover_image as thumbnail, min_players, max_players, duration, like_count, view_count, collect_count, purchase_count, is_home_featured, home_featured_sort, is_script_featured, script_featured_sort, script_type, horror_level, difficulty, room_size, price_tier1, area_size, room_count, rotation_count, npc_count, corridor_count, auth_status, feature_tags, suitable_players, auth_services, authorized_cities, auth_cities FROM script $where ORDER BY is_script_featured DESC, script_featured_sort ASC, like_count DESC, view_count DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $scripts = array_map('buildScriptSummary', $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM script $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $scripts,
                'total' => $total
            ]
        ]);
        exit;
    }

    // Brands API
    if (preg_match('/^brands(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->prepare("SELECT id, name, logo, follower_count, total_authorizations, status, created_at FROM brand WHERE status = 'approved' ORDER BY follower_count DESC, total_authorizations DESC LIMIT $limit OFFSET $offset");
        $stmt->execute();
        $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM brand WHERE status = 'approved'");
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $brands,
                'total' => $total
            ]
        ]);
        exit;
    }

    // Brand detail API
    if (preg_match('/^brands\/(\d+)$/', $apiPath, $matches) && $method === 'GET') {
        $brandId = (int) $matches[1];

        $selectColumns = 'id, name, logo, follower_count, total_authorizations, status, created_at';
        if (hasColumn($pdo, 'brand', 'description')) {
            $selectColumns .= ', description';
        }

        $stmt = $pdo->prepare("SELECT $selectColumns FROM brand WHERE id = ?");
        $stmt->execute([$brandId]);
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$brand) {
            echo json_encode(['code' => 404, 'msg' => 'Brand not found']);
            exit;
        }

        $scriptStmt = $pdo->prepare("SELECT id, name, cover_image as thumbnail, min_players, max_players, duration, like_count, view_count, collect_count, purchase_count, script_type, horror_level, difficulty, room_size, price_tier1, area_size, room_count, rotation_count, npc_count, corridor_count, auth_status, feature_tags, suitable_players, auth_services, authorized_cities, auth_cities FROM script WHERE brand_id = ? AND status = 'approved' ORDER BY like_count DESC, view_count DESC LIMIT 10");
        $scriptStmt->execute([$brandId]);
        $scripts = array_map('buildScriptSummary', $scriptStmt->fetchAll(PDO::FETCH_ASSOC));

        $response = array_merge($brand, [
            'scripts' => $scripts,
        ]);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => $response
        ]);
        exit;
    }

    if (preg_match('/^constructors(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $permissionStmt = $pdo->query("
            SELECT cp.*
            FROM construction_permission cp
            INNER JOIN (
                SELECT user_id, MAX(id) AS max_id
                FROM construction_permission
                GROUP BY user_id
            ) latest ON latest.max_id = cp.id
            WHERE cp.status = 'approved'
            ORDER BY cp.created_at DESC
        ");
        $permissions = $permissionStmt ? $permissionStmt->fetchAll(PDO::FETCH_ASSOC) : [];

        $allCasesStmt = $pdo->query("SELECT * FROM construction_case WHERE status = 'approved' ORDER BY created_at DESC");
        $allCaseRows = $allCasesStmt ? $allCasesStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        $casesByUser = [];
        foreach ($allCaseRows as $row) {
            $casesByUser[(int) $row['user_id']][] = buildConstructionCaseItem($row);
        }

        $constructors = [];
        foreach ($permissions as $permission) {
            $userId = (int) ($permission['user_id'] ?? 0);
            $cases = $casesByUser[$userId] ?? [];
            if (($permission['company_name'] ?? '') === '' && !$cases) {
                continue;
            }
            $constructors[] = buildConstructorSummary($permission, $cases);
        }

        $total = count($constructors);
        $constructors = array_slice($constructors, $offset, $limit);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $constructors,
                'total' => $total,
            ],
        ]);
        exit;
    }

    if (preg_match('/^constructors\/(\d+)$/', $apiPath, $matches) && $method === 'GET') {
        $constructorId = (int) $matches[1];

        $permissionStmt = $pdo->prepare("
            SELECT *
            FROM construction_permission
            WHERE user_id = ?
              AND status = 'approved'
            ORDER BY id DESC
            LIMIT 1
        ");
        $permissionStmt->execute([$constructorId]);
        $permission = $permissionStmt->fetch(PDO::FETCH_ASSOC);

        if (!$permission) {
            echo json_encode(['code' => 404, 'msg' => 'Constructor not found']);
            exit;
        }

        $caseStmt = $pdo->prepare("SELECT * FROM construction_case WHERE user_id = ? AND status = 'approved' ORDER BY created_at DESC");
        $caseStmt->execute([$constructorId]);
        $cases = array_map('buildConstructionCaseItem', $caseStmt->fetchAll(PDO::FETCH_ASSOC));

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => buildConstructorSummary($permission, $cases),
        ]);
        exit;
    }

    if (preg_match('/^brands\/(\d+)\/follow$/', $apiPath, $matches) && $method === 'POST') {
        requireUser($pdo);
        $brandId = (int) $matches[1];

        if (hasTable($pdo, 'brand')) {
            $stmt = $pdo->prepare('UPDATE brand SET follower_count = follower_count + 1 WHERE id = ?');
            $stmt->execute([$brandId]);
        }

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['active' => true],
        ]);
        exit;
    }

    // Construction case permission API
    if ($apiPath === 'user/construction-case-permission' && $method === 'GET') {
        $user = requireUser($pdo);
        $stmt = $pdo->prepare('SELECT * FROM construction_permission WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
        $stmt->execute([(int) $user['id']]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentRole = $record && $record['status'] === 'approved' ? $record['role_type'] : 'member';

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'record' => $record,
                'current_role' => $currentRole
            ]
        ]);
        exit;
    }

    if ($apiPath === 'construction-case-permission' && $method === 'POST') {
        $user = requireUser($pdo);
        $data = jsonInput();

        $stmt = $pdo->prepare('INSERT INTO construction_permission (user_id, role_type, brand_name, company_name, contact_name, contact_phone, reason) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int) $user['id'],
            $data['role_type'] ?? 'member',
            $data['brand_name'] ?? '',
            $data['company_name'] ?? '',
            $data['contact_name'] ?? '',
            $data['contact_phone'] ?? '',
            $data['reason'] ?? '',
        ]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    // Construction case API
    if (preg_match('/^construction-cases(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $brandName = trim((string) ($_GET['brand_name'] ?? ''));
        $offset = max(0, ($page - 1) * $limit);

        $where = 'WHERE status = ?';
        $params = ['approved'];
        if ($brandName !== '') {
            $where .= ' AND brand_name LIKE ?';
            $params[] = '%' . $brandName . '%';
        }

        $stmt = $pdo->prepare("SELECT * FROM construction_case $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cases = array_map('buildConstructionCaseItem', $rows);

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM construction_case $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $brandStmt = $pdo->query("SELECT DISTINCT brand_name FROM construction_case WHERE status = 'approved' AND brand_name <> '' ORDER BY brand_name ASC");
        $brands = $brandStmt ? $brandStmt->fetchAll(PDO::FETCH_COLUMN) : [];

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $cases,
                'total' => $total,
                'brands' => $brands,
            ]
        ]);
        exit;
    }

    if ($apiPath === 'construction-cases' && $method === 'POST') {
        $user = requireUser($pdo);
        $data = jsonInput();
        $payload = normalizeConstructionCasePayload($data);

        $stmt = $pdo->prepare('INSERT INTO construction_case (user_id, brand_name, project_name, phase, cover, description, notes, images, videos, status, reject_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int) $user['id'],
            $payload['brand_name'],
            $payload['project_name'],
            $payload['phase'],
            $payload['cover'],
            $payload['description'],
            $payload['notes'],
            $payload['images'],
            $payload['videos'],
            'pending',
            '',
        ]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['id' => (int) $pdo->lastInsertId()],
        ]);
        exit;
    }

    if (preg_match('/^construction-cases\/(\d+)$/', $apiPath, $matches) && $method === 'GET') {
        $caseId = (int) $matches[1];
        $stmt = $pdo->prepare('SELECT * FROM construction_case WHERE id = ?');
        $stmt->execute([$caseId]);
        $case = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$case) {
            echo json_encode(['code' => 404, 'msg' => 'Case not found']);
            exit;
        }
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => buildConstructionCaseItem($case)
        ]);
        exit;
    }

    if (preg_match('/^construction-cases\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
        $caseId = (int) $matches[1];
        $user = requireUser($pdo);
        $data = jsonInput();
        $payload = normalizeConstructionCasePayload($data);

        $ownerStmt = $pdo->prepare('SELECT user_id FROM construction_case WHERE id = ? LIMIT 1');
        $ownerStmt->execute([$caseId]);
        $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);
        if (!$owner || (int) $owner['user_id'] !== (int) $user['id']) {
            http_response_code(403);
            echo json_encode(['code' => 403, 'msg' => 'You can only update your own construction cases']);
            exit;
        }

        $stmt = $pdo->prepare('UPDATE construction_case SET brand_name = ?, project_name = ?, phase = ?, cover = ?, description = ?, notes = ?, images = ?, videos = ?, status = ?, reject_reason = ? WHERE id = ?');
        $stmt->execute([
            $payload['brand_name'],
            $payload['project_name'],
            $payload['phase'],
            $payload['cover'],
            $payload['description'],
            $payload['notes'],
            $payload['images'],
            $payload['videos'],
            'pending',
            '',
            $caseId,
        ]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^construction-cases\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $user = requireUser($pdo);
        $caseId = (int) $matches[1];

        $stmt = $pdo->prepare('DELETE FROM construction_case WHERE id = ? AND user_id = ?');
        $stmt->execute([$caseId, (int) $user['id']]);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if ($apiPath === 'user/construction-cases' && $method === 'GET') {
        $user = requireUser($pdo);
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->prepare("SELECT * FROM construction_case WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute([(int) $user['id']]);
        $cases = array_map('buildConstructionCaseItem', $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countStmt = $pdo->prepare('SELECT COUNT(*) as total FROM construction_case WHERE user_id = ?');
        $countStmt->execute([(int) $user['id']]);
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $cases,
                'total' => $total
            ]
        ]);
        exit;
    }

    if ($apiPath === 'user/scripts' && $method === 'GET') {
        echo json_encode([
            'code' => 403,
            'msg' => 'Please use the brand portal to manage scripts',
            'data' => [
                'list' => [],
                'total' => 0,
            ]
        ]);
        exit;
    }

    if ($apiPath === 'admin/construction/profile' && $method === 'GET') {
        $admin = requireAdmin($pdo);
        requireConstructorAccount($admin);

        $stmt = $pdo->prepare('SELECT * FROM construction_permission WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([(int) $admin['constructor_user_id']]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'user_id' => (int) $admin['constructor_user_id'],
                'company_name' => $record['company_name'] ?? '',
                'brand_name' => $record['brand_name'] ?? '',
                'contact_name' => $record['contact_name'] ?? '',
                'contact_phone' => $record['contact_phone'] ?? '',
                'description' => $record['description'] ?? ($record['reason'] ?? ''),
                'status' => $record['status'] ?? 'pending',
                'review_note' => $record['review_note'] ?? '',
            ],
        ]);
        exit;
    }

    if ($apiPath === 'admin/construction/profile' && $method === 'PUT') {
        $admin = requireAdmin($pdo);
        requireConstructorAccount($admin);
        $payload = jsonInput();

        $stmt = $pdo->prepare('
            UPDATE construction_permission
            SET company_name = ?, brand_name = ?, contact_name = ?, contact_phone = ?, description = ?
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT 1
        ');
        $stmt->execute([
            trim((string) ($payload['company_name'] ?? '')),
            trim((string) ($payload['brand_name'] ?? '')),
            trim((string) ($payload['contact_name'] ?? '')),
            trim((string) ($payload['contact_phone'] ?? '')),
            trim((string) ($payload['description'] ?? '')),
            (int) $admin['constructor_user_id'],
        ]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
        ]);
        exit;
    }

    if (preg_match('/^admin\/construction\/cases(\?.*)?$/', $apiPath) && $method === 'GET') {
        $admin = requireAdmin($pdo);
        requireConstructorAccount($admin);

        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->prepare("SELECT * FROM construction_case WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute([(int) $admin['constructor_user_id']]);
        $cases = array_map('buildConstructionCaseItem', $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM construction_case WHERE user_id = ?');
        $countStmt->execute([(int) $admin['constructor_user_id']]);
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $cases,
                'total' => $total,
            ],
        ]);
        exit;
    }

    if ($apiPath === 'admin/construction/cases' && $method === 'POST') {
        $admin = requireAdmin($pdo);
        requireConstructorAccount($admin);
        $payload = normalizeConstructionCasePayload(jsonInput());

        $stmt = $pdo->prepare('INSERT INTO construction_case (user_id, brand_name, project_name, phase, cover, description, notes, images, videos, status, reject_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int) $admin['constructor_user_id'],
            $payload['brand_name'],
            $payload['project_name'],
            $payload['phase'],
            $payload['cover'],
            $payload['description'],
            $payload['notes'],
            $payload['images'],
            $payload['videos'],
            'pending',
            '',
        ]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['id' => (int) $pdo->lastInsertId()],
        ]);
        exit;
    }

    if (preg_match('/^admin\/construction\/cases\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
        $admin = requireAdmin($pdo);
        requireConstructorAccount($admin);
        $caseId = (int) $matches[1];
        $payload = normalizeConstructionCasePayload(jsonInput());

        $checkStmt = $pdo->prepare('SELECT user_id FROM construction_case WHERE id = ? LIMIT 1');
        $checkStmt->execute([$caseId]);
        $owner = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$owner || (int) $owner['user_id'] !== (int) $admin['constructor_user_id']) {
            http_response_code(403);
            echo json_encode(['code' => 403, 'msg' => 'You can only update your own construction cases']);
            exit;
        }

        $stmt = $pdo->prepare('UPDATE construction_case SET brand_name = ?, project_name = ?, phase = ?, cover = ?, description = ?, notes = ?, images = ?, videos = ?, status = ?, reject_reason = ? WHERE id = ?');
        $stmt->execute([
            $payload['brand_name'],
            $payload['project_name'],
            $payload['phase'],
            $payload['cover'],
            $payload['description'],
            $payload['notes'],
            $payload['images'],
            $payload['videos'],
            'pending',
            '',
            $caseId,
        ]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
        ]);
        exit;
    }

    if (preg_match('/^admin\/construction\/cases\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $admin = requireAdmin($pdo);
        requireConstructorAccount($admin);
        $caseId = (int) $matches[1];

        $stmt = $pdo->prepare('DELETE FROM construction_case WHERE id = ? AND user_id = ?');
        $stmt->execute([$caseId, (int) $admin['constructor_user_id']]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
        ]);
        exit;
    }

    if ($apiPath === 'user/listings' && $method === 'GET') {
        requireUser($pdo);

        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $columns = ['id', 'title', 'type', 'price', 'status', 'is_featured', 'created_at'];
        if (hasColumn($pdo, 'market_listing', 'description')) {
            $columns[] = 'description';
        }

        $stmt = $pdo->prepare(sprintf(
            'SELECT %s FROM market_listing ORDER BY created_at DESC LIMIT %d OFFSET %d',
            implode(', ', $columns),
            $limit,
            $offset
        ));
        $stmt->execute();
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->query('SELECT COUNT(*) as total FROM market_listing');
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $list,
                'total' => $total,
            ],
        ]);
        exit;
    }

    if ($apiPath === 'user/interests' && $method === 'GET') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => []],
        ]);
        exit;
    }

    if ($apiPath === 'user/recent-views' && $method === 'GET') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => []],
        ]);
        exit;
    }

    if ($apiPath === 'user/views' && $method === 'POST') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => null,
        ]);
        exit;
    }

    if ($apiPath === 'scripts' && $method === 'POST') {
        requireUser($pdo);

        http_response_code(403);
        echo json_encode([
            'code' => 403,
            'msg' => 'Regular users cannot publish scripts. Please use the brand portal.',
        ]);
        exit;
    }

    if (preg_match('/^scripts\/(\d+)\/like$/', $apiPath, $matches) && $method === 'POST') {
        requireUser($pdo);
        $scriptId = (int) $matches[1];

        $stmt = $pdo->prepare('UPDATE script SET like_count = like_count + 1 WHERE id = ?');
        $stmt->execute([$scriptId]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['active' => true],
        ]);
        exit;
    }

    if (preg_match('/^scripts\/(\d+)\/collect$/', $apiPath, $matches) && $method === 'POST') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['active' => true],
        ]);
        exit;
    }

    if (preg_match('/^scripts\/(\d+)\/purchase-intent$/', $apiPath, $matches) && $method === 'POST') {
        $scriptId = (int) $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $city = trim((string) ($data['city'] ?? ''));
        $contactName = trim((string) ($data['contact_name'] ?? ''));
        $contactPhone = trim((string) ($data['contact_phone'] ?? ''));

        if ($city === '' || $contactName === '' || $contactPhone === '') {
            echo json_encode(['code' => 400, 'msg' => '请填写完整购买信息']);
            exit;
        }

        $scriptStmt = $pdo->prepare('SELECT id, brand_id FROM script WHERE id = ? LIMIT 1');
        $scriptStmt->execute([$scriptId]);
        $script = $scriptStmt->fetch(PDO::FETCH_ASSOC);
        if (!$script) {
            echo json_encode(['code' => 404, 'msg' => 'Script not found']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO script_purchase_intent (script_id, brand_id, city, contact_name, contact_phone) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$scriptId, (int) ($script['brand_id'] ?? 0), $city, $contactName, $contactPhone]);

        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    // Admin construction permission management API
    if (preg_match('/^admin\/construction-permissions(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->query("
            SELECT cp.*, a.username AS account_username
            FROM construction_permission cp
            LEFT JOIN admin a ON a.constructor_user_id = cp.user_id AND a.role = 'constructor'
            ORDER BY cp.created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM construction_permission");
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $permissions,
                'total' => $total
            ]
        ]);
        exit;
    }

    if (preg_match('/^admin\/construction-permissions\/(\d+)\/approve$/', $apiPath, $matches) && $method === 'PUT') {
        $id = (int) $matches[1];

        $stmt = $pdo->prepare('SELECT * FROM construction_permission WHERE id = ?');
        $stmt->execute([$id]);
        $permission = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$permission) {
            echo json_encode(['code' => 404, 'msg' => 'Permission not found']);
            exit;
        }

        if ($permission['role_type'] === 'brand') {
            $brandName = $permission['brand_name'];

            $brandStmt = $pdo->prepare('SELECT id FROM brand WHERE name = ?');
            $brandStmt->execute([$brandName]);
            $existingBrand = $brandStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingBrand) {
                $insertBrandStmt = $pdo->prepare('INSERT INTO brand (name, status) VALUES (?, ?)');
                $insertBrandStmt->execute([$brandName, 'approved']);
            }
        }

        $stmt = $pdo->prepare('UPDATE construction_permission SET status = "approved" WHERE id = ?');
        $stmt->execute([$id]);

        if (($permission['role_type'] ?? '') === 'constructor' && !empty($permission['user_id'])) {
            createOrUpdateConstructorAdmin($pdo, (int) $permission['user_id'], $permission);
        }

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^admin\/construction-permissions\/(\d+)\/reject$/', $apiPath, $matches) && $method === 'PUT') {
        $id = (int) $matches[1];
        $data = jsonInput();

        $stmt = $pdo->prepare('UPDATE construction_permission SET status = "rejected", review_note = ? WHERE id = ?');
        $stmt->execute([$data['review_note'] ?? '', $id]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    // Admin construction case management API
    if (preg_match('/^admin\/construction-cases(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->query("SELECT * FROM construction_case ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $cases = array_map('buildConstructionCaseItem', $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM construction_case");
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $cases,
                'total' => $total
            ]
        ]);
        exit;
    }

    if (preg_match('/^admin\/construction-cases\/(\d+)\/approve$/', $apiPath, $matches) && $method === 'PUT') {
        $id = (int) $matches[1];
        $stmt = $pdo->prepare('UPDATE construction_case SET status = "approved" WHERE id = ?');
        $stmt->execute([$id]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^admin\/construction-cases\/(\d+)\/reject$/', $apiPath, $matches) && $method === 'PUT') {
        $id = (int) $matches[1];
        $data = jsonInput();
        $stmt = $pdo->prepare('UPDATE construction_case SET status = "rejected", reject_reason = ? WHERE id = ?');
        $stmt->execute([$data['review_note'] ?? '审核未通过', $id]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^admin\/construction-cases\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $id = (int) $matches[1];
        $stmt = $pdo->prepare('DELETE FROM construction_case WHERE id = ?');
        $stmt->execute([$id]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    // Community API
    if (preg_match('/^community\/posts(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->prepare("SELECT * FROM community_post WHERE status = 'approved' ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM community_post WHERE status = 'approved'");
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $posts,
                'total' => $total
            ]
        ]);
        exit;
    }

    if (preg_match('/^community\/posts\/(\d+)$/', $apiPath, $matches) && $method === 'GET') {
        $postId = (int) $matches[1];

        $viewStmt = $pdo->prepare("UPDATE community_post SET view_count = view_count + 1 WHERE id = ?");
        $viewStmt->execute([$postId]);

        $stmt = $pdo->prepare("SELECT * FROM community_post WHERE id = ? AND status = 'approved'");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            echo json_encode(['code' => 404, 'msg' => 'Post not found']);
            exit;
        }

        $commentStmt = $pdo->prepare("SELECT * FROM community_comment WHERE post_id = ? AND status = 'approved' ORDER BY created_at ASC");
        $commentStmt->execute([$postId]);
        $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            ...$post,
            'comments' => $comments
        ];
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => $response
        ]);
        exit;
    }

    if ($apiPath === 'community/posts' && $method === 'POST') {
        $user = requireUser($pdo);
        $data = jsonInput();

        $stmt = $pdo->prepare('INSERT INTO community_post (user_id, title, content, images) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            (int) $user['id'],
            $data['title'] ?? '',
            $data['content'] ?? '',
            json_encode($data['images'] ?? []),
        ]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^community\/posts\/(\d+)\/comments$/', $apiPath, $matches) && $method === 'POST') {
        $postId = (int) $matches[1];
        $user = requireUser($pdo);
        $data = jsonInput();

        $stmt = $pdo->prepare('INSERT INTO community_comment (post_id, user_id, content) VALUES (?, ?, ?)');
        $stmt->execute([$postId, (int) $user['id'], $data['content'] ?? '']);

        $commentCountStmt = $pdo->prepare("UPDATE community_post SET comment_count = comment_count + 1 WHERE id = ?");
        $commentCountStmt->execute([$postId]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^community\/posts\/(\d+)\/like$/', $apiPath, $matches) && $method === 'POST') {
        $postId = (int) $matches[1];
        $likeStmt = $pdo->prepare("UPDATE community_post SET like_count = like_count + 1 WHERE id = ?");
        $likeStmt->execute([$postId]);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if ($apiPath === 'user/community/posts' && $method === 'GET') {
        $user = requireUser($pdo);
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = max(0, ($page - 1) * $limit);

        $stmt = $pdo->prepare("SELECT * FROM community_post WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute([(int) $user['id']]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->prepare('SELECT COUNT(*) as total FROM community_post WHERE user_id = ?');
        $countStmt->execute([(int) $user['id']]);
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $posts,
                'total' => $total
            ]
        ]);
        exit;
    }

    // Admin community management API
    if (preg_match('/^admin\/community\/posts(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $offset = ($page - 1) * $limit;
        
        $stmt = $pdo->query("SELECT * FROM community_post ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM community_post");
        $total = $countStmt->fetchColumn();
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $posts,
                'total' => $total
            ]
        ]);
        exit;
    }

    if (preg_match('/^admin\/community\/posts\/(\d+)\/approve$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('UPDATE community_post SET status = "approved" WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^admin\/community\/posts\/(\d+)\/reject$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('UPDATE community_post SET status = "rejected" WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^admin\/community\/posts\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('DELETE FROM community_post WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^admin\/community\/comments(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $offset = ($page - 1) * $limit;
        
        $stmt = $pdo->query("SELECT * FROM community_comment ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM community_comment");
        $total = $countStmt->fetchColumn();
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'list' => $comments,
                'total' => $total
            ]
        ]);
        exit;
    }

    if (preg_match('/^admin\/community\/comments\/(\d+)\/approve$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('UPDATE community_comment SET status = "approved" WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^admin\/community\/comments\/(\d+)\/reject$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('UPDATE community_comment SET status = "rejected" WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    if (preg_match('/^admin\/community\/comments\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('DELETE FROM community_comment WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success'
        ]);
        exit;
    }

    // Admin login
    if ($apiPath === 'admin/login' && $method === 'POST') {
        $data = jsonInput();
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $stmt = $pdo->prepare('
            SELECT a.id, a.username, a.password, a.role, a.brand_id, a.constructor_user_id, b.name AS brand_name,
                   cp.company_name, cp.description
            FROM admin a
            LEFT JOIN brand b ON b.id = a.brand_id
            LEFT JOIN (
                SELECT cp1.*
                FROM construction_permission cp1
                INNER JOIN (
                    SELECT user_id, MAX(id) AS max_id
                    FROM construction_permission
                    GROUP BY user_id
                ) latest_cp ON latest_cp.max_id = cp1.id
            ) cp ON cp.user_id = a.constructor_user_id
            WHERE a.username = ? LIMIT 1
        ');
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($password, $admin['password'])) {
            echo json_encode(['code' => 401, 'msg' => 'Invalid username or password']);
            exit;
        }

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'token' => 'admin_' . $admin['id'],
                'admin' => [
                    'id' => (int) $admin['id'],
                    'username' => $admin['username'],
                    'role' => $admin['role'],
                    'brand_id' => isset($admin['brand_id']) ? (int) $admin['brand_id'] : null,
                    'brand_name' => $admin['brand_name'] ?? '',
                    'constructor_user_id' => isset($admin['constructor_user_id']) ? (int) $admin['constructor_user_id'] : null,
                    'company_name' => $admin['company_name'] ?? '',
                    'description' => $admin['description'] ?? '',
                ]
            ]
        ]);
        exit;
    }

    if ($apiPath === 'admin/logout' && $method === 'POST') {
        echo json_encode(['code' => 200, 'msg' => 'Success', 'data' => null]);
        exit;
    }

    if ($apiPath === 'admin/profile' && $method === 'GET') {
        $admin = requireAdmin($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'id' => (int) $admin['id'],
                'username' => $admin['username'],
                'role' => $admin['role'],
                'brand_id' => isset($admin['brand_id']) ? (int) $admin['brand_id'] : null,
                'brand_name' => $admin['brand_name'] ?? '',
                'constructor_user_id' => isset($admin['constructor_user_id']) ? (int) $admin['constructor_user_id'] : null,
                'company_name' => $admin['constructor_company_name'] ?? '',
                'description' => $admin['constructor_description'] ?? '',
            ],
        ]);
        exit;
    }

    // Admin stats
    if ($apiPath === 'admin/stats/overview' && $method === 'GET') {
        $admin = requireAdmin($pdo);

        if (($admin['role'] ?? '') === 'brand' && !empty($admin['brand_id'])) {
            $brandId = (int) $admin['brand_id'];

            $scriptCountStmt = $pdo->prepare('SELECT COUNT(*) FROM script WHERE brand_id = ?');
            $scriptCountStmt->execute([$brandId]);
            $totalScripts = (int) $scriptCountStmt->fetchColumn();

            $pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM script WHERE brand_id = ? AND status = 'pending'");
            $pendingStmt->execute([$brandId]);
            $pendingScripts = (int) $pendingStmt->fetchColumn();

            echo json_encode([
                'code' => 200,
                'msg' => 'Success',
                'data' => [
                    'scripts' => $totalScripts,
                    'pending_scripts' => $pendingScripts,
                    'brands' => 1,
                    'users' => 0,
                    'market_listings' => 0,
                    'brand_name' => $admin['brand_name'] ?? '',
                ]
            ]);
            exit;
        }

        if (($admin['role'] ?? '') === 'constructor' && !empty($admin['constructor_user_id'])) {
            $constructorUserId = (int) $admin['constructor_user_id'];

            $caseCountStmt = $pdo->prepare('SELECT COUNT(*) FROM construction_case WHERE user_id = ?');
            $caseCountStmt->execute([$constructorUserId]);
            $totalCases = (int) $caseCountStmt->fetchColumn();

            $pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM construction_case WHERE user_id = ? AND status = 'pending'");
            $pendingStmt->execute([$constructorUserId]);
            $pendingCases = (int) $pendingStmt->fetchColumn();

            $approvedStmt = $pdo->prepare("SELECT COUNT(*) FROM construction_case WHERE user_id = ? AND status = 'approved'");
            $approvedStmt->execute([$constructorUserId]);
            $approvedCases = (int) $approvedStmt->fetchColumn();

            echo json_encode([
                'code' => 200,
                'msg' => 'Success',
                'data' => [
                    'construction_cases' => $totalCases,
                    'pending_cases' => $pendingCases,
                    'approved_cases' => $approvedCases,
                    'company_name' => $admin['constructor_company_name'] ?? '',
                    'brand_name' => $admin['brand_name'] ?? '',
                ]
            ]);
            exit;
        }

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'total_scripts' => 120,
                'total_brands' => 35,
                'total_users' => 500,
                'total_market_listings' => 85,
                'pending_scripts' => 5,
                'pending_brands' => 2,
                'pending_market_listings' => 8
            ]
        ]);
        exit;
    }

    // Admin categories
    if ($apiPath === 'admin/categories' && $method === 'GET') {
        $stmt = $pdo->query('SELECT id, name, sort_order, created_at FROM category ORDER BY sort_order ASC');
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => $categories
        ]);
        exit;
    }

    if (preg_match('/^admin\/categories\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare('UPDATE category SET name = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['sort_order'], $id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if ($apiPath === 'admin/categories' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare('INSERT INTO category (name, sort_order) VALUES (?, ?)');
        $stmt->execute([$data['name'], $data['sort_order']]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/categories\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('DELETE FROM category WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    // Admin brands
    if (preg_match('/^admin\/brands(\?.*)?$/', $apiPath) && $method === 'GET') {
        $admin = requireAdmin($pdo);
        requireSuperAdmin($admin);

        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $status = trim((string) ($_GET['status'] ?? ''));
        $completeness = trim((string) ($_GET['completeness'] ?? ''));

        $offset = max(0, ($page - 1) * $limit);
        $where = '';
        $params = [];
        if ($status !== '') {
            $where = 'WHERE status = ?';
            $params[] = $status;
        }

        $stmt = $pdo->prepare("
            SELECT b.id, b.name, b.logo, b.description, b.follower_count, b.total_authorizations, b.status, b.created_at,
                   a.username AS account_username
            FROM brand b
            LEFT JOIN admin a ON a.brand_id = b.id AND a.role = 'brand'
            $where
            ORDER BY b.id DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM brand $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => $brands, 'total' => $total]
        ]);
        exit;
    }

    if (preg_match('/^admin\/brands\/(\d+)$/', $apiPath, $matches) && $method === 'GET') {
        $admin = requireAdmin($pdo);
        $id = $matches[1];

        if (($admin['role'] ?? '') === 'brand' && (int) ($admin['brand_id'] ?? 0) !== (int) $id) {
            http_response_code(403);
            echo json_encode(['code' => 403, 'msg' => 'You can only view your own brand']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT b.*, a.username AS account_username
            FROM brand b
            LEFT JOIN admin a ON a.brand_id = b.id AND a.role = 'brand'
            WHERE b.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => $brand
        ]);
        exit;
    }

    if (preg_match('/^admin\/brands\/(\d+)\/audit$/', $apiPath, $matches) && $method === 'PUT') {
        $admin = requireAdmin($pdo);
        requireSuperAdmin($admin);
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'];
        
        $stmt = $pdo->prepare('UPDATE brand SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if ($apiPath === 'admin/brands' && $method === 'POST') {
        $admin = requireAdmin($pdo);
        requireSuperAdmin($admin);
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $pdo->prepare('INSERT INTO brand (name, logo, description, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['logo'] ?? '',
            $data['description'] ?? '',
            $data['status'] ?? 'pending'
        ]);

        $brandId = (int) $pdo->lastInsertId();
        createOrUpdateBrandAdmin($pdo, $brandId, $data);

        echo json_encode(['code' => 200, 'msg' => 'Success', 'data' => ['id' => $brandId]]);
        exit;
    }

    if (preg_match('/^admin\/brands\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
        $admin = requireAdmin($pdo);
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);

        if (($admin['role'] ?? '') === 'brand' && (int) ($admin['brand_id'] ?? 0) !== (int) $id) {
            http_response_code(403);
            echo json_encode(['code' => 403, 'msg' => 'You can only update your own brand']);
            exit;
        }

        $nextStatus = ($admin['role'] ?? '') === 'brand'
            ? 'approved'
            : ($data['status'] ?? 'pending');

        $stmt = $pdo->prepare('UPDATE brand SET name = ?, logo = ?, description = ?, status = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['logo'] ?? '', $data['description'] ?? '', $nextStatus, $id]);

        if (($admin['role'] ?? '') !== 'brand') {
            createOrUpdateBrandAdmin($pdo, (int) $id, $data);
        }
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/brands\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $admin = requireAdmin($pdo);
        requireSuperAdmin($admin);
        $id = $matches[1];
        
        $accountStmt = $pdo->prepare("DELETE FROM admin WHERE brand_id = ? AND role = 'brand'");
        $accountStmt->execute([$id]);

        $stmt = $pdo->prepare('DELETE FROM brand WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    // Admin scripts
    if (preg_match('/^admin\/scripts(\?.*)?$/', $apiPath) && $method === 'GET') {
        $admin = requireAdmin($pdo);
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $status = trim((string) ($_GET['status'] ?? ''));
        $completeness = trim((string) ($_GET['completeness'] ?? ''));

        $offset = max(0, ($page - 1) * $limit);
        $where = '';
        $params = [];

        if (($admin['role'] ?? '') === 'brand') {
            requireBrandAccount($admin);
            $where = 'WHERE brand_id = ?';
            $params[] = (int) $admin['brand_id'];
        }

        if ($status !== '') {
            $where .= $where ? ' AND status = ?' : 'WHERE status = ?';
            $params[] = $status;
        }

        if ($completeness === 'incomplete') {
            $where .= $where ? ' AND ' : 'WHERE ';
            $where .= "(TRIM(COALESCE(video_url, '')) = '' OR TRIM(COALESCE(detail_content, '')) = '' OR COALESCE(price_tier1, 0) <= 0 OR TRIM(COALESCE(gallery_images, '')) = '' OR TRIM(COALESCE(gallery_images, '')) = '[]')";
        } elseif ($completeness === 'complete') {
            $where .= $where ? ' AND ' : 'WHERE ';
            $where .= "(TRIM(COALESCE(video_url, '')) <> '' AND TRIM(COALESCE(detail_content, '')) <> '' AND COALESCE(price_tier1, 0) > 0 AND TRIM(COALESCE(gallery_images, '')) <> '' AND TRIM(COALESCE(gallery_images, '')) <> '[]')";
        }

        $stmt = $pdo->prepare("SELECT id, name, brand_id, category_id, min_players, max_players, duration, status, view_count, like_count, collect_count, purchase_count, is_home_featured, home_featured_sort, is_script_featured, script_featured_sort, cover_image, description, created_at, script_type, horror_level, difficulty, room_size, feature_tags, area_size, room_count, rotation_count, npc_count, corridor_count, suitable_players, auth_status, auth_services, authorized_cities, auth_cities, gallery_images, video_url, detail_content, authorizer, price_tier1 FROM script $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $scripts = array_map(static function (array $row): array {
            $row['horror_level'] = (int) ($row['horror_level'] ?? 0);
            $row['area_size'] = (int) ($row['area_size'] ?? 0);
            $row['price_tier1'] = (float) ($row['price_tier1'] ?? 0);
            $row['is_home_featured'] = (int) ($row['is_home_featured'] ?? 0);
            $row['home_featured_sort'] = (int) ($row['home_featured_sort'] ?? 0);
            $row['is_script_featured'] = (int) ($row['is_script_featured'] ?? 0);
            $row['script_featured_sort'] = (int) ($row['script_featured_sort'] ?? 0);
            $row['feature_tags'] = decodeJsonArray($row['feature_tags'] ?? []);
            $row['suitable_players'] = decodeJsonArray($row['suitable_players'] ?? []);
            $row['auth_services'] = decodeJsonArray($row['auth_services'] ?? []);
            $row['authorized_cities'] = decodeJsonArray($row['authorized_cities'] ?? []);
            $row['auth_cities'] = decodeJsonArray($row['auth_cities'] ?? []);
            $row['gallery_images'] = decodeJsonArray($row['gallery_images'] ?? []);
            return $row;
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM script $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => $scripts, 'total' => $total]
        ]);
        exit;
    }

    if ($apiPath === 'admin/scripts' && $method === 'POST') {
        $admin = requireAdmin($pdo);
        $data = json_decode(file_get_contents('php://input'), true);

        $payload = normalizeScriptPayload(is_array($data) ? $data : []);
        $brandId = (int) ($payload['brand_id'] ?? 0);
        $status = $payload['status'] ?? 'draft';

        if (($admin['role'] ?? '') === 'brand') {
            requireBrandAccount($admin);
            $brandId = (int) $admin['brand_id'];
            $status = 'pending';
        }

        $stmt = $pdo->prepare('INSERT INTO script (name, brand_id, category_id, min_players, max_players, duration, status, view_count, like_count, cover_image, description, script_type, horror_level, difficulty, room_size, feature_tags, area_size, room_count, rotation_count, npc_count, corridor_count, suitable_players, auth_status, auth_services, authorized_cities, auth_cities, gallery_images, video_url, detail_content, authorizer, price_tier1, collect_count, purchase_count, is_home_featured, home_featured_sort, is_script_featured, script_featured_sort) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $payload['name'],
            $brandId,
            $payload['category_id'],
            $payload['min_players'],
            $payload['max_players'],
            $payload['duration'],
            $status,
            $payload['view_count'],
            $payload['like_count'],
            $payload['cover_image'],
            $payload['description'],
            $payload['script_type'],
            $payload['horror_level'],
            $payload['difficulty'],
            $payload['room_size'],
            $payload['feature_tags'],
            $payload['area_size'],
            $payload['room_count'],
            $payload['rotation_count'],
            $payload['npc_count'],
            $payload['corridor_count'],
            $payload['suitable_players'],
            $payload['auth_status'],
            $payload['auth_services'],
            $payload['authorized_cities'],
            $payload['auth_cities'],
            $payload['gallery_images'],
            $payload['video_url'],
            $payload['detail_content'],
            $payload['authorizer'],
            $payload['price_tier1'],
            $payload['collect_count'],
            $payload['purchase_count'],
            $payload['is_home_featured'],
            $payload['home_featured_sort'],
            $payload['is_script_featured'],
            $payload['script_featured_sort'],
        ]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/scripts\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
        $admin = requireAdmin($pdo);
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);

        if (($admin['role'] ?? '') === 'brand') {
            requireBrandAccount($admin);
            $checkStmt = $pdo->prepare('SELECT brand_id FROM script WHERE id = ? LIMIT 1');
            $checkStmt->execute([$id]);
            $script = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$script || (int) $script['brand_id'] !== (int) $admin['brand_id']) {
                http_response_code(403);
                echo json_encode(['code' => 403, 'msg' => 'You can only update your own brand scripts']);
                exit;
            }
        }

        $payload = normalizeScriptPayload(is_array($data) ? $data : []);
        $brandId = ($admin['role'] ?? '') === 'brand'
            ? (int) $admin['brand_id']
            : (int) ($payload['brand_id'] ?? 0);
        $status = ($admin['role'] ?? '') === 'brand'
            ? 'pending'
            : ($payload['status'] ?? 'draft');
        
        $stmt = $pdo->prepare('UPDATE script SET name = ?, brand_id = ?, category_id = ?, min_players = ?, max_players = ?, duration = ?, status = ?, view_count = ?, like_count = ?, cover_image = ?, description = ?, script_type = ?, horror_level = ?, difficulty = ?, room_size = ?, feature_tags = ?, area_size = ?, room_count = ?, rotation_count = ?, npc_count = ?, corridor_count = ?, suitable_players = ?, auth_status = ?, auth_services = ?, authorized_cities = ?, auth_cities = ?, gallery_images = ?, video_url = ?, detail_content = ?, authorizer = ?, price_tier1 = ?, collect_count = ?, purchase_count = ?, is_home_featured = ?, home_featured_sort = ?, is_script_featured = ?, script_featured_sort = ? WHERE id = ?');
        $stmt->execute([
            $payload['name'],
            $brandId,
            $payload['category_id'],
            $payload['min_players'],
            $payload['max_players'],
            $payload['duration'],
            $status,
            $payload['view_count'],
            $payload['like_count'],
            $payload['cover_image'],
            $payload['description'],
            $payload['script_type'],
            $payload['horror_level'],
            $payload['difficulty'],
            $payload['room_size'],
            $payload['feature_tags'],
            $payload['area_size'],
            $payload['room_count'],
            $payload['rotation_count'],
            $payload['npc_count'],
            $payload['corridor_count'],
            $payload['suitable_players'],
            $payload['auth_status'],
            $payload['auth_services'],
            $payload['authorized_cities'],
            $payload['auth_cities'],
            $payload['gallery_images'],
            $payload['video_url'],
            $payload['detail_content'],
            $payload['authorizer'],
            $payload['price_tier1'],
            $payload['collect_count'],
            $payload['purchase_count'],
            $payload['is_home_featured'],
            $payload['home_featured_sort'],
            $payload['is_script_featured'],
            $payload['script_featured_sort'],
            $id,
        ]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/scripts\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $admin = requireAdmin($pdo);
        $id = $matches[1];

        if (($admin['role'] ?? '') === 'brand') {
            requireBrandAccount($admin);
            $checkStmt = $pdo->prepare('SELECT brand_id FROM script WHERE id = ? LIMIT 1');
            $checkStmt->execute([$id]);
            $script = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$script || (int) $script['brand_id'] !== (int) $admin['brand_id']) {
                http_response_code(403);
                echo json_encode(['code' => 403, 'msg' => 'You can only delete your own brand scripts']);
                exit;
            }
        }
        
        $stmt = $pdo->prepare('DELETE FROM script WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/scripts\/(\d+)\/audit$/', $apiPath, $matches) && $method === 'PUT') {
        $admin = requireAdmin($pdo);
        requireSuperAdmin($admin);
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'];
        
        $stmt = $pdo->prepare('UPDATE script SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    // Admin market listings
    if (preg_match('/^admin\/market\/listings(\?.*)?$/', $apiPath) && $method === 'GET') {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $status = trim((string) ($_GET['status'] ?? ''));

        $offset = max(0, ($page - 1) * $limit);
        $where = '';
        $params = [];
        if ($status !== '') {
            $where = 'WHERE status = ?';
            $params[] = $status;
        }

        $stmt = $pdo->prepare("SELECT id, title, type, price, status, is_featured, created_at FROM market_listing $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM market_listing $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => $listings, 'total' => $total]
        ]);
        exit;
    }

    if (preg_match('/^admin\/market\/listings\/(\d+)\/audit$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'];
        
        $stmt = $pdo->prepare('UPDATE market_listing SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/market\/listings\/(\d+)\/featured$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        $featured = $data['featured'];
        
        $stmt = $pdo->prepare('UPDATE market_listing SET is_featured = ? WHERE id = ?');
        $stmt->execute([$featured, $id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/market\/listings\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('DELETE FROM market_listing WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    // Market API
    if (preg_match('/^market(\?.*)?$/', $apiPath) && $method === 'GET') {
        $type = trim((string) ($_GET['type'] ?? ''));
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $sort = trim((string) ($_GET['sort'] ?? 'latest'));
        $offset = max(0, ($page - 1) * $limit);

        $where = '';
        $params = [];
        if ($type !== '') {
            $where = 'WHERE type = ?';
            $params[] = $type;
        }
        $order = $sort === 'hot' ? 'ORDER BY like_count DESC, created_at DESC' : 
                 ($sort === 'featured' ? 'ORDER BY is_featured DESC, created_at DESC' : 
                 'ORDER BY created_at DESC');

        $stmt = $pdo->prepare("SELECT id, title, type, price, status, is_featured, created_at FROM market_listing $where $order LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM market_listing $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $featuredStmt = $pdo->query('SELECT id, title, status, created_at FROM market_listing WHERE is_featured = 1 AND status = "approved" ORDER BY created_at DESC LIMIT 5');
        $featured = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => [
                'listings' => $listings,
                'featured' => $featured,
                'total' => $total
            ]
        ]);
        exit;
    }

    if ($apiPath === 'market/listings' && $method === 'POST') {
        $user = requireUser($pdo);
        $data = jsonInput();

        $columns = ['title', 'type', 'price', 'status'];
        $values = [
            $data['title'] ?? '',
            $data['type'] ?? 'sell',
            $data['price'] ?? 0,
            'pending',
        ];

        if (hasColumn($pdo, 'market_listing', 'description')) {
            $columns[] = 'description';
            $values[] = $data['description'] ?? '';
        }
        if (hasColumn($pdo, 'market_listing', 'user_id')) {
            $columns[] = 'user_id';
            $values[] = $user['id'];
        }

        $stmt = $pdo->prepare(sprintf(
            'INSERT INTO market_listing (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        ));
        $stmt->execute($values);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['id' => (int) $pdo->lastInsertId()],
        ]);
        exit;
    }

    if (preg_match('/^market\/(\d+)$/', $apiPath, $matches) && $method === 'GET') {
        $listingId = (int) $matches[1];

        $columns = ['id', 'title', 'type', 'price', 'status', 'is_featured', 'created_at'];
        if (hasColumn($pdo, 'market_listing', 'description')) {
            $columns[] = 'description';
        }
        if (hasColumn($pdo, 'market_listing', 'user_id')) {
            $columns[] = 'user_id';
        }

        $stmt = $pdo->prepare(sprintf('SELECT %s FROM market_listing WHERE id = ? LIMIT 1', implode(', ', $columns)));
        $stmt->execute([$listingId]);
        $detail = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$detail) {
            echo json_encode(['code' => 404, 'msg' => 'Listing not found']);
            exit;
        }

        if (!isset($detail['description'])) {
            $detail['description'] = '';
        }
        $detail['user_nickname'] = '匿名用户';

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => $detail,
        ]);
        exit;
    }

    if (preg_match('/^market\/listings\/(\d+)\/interest$/', $apiPath, $matches) && $method === 'POST') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['active' => true],
        ]);
        exit;
    }

    if (preg_match('/^market\/listings\/(\d+)\/like$/', $apiPath, $matches) && $method === 'POST') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['active' => true],
        ]);
        exit;
    }

    if (preg_match('/^market\/listings\/(\d+)\/comments$/', $apiPath, $matches) && $method === 'GET') {
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => []],
        ]);
        exit;
    }

    if (preg_match('/^market\/listings\/(\d+)\/comments$/', $apiPath, $matches) && $method === 'POST') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['id' => 0],
        ]);
        exit;
    }

    if (preg_match('/^market\/comments\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        requireUser($pdo);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => null,
        ]);
        exit;
    }

    // Admin home content
    if ($apiPath === 'admin/home/banners' && $method === 'GET') {
        $stmt = $pdo->query('SELECT id, image, link, sort_order FROM home_banner ORDER BY sort_order ASC');
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => $banners]
        ]);
        exit;
    }

    if ($apiPath === 'admin/home/banners' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare('INSERT INTO home_banner (image, link, sort_order) VALUES (?, ?, ?)');
        $stmt->execute([$data['image'], $data['link'], $data['sort_order']]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/home\/banners\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare('UPDATE home_banner SET image = ?, link = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$data['image'], $data['link'], $data['sort_order'], $id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/home\/banners\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('DELETE FROM home_banner WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if ($apiPath === 'admin/home/ads' && $method === 'GET') {
        $stmt = $pdo->query('SELECT id, image, link, sort_order FROM home_ad ORDER BY sort_order ASC');
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => $ads]
        ]);
        exit;
    }

    if ($apiPath === 'admin/home/ads' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare('INSERT INTO home_ad (image, link, sort_order) VALUES (?, ?, ?)');
        $stmt->execute([$data['image'], $data['link'], $data['sort_order']]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/home\/ads\/(\d+)$/', $apiPath, $matches) && $method === 'PUT') {
        $id = $matches[1];
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare('UPDATE home_ad SET image = ?, link = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([$data['image'], $data['link'], $data['sort_order'], $id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if (preg_match('/^admin\/home\/ads\/(\d+)$/', $apiPath, $matches) && $method === 'DELETE') {
        $id = $matches[1];
        
        $stmt = $pdo->prepare('DELETE FROM home_ad WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode(['code' => 200, 'msg' => 'Success']);
        exit;
    }

    if ($apiPath === 'admin/script-purchase-intents' && $method === 'GET') {
        $admin = requireAdmin($pdo);
        requireSuperAdmin($admin);

        $stmt = $pdo->query('SELECT spi.id, spi.script_id, spi.brand_id, spi.city, spi.contact_name, spi.contact_phone, spi.created_at, s.name AS script_name, b.name AS brand_name FROM script_purchase_intent spi LEFT JOIN script s ON s.id = spi.script_id LEFT JOIN brand b ON b.id = spi.brand_id ORDER BY spi.id DESC');
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'code' => 200,
            'msg' => 'Success',
            'data' => ['list' => $list]
        ]);
        exit;
    }

    // Default response
    echo json_encode(['code' => 404, 'msg' => 'API endpoint not found']);
} else {
    // Static files
    $file = __DIR__ . $path;
    if (file_exists($file) && is_file($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
            'css' => 'text/css',
            'js' => 'application/javascript'
        ];

        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($file);
    } else {
        echo json_encode(['code' => 404, 'msg' => 'File not found']);
    }
}
