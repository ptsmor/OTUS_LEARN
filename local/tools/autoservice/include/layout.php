<?php

/**
 * Общая обёртка страниц закупок (вид для «заказчика»; диагностика — ?debug=1).
 */

declare(strict_types=1);

function autoservice_tool_debug(): bool
{
    return isset($_GET['debug']) && (string)$_GET['debug'] === '1';
}

/**
 * @return array<string, array{0: string, 1: string}>
 */
function autoservice_tool_nav(): array
{
    return [
        'manual' => ['purchase_manual.php', 'Новая заявка'],
        'approve' => ['purchase_approve.php', 'Согласование'],
        'requests' => ['purchase_requests.php', 'К закупщику'],
        'stock' => ['stock_sync.php', 'Остатки склада'],
    ];
}

function autoservice_tool_header(string $pageTitle, string $activeNav, ?string $subtitle = null): void
{
    $nav = autoservice_tool_nav();
    $debug = autoservice_tool_debug();
    $debugQs = $debug ? '?debug=1' : '';

    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . htmlspecialcharsbx($pageTitle) . ' — Автосервис</title>';
    echo '<style>
        :root { --as-blue:#2fc6f6; --as-blue-dark:#1e8eb8; --as-bg:#eef2f4; --as-card:#fff; --as-border:#dfe3e6; --as-text:#333; --as-muted:#828b95; }
        * { box-sizing:border-box; }
        body { margin:0; font:14px/1.45 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; color:var(--as-text); background:var(--as-bg); }
        a { color:var(--as-blue-dark); }
        .as-top { background:linear-gradient(90deg,#2fc6f6,#55d0f8); color:#fff; padding:14px 24px; }
        .as-top a { color:#fff; text-decoration:none; opacity:.95; }
        .as-top a:hover { text-decoration:underline; }
        .as-brand { font-size:18px; font-weight:600; margin:0 0 4px; }
        .as-brand small { font-weight:400; opacity:.9; font-size:13px; }
        .as-nav { display:flex; flex-wrap:wrap; gap:6px; margin-top:12px; }
        .as-nav a { padding:7px 14px; border-radius:4px; background:rgba(255,255,255,.2); font-size:13px; }
        .as-nav a.active { background:#fff; color:var(--as-blue-dark); font-weight:600; }
        .as-wrap { max-width:1100px; margin:0 auto; padding:20px 16px 32px; }
        .as-card { background:var(--as-card); border:1px solid var(--as-border); border-radius:8px; padding:20px 22px; margin-bottom:16px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
        .as-card h1 { margin:0 0 8px; font-size:22px; font-weight:600; }
        .as-lead { color:var(--as-muted); margin:0 0 16px; max-width:720px; }
        .as-alert { padding:12px 16px; border-radius:6px; margin-bottom:16px; }
        .as-alert--ok { background:#e8f5e9; border:1px solid #a5d6a7; color:#2e7d32; }
        .as-alert--err { background:#ffebee; border:1px solid #ef9a9a; color:#c62828; }
        .as-alert--info { background:#e3f2fd; border:1px solid #90caf9; color:#1565c0; }
        .as-table { width:100%; border-collapse:collapse; font-size:13px; }
        .as-table th,.as-table td { border:1px solid var(--as-border); padding:10px 12px; text-align:left; vertical-align:middle; }
        .as-table th { background:#f5f7f8; font-weight:600; }
        .as-table tr:hover td { background:#fafbfc; }
        .as-btn { cursor:pointer; border:1px solid var(--as-blue-dark); background:var(--as-blue); color:#fff; padding:7px 14px; border-radius:4px; font-size:13px; }
        .as-btn:hover { background:var(--as-blue-dark); }
        .as-btn--ghost { background:#fff; color:var(--as-blue-dark); }
        .as-btn--danger { border-color:#c62828; background:#fff; color:#c62828; }
        .as-input { padding:7px 10px; border:1px solid var(--as-border); border-radius:4px; font-size:13px; }
        .as-form label { display:block; margin-bottom:14px; font-weight:500; }
        .as-form select,.as-form input[type=text] { margin-top:6px; min-width:320px; max-width:100%; }
        .as-stats { display:flex; flex-wrap:wrap; gap:12px; margin:12px 0 0; }
        .as-stat { flex:1; min-width:140px; background:#f5f7f8; border-radius:6px; padding:12px 14px; border:1px solid var(--as-border); }
        .as-stat strong { display:block; font-size:22px; margin-top:4px; }
        .as-footer { font-size:12px; color:var(--as-muted); margin-top:20px; padding-top:12px; border-top:1px solid var(--as-border); }
        .as-debug { margin-top:16px; padding:14px; background:#fffde7; border:1px dashed #e6c200; border-radius:6px; font-size:12px; font-family:Consolas,monospace; white-space:pre-wrap; }
        .as-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; background:#e8f5e9; }
        .as-badge--auto { background:#e3f2fd; }
    </style></head><body>';

    echo '<header class="as-top"><div class="as-wrap" style="padding-top:0;padding-bottom:0">';
    echo '<p class="as-brand">Автосервис <small>· склад, услуги и запчасти</small></p>';
    echo '<p style="margin:0;font-size:13px;opacity:.9"><a href="/crm/">← CRM</a>'
        . ' · <span style="opacity:.85">в CRM: меню «Склад и закупки»</span></p>';
    echo '<nav class="as-nav">';
    foreach ($nav as $key => $item) {
        $cls = $key === $activeNav ? ' class="active"' : '';
        echo '<a href="' . htmlspecialcharsbx($item[0] . $debugQs) . '"' . $cls . '>'
            . htmlspecialcharsbx($item[1]) . '</a>';
    }
    echo '</nav></div></header>';

    echo '<main class="as-wrap"><div class="as-card">';
    echo '<h1>' . htmlspecialcharsbx($pageTitle) . '</h1>';
    if ($subtitle !== null && $subtitle !== '') {
        echo '<p class="as-lead">' . $subtitle . '</p>';
    }
}

function autoservice_tool_footer(?string $debugHtml = null): void
{
    echo '</div>'; // .as-card

    $debug = autoservice_tool_debug();
    if ($debug && $debugHtml !== null && $debugHtml !== '') {
        echo '<div class="as-debug"><strong>Режим разработчика</strong> (<code>debug=1</code>)<br>' . $debugHtml . '</div>';
    }

    echo '</main></body></html>';
}

function autoservice_tool_alert(string $type, string $message): void
{
    if ($message === '') {
        return;
    }
    $map = ['ok' => 'as-alert--ok', 'err' => 'as-alert--err', 'info' => 'as-alert--info'];
    $class = $map[$type] ?? 'as-alert--info';
    echo '<div class="as-alert ' . $class . '">' . htmlspecialcharsbx($message) . '</div>';
}