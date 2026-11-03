<?php

function app_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function app_verify_csrf_token($token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function app_require_role(array $roles, string $loginPath): void
{
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', $roles, true)) {
        header('Location: ' . $loginPath);
        exit();
    }
}

function app_page_number(string $parameter): int
{
    $value = $_GET[$parameter] ?? 1;
    if (!is_string($value) || !ctype_digit($value)) {
        return 1;
    }

    return max(1, min((int) $value, 1000000));
}

function app_page_offset(int $page, int $pageSize = 50): int
{
    $safePage = max(1, min($page, 1000000));
    $safePageSize = max(1, min($pageSize, 1000));
    return ($safePage - 1) * $safePageSize;
}

function app_trim_extra_page_row(array &$rows, int $pageSize = 50): bool
{
    if (count($rows) <= $pageSize) {
        return false;
    }

    array_pop($rows);
    return true;
}

function app_render_pagination(string $parameter, int $page, bool $hasPrevious, bool $hasNext): void
{
    if (!$hasPrevious && !$hasNext) {
        return;
    }

    $query = $_GET;
    $pageUrl = static function (int $targetPage) use ($parameter, $query): string {
        $query[$parameter] = $targetPage;
        return '?' . http_build_query($query);
    };

    echo '<nav class="pagination" aria-label="Table pages">';
    if ($hasPrevious) {
        echo '<a href="' . app_escape($pageUrl($page - 1)) . '">Previous</a>';
    }
    echo '<span>Page ' . $page . '</span>';
    if ($hasNext) {
        echo '<a href="' . app_escape($pageUrl($page + 1)) . '">Next</a>';
    }
    echo '</nav>';
}
