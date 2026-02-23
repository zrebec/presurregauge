<?php
/**
 * Pressure Gauge — Database Migration Runner
 * 
 * Standalone script for running SQL migrations.
 * Upload to server, uncomment the migrations you need, run in browser.
 * 
 * Usage:
 *   1. Upload this file to server root (usualy under data)
 *   2. Uncomment the migration files you want to execute in the $migrations array
 *   3. Open in browser: https://yoursite.com/data/migrate.php
 *   4. Review the output
 *   5. DELETE this file from server after use (security!)
 */

// ============================================================
// CONFIGURATION
// ============================================================

// Path to SQLite database and migration scripts
$dbPath = '../pressuregauge.db';
$migrationsDir = __DIR__ . '/migrations';

// ============================================================
// SELECT MIGRATIONS TO RUN
// Uncomment the lines you want to execute, keep the rest commented.
// They run in the order listed here.
// ============================================================

$migrations = [
    // '000_pragma_settings.sql',
    // '001_create_structure.sql',
    // '002_basic_data.sql',
    '004.alter_add_spo2_and_note.sql',
    // '999_test_file.sql',
];

// ============================================================
// MIGRATION RUNNER — do not edit below this line
// ============================================================

$results = [];

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbStatus = ['ok' => true, 'message' => 'Connected to: ' . $dbPath];
} catch (PDOException $e) {
    $dbStatus = ['ok' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
}

if ($dbStatus['ok'] && !empty($migrations)) {
    foreach ($migrations as $filename) {
        $filepath = $migrationsDir . '/' . $filename;
        $result = ['file' => $filename, 'status' => 'error', 'message' => '', 'statements' => []];

        // Check if file exists
        if (!file_exists($filepath)) {
            $result['message'] = 'File not found: ' . $filepath;
            $results[] = $result;
            continue;
        }

        // Read SQL content
        $sql = file_get_contents($filepath);
        if (empty(trim($sql))) {
            $result['status'] = 'skipped';
            $result['message'] = 'File is empty';
            $results[] = $result;
            continue;
        }

        // Filter out SQLite CLI commands (.mode, .headers, etc.) and SQL comments
        $lines = explode("\n", $sql);
        $filteredLines = array_filter($lines, function($line) {
            $trimmed = trim($line);
            return $trimmed !== '' 
                && !str_starts_with($trimmed, '.') 
                && !str_starts_with($trimmed, '--');
        });
        $cleanSql = implode("\n", $filteredLines);

        // Split into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $cleanSql)),
            fn($s) => !empty($s)
        );

        if (empty($statements)) {
            $result['status'] = 'skipped';
            $result['message'] = 'No executable statements found';
            $results[] = $result;
            continue;
        }

        // Execute each statement
        $allOk = true;
        foreach ($statements as $stmt) {
            $stmtResult = ['sql' => $stmt, 'status' => 'ok', 'message' => ''];
            try {
                // Detect SELECT statements and fetch results
                if (stripos(trim($stmt), 'SELECT') === 0) {
                    $rows = $db->query($stmt)->fetchAll(PDO::FETCH_ASSOC);
                    $stmtResult['message'] = count($rows) . ' row(s) returned';
                    $stmtResult['rows'] = $rows;
                } else {
                    $affected = $db->exec($stmt);
                    $stmtResult['message'] = $affected . ' row(s) affected';
                }
            } catch (PDOException $e) {
                $stmtResult['status'] = 'error';
                $stmtResult['message'] = $e->getMessage();
                $allOk = false;
            }
            $result['statements'][] = $stmtResult;
        }

        $result['status'] = $allOk ? 'ok' : 'error';
        $result['message'] = count($statements) . ' statement(s), ' 
            . ($allOk ? 'all succeeded' : 'some failed');
        $results[] = $result;
    }
}

