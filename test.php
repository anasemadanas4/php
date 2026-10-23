
<?php
$checks = [];

$checks[] = [
    "name" => "PHP Runtime",
    "status" => PHP_VERSION,
    "ok" => version_compare(PHP_VERSION, "7.4.0", ">=")
];

$checks[] = [
    "name" => "Web Server",
    "status" => $_SERVER["SERVER_SOFTWARE"] ?? "Unknown",
    "ok" => isset($_SERVER["SERVER_SOFTWARE"])
];

$checks[] = [
    "name" => "File System",
    "status" => is_writable(__DIR__) ? "Writable" : "Not Writable",
    "ok" => is_writable(__DIR__)
];

$checks[] = [
    "name" => "Session Support",
    "status" => extension_loaded("session") ? "Available" : "Unavailable",
    "ok" => extension_loaded("session")
];

$checks[] = [
    "name" => "JSON Extension",
    "status" => extension_loaded("json") ? "Available" : "Unavailable",
    "ok" => extension_loaded("json")
];

$checks[] = [
    "name" => "PDO Extension",
    "status" => extension_loaded("PDO") ? "Available" : "Unavailable",
    "ok" => extension_loaded("PDO")
];

$checks[] = [
    "name" => "MySQL Driver",
    "status" => extension_loaded("pdo_mysql") ? "Available" : "Unavailable",
    "ok" => extension_loaded("pdo_mysql")
];

$checks[] = [
    "name" => "cURL Extension",
    "status" => extension_loaded("curl") ? "Available" : "Unavailable",
    "ok" => extension_loaded("curl")
];

$checks[] = [
    "name" => "OpenSSL Extension",
    "status" => extension_loaded("openssl") ? "Available" : "Unavailable",
    "ok" => extension_loaded("openssl")
];

$checks[] = [
    "name" => "Upload Support",
    "status" => ini_get("file_uploads") ? "Enabled" : "Disabled",
    "ok" => (bool) ini_get("file_uploads")
];

$passed = count(array_filter($checks, fn($check) => $check["ok"]));
$total = count($checks);
$percentage = round(($passed / $total) * 100);

$databaseStatus = "Not Configured";

if (extension_loaded("pdo_mysql")) {
    $databaseStatus = "Driver Ready";
} elseif (extension_loaded("sqlite3")) {
    $databaseStatus = "SQLite Available";
} else {
    $databaseStatus = "No Supported Driver";
}

$memoryLimit = ini_get("memory_limit");
$maxUpload = ini_get("upload_max_filesize");
$maxPost = ini_get("post_max_size");
$maxExecution = ini_get("max_execution_time");
$https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
    || (($_SERVER["SERVER_PORT"] ?? null) == 443);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Server Diagnostics</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0b1120;
            color: #e5e7eb;
            min-height: 100vh;
        }

        .container {
            max-width: 1050px;
            margin: auto;
            padding: 40px 20px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 30px;
            margin: 0 0 10px;
        }

        .header p {
            color: #94a3b8;
            margin: 0;
            line-height: 1.6;
        }

        .panel {
            background: #111b2e;
            border: 1px solid #25334a;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 22px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }

        .stat {
            background: #17243a;
            border: 1px solid #2b3a52;
            border-radius: 12px;
            padding: 20px;
        }

        .stat span {
            display: block;
            color: #94a3b8;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .stat strong {
            font-size: 23px;
            overflow-wrap: anywhere;
        }

        .progress {
            height: 8px;
            background: #273449;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 18px;
        }

        .progress div {
            height: 100%;
            width: <?= $percentage ?>%;
            background: <?= $percentage === 100 ? "#22c55e" : "#3b82f6" ?>;
        }

        h2 {
            font-size: 19px;
            margin: 0 0 20px;
        }

        .check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #25334a;
        }

        .check:last-child {
            border-bottom: none;
        }

        .name {
            font-weight: 600;
        }

        .value {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 6px;
            overflow-wrap: anywhere;
        }

        .badge {
            border-radius: 20px;
            padding: 6px 10px;
            font-size: 12px;
            white-space: nowrap;
        }

        .good {
            background: #123a2b;
            color: #4ade80;
        }

        .bad {
            background: #40202a;
            color: #fda4af;
        }

        .info {
            background: #193451;
            color: #93c5fd;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }

        .detail {
            padding: 12px 0;
        }

        .detail span {
            display: block;
            color: #94a3b8;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .detail strong {
            overflow-wrap: anywhere;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            border: none;
            border-radius: 9px;
            padding: 11px 16px;
            font-weight: 600;
            cursor: pointer;
            background: #2563eb;
            color: white;
        }

        button.secondary {
            background: #25334a;
        }

        .footer {
            color: #64748b;
            font-size: 12px;
            text-align: center;
            padding: 15px;
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px 14px;
            }

            .header h1 {
                font-size: 25px;
            }

            .panel {
                padding: 18px;
            }

            .check {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <header class="header">
        <h1>PHP Server Diagnostics</h1>
        <p>Runtime checks, server configuration, and extension availability.</p>
    </header>

    <section class="panel">
        <div class="summary">
            <div class="stat">
                <span>PHP VERSION</span>
                <strong><?= htmlspecialchars(PHP_VERSION) ?></strong>
            </div>
            <div class="stat">
                <span>CHECKS PASSED</span>
                <strong><?= $passed ?> / <?= $total ?></strong>
            </div>
            <div class="stat">
                <span>DATABASE DRIVER</span>
                <strong style="font-size: 18px">
                    <?= htmlspecialchars($databaseStatus) ?>
                </strong>
            </div>
            <div class="stat">
                <span>HTTPS</span>
                <strong><?= $https ? "Enabled" : "Disabled" ?></strong>
            </div>
        </div>

        <div class="progress">
            <div></div>
        </div>
        <p style="color:#94a3b8;font-size:13px;margin-bottom:0">
            <?= $percentage ?>% of basic environment checks passed.
        </p>
    </section>

    <section class="panel">
        <h2>Environment Checks</h2>

        <?php foreach ($checks as $check): ?>
            <div class="check">
                <div>
                    <div class="name">
                        <?= htmlspecialchars($check["name"]) ?>
                    </div>
                    <div class="value">
                        <?= htmlspecialchars((string) $check["status"]) ?>
                    </div>
                </div>
                <span class="badge <?= $check["ok"] ? "good" : "bad" ?>">
                    <?= $check["ok"] ? "PASS" : "CHECK" ?>
                </span>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>PHP Configuration</h2>

        <div class="details">
            <div class="detail">
                <span>Memory Limit</span>
                <strong><?= htmlspecialchars($memoryLimit) ?></strong>
            </div>
            <div class="detail">
                <span>Maximum Upload Size</span>
                <strong><?= htmlspecialchars($maxUpload) ?></strong>
            </div>
            <div class="detail">
                <span>Maximum POST Size</span>
                <strong><?= htmlspecialchars($maxPost) ?></strong>
            </div>
            <div class="detail">
                <span>Execution Time Limit</span>
                <strong><?= htmlspecialchars($maxExecution) ?> seconds</strong>
            </div>
            <div class="detail">
                <span>Operating System</span>
                <strong><?= htmlspecialchars(PHP_OS_FAMILY) ?></strong>
            </div>
            <div class="detail">
                <span>Server Time</span>
                <strong><?= date("Y-m-d H:i:s") ?></strong>
            </div>
        </div>

        <div class="actions">
            <button onclick="location.reload()">Refresh Diagnostics</button>
            <button class="secondary" onclick="window.print()">Print Report</button>
        </div>
    </section>

    <div class="footer">
        PHP Server Diagnostics &bull; <?= date("Y") ?>
    </div>
</div>
</body>
</html>