// Count results for summary
$countOk = count(array_filter($results, fn($r) => $r['status'] === 'ok'));
$countErr = count(array_filter($results, fn($r) => $r['status'] === 'error'));
$countSkip = count(array_filter($results, fn($r) => $r['status'] === 'skipped'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration Runner — Pressure Gauge</title>
    <style>
        :root {
            --bg: #0a0a0a;
            --fg: #d4d4d4;
            --border: #333;
            --green: #22c55e;
            --green-bg: rgba(34, 197, 94, 0.08);
            --red: #ef4444;
            --red-bg: rgba(239, 68, 68, 0.08);
            --yellow: #eab308;
            --yellow-bg: rgba(234, 179, 8, 0.08);
            --blue: #60a5fa;
            --mono: 'Courier New', Courier, monospace;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg);
            color: var(--fg);
            font-family: var(--mono);
            font-size: 14px;
            line-height: 1.6;
            padding: 2rem;
            max-width: 960px;
            margin: 0 auto;
        }

        h1 {
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            color: var(--blue);
        }

        /* Status box for DB connection */
        .db-status {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            margin-bottom: 1.5rem;
            border-left: 3px solid var(--green);
            background: var(--green-bg);
        }

        .db-status.error {
            border-left-color: var(--red);
            background: var(--red-bg);
        }

        /* Summary bar */
        .summary {
            display: flex;
            gap: 1.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            margin-bottom: 1.5rem;
            background: rgba(255,255,255,0.02);
        }

        .summary span { font-weight: bold; }
        .summary .ok { color: var(--green); }
        .summary .err { color: var(--red); }
        .summary .skip { color: var(--yellow); }

        /* Empty state */
        .empty-state {
            padding: 2rem;
            text-align: center;
            border: 1px dashed var(--border);
            border-radius: 4px;
            color: #666;
        }

        /* Migration result card */
        .migration {
            border: 1px solid var(--border);
            border-radius: 4px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .migration-header {
            padding: 0.6rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            border-bottom: 1px solid var(--border);
        }

        .migration.ok .migration-header { 
            background: var(--green-bg); 
            border-left: 3px solid var(--green);
        }
        .migration.error .migration-header { 
            background: var(--red-bg); 
            border-left: 3px solid var(--red);
        }
        .migration.skipped .migration-header { 
            background: var(--yellow-bg); 
            border-left: 3px solid var(--yellow);
        }

        .badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0.15rem 0.5rem;
            border-radius: 3px;
        }
        .badge.ok { color: var(--green); border: 1px solid var(--green); }
        .badge.error { color: var(--red); border: 1px solid var(--red); }
        .badge.skipped { color: var(--yellow); border: 1px solid var(--yellow); }

        .migration-info {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            color: #888;
        }

        /* Individual statement result */
        .stmt {
            padding: 0.5rem 1rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .stmt-sql {
            background: rgba(255,255,255,0.03);
            padding: 0.4rem 0.6rem;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-bottom: 0.3rem;
            white-space: pre-wrap;
            word-break: break-all;
            color: var(--fg);
        }

        .stmt-result {
            font-size: 0.75rem;
        }
        .stmt-result.ok { color: var(--green); }
        .stmt-result.error { color: var(--red); }

        /* SELECT result table */
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.4rem 0;
            font-size: 0.75rem;
        }
        .result-table th {
            text-align: left;
            padding: 0.3rem 0.5rem;
            border-bottom: 1px solid var(--border);
            color: var(--blue);
        }
        .result-table td {
            padding: 0.3rem 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .warning {
            margin-top: 2rem;
            padding: 0.75rem 1rem;
            border: 1px solid var(--red);
            border-radius: 4px;
            background: var(--red-bg);
            color: var(--red);
            font-size: 0.85rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>&#9656; Migration Runner</h1>

    <!-- DB connection status -->
    <div class="db-status <?= $dbStatus['ok'] ? '' : 'error' ?>">
        <?= $dbStatus['ok'] ? '&#10003;' : '&#10007;' ?> 
        <?= htmlspecialchars($dbStatus['message']) ?>
    </div>

    <?php if (empty($migrations)): ?>
        <!-- No migrations selected -->
        <div class="empty-state">
            No migrations selected.<br>
            Edit <strong>$migrations</strong> array in this file to select SQL files to run.
        </div>

    <?php else: ?>
        <!-- Summary -->
        <div class="summary">
            <div>Migrations: <span><?= count($results) ?></span></div>
            <?php if ($countOk): ?><div class="ok">&#10003; <?= $countOk ?> ok</div><?php endif; ?>
            <?php if ($countErr): ?><div class="err">&#10007; <?= $countErr ?> failed</div><?php endif; ?>
            <?php if ($countSkip): ?><div class="skip">&#9888; <?= $countSkip ?> skipped</div><?php endif; ?>
        </div>

        <!-- Migration results -->
        <?php foreach ($results as $result): ?>
            <div class="migration <?= $result['status'] ?>">
                <div class="migration-header">
                    <span><?= htmlspecialchars($result['file']) ?></span>
                    <span class="badge <?= $result['status'] ?>"><?= $result['status'] ?></span>
                </div>
                <div class="migration-info"><?= htmlspecialchars($result['message']) ?></div>

                <?php foreach ($result['statements'] as $stmt): ?>
                    <div class="stmt">
                        <div class="stmt-sql"><?= htmlspecialchars($stmt['sql']) ?></div>
                        <div class="stmt-result <?= $stmt['status'] ?>">
                            <?= $stmt['status'] === 'ok' ? '&#10003;' : '&#10007;' ?>
                            <?= htmlspecialchars($stmt['message']) ?>
                        </div>

                        <?php if (!empty($stmt['rows'])): ?>
                            <table class="result-table">
                                <thead>
                                    <tr>
                                        <?php foreach (array_keys($stmt['rows'][0]) as $col): ?>
                                            <th><?= htmlspecialchars($col) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stmt['rows'] as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $val): ?>
                                                <td><?= htmlspecialchars($val ?? 'NULL') ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="warning">
        &#9888; Delete this file from server after use. Do not leave migration scripts accessible in production.
    </div>
</body>
</html>