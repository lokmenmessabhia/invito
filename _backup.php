<?php
declare(strict_types=1);
/**
 * Production-ready database backup script.
 * Run via cron (Linux) or Task Scheduler (Windows) every hour.
 *
 * Linux cron:   0 * * * * /usr/bin/php /path/to/_backup.php
 * Windows:      schtasks /create /tn "Invito Backup" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\invito\_backup.php" /sc hourly /f
 */

require_once __DIR__ . '/config/database.php';

// --- Configuration ---
$backupDir     = __DIR__ . '/backup';
$logFile       = $backupDir . '/backup.log';
$lockFile      = $backupDir . '/.backup.lock';
$retentionDays = 30;
$interval      = 3600;

// --- Helpers ---

function logMsg(string $msg): void {
  global $logFile;
  $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
  echo $line . "\n";
  if ($logFile) {
    file_put_contents($logFile, $line . "\n", FILE_APPEND | LOCK_EX);
  }
}

function findMysqldump(): ?string {
  $candidates = [
    'mysqldump',
    '/usr/bin/mysqldump',
    '/usr/local/bin/mysqldump',
    '/opt/homebrew/bin/mysqldump',
    'C:\xampp\mysql\bin\mysqldump.exe',
    'C:\wamp64\bin\mysql\mysql8.0.31\bin\mysqldump.exe',
    'C:\wamp\bin\mysql\mysql8.0\bin\mysqldump.exe',
  ];
  foreach ($candidates as $bin) {
    if (PHP_OS_FAMILY === 'Windows') {
      if (file_exists($bin)) return $bin;
    } else {
      if (!str_contains($bin, '/')) {
        $which = trim(shell_exec('which ' . escapeshellarg($bin) . ' 2>/dev/null') ?? '');
        if ($which) return $which;
      } elseif (file_exists($bin)) {
        return $bin;
      }
    }
  }
  return null;
}

function acquireLock(): bool {
  global $lockFile;
  $f = @fopen($lockFile, 'c');
  if (!$f) return false;
  if (!@flock($f, LOCK_EX | LOCK_NB)) {
    fclose($f);
    return false;
  }
  // Write PID so we can debug stale locks
  ftruncate($f, 0);
  fwrite($f, (string)getmypid());
  fflush($f);
  return true;
}

function releaseLock(): void {
  global $lockFile;
  $f = @fopen($lockFile, 'r');
  if ($f) {
    flock($f, LOCK_UN);
    fclose($f);
  }
}

function cleanOldBackups(): void {
  global $backupDir, $retentionDays;
  $cutoff = time() - ($retentionDays * 86400);
  foreach (glob($backupDir . '/*.sql') as $f) {
    if (filemtime($f) < $cutoff) {
      @unlink($f);
      logMsg('Deleted old backup: ' . basename($f));
    }
  }
  foreach (glob($backupDir . '/*.sql.gz') as $f) {
    if (filemtime($f) < $cutoff) {
      @unlink($f);
      logMsg('Deleted old backup: ' . basename($f));
    }
  }
}

function getLastBackupTime(): ?int {
  global $backupDir;
  $files = glob($backupDir . '/*.{sql,sql.gz}', GLOB_BRACE);
  if (!$files) return null;
  $times = array_map('filemtime', $files);
  rsort($times);
  return $times[0];
}

function runBackup(): void {
  global $backupDir;
  $mysqldump = findMysqldump();
  if (!$mysqldump) {
    logMsg('ERROR: mysqldump not found. Install MySQL client or set path manually.');
    return;
  }

  $filename = date('Y-m-d_H') . '.sql';
  $filepath = $backupDir . '/' . $filename;

  $passArg = DB_PASS ? sprintf(" -p'%s'", DB_PASS) : '';
  $cmd = sprintf(
    '%s --host=%s --port=%s --user=%s%s --routines --triggers --add-drop-table --single-transaction --quick %s > %s 2>&1',
    escapeshellarg($mysqldump),
    escapeshellarg(DB_HOST),
    escapeshellarg((string)DB_PORT),
    escapeshellarg(DB_USER),
    $passArg,
    escapeshellarg(DB_NAME),
    escapeshellarg($filepath)
  );

  logMsg('Running: ' . $mysqldump);
  $output = [];
  $retCode = 0;
  exec($cmd, $output, $retCode);

  if ($retCode !== 0) {
    logMsg('mysqldump failed (code ' . $retCode . '): ' . implode("\n", $output));
    @unlink($filepath);
    return;
  }

  if (!file_exists($filepath) || filesize($filepath) === 0) {
    logMsg('Backup file is empty or not created.');
    @unlink($filepath);
    return;
  }

  // Compress with gzip
  $gzPath = $filepath . '.gz';
  $gz = gzopen($gzPath, 'w9');
  if ($gz) {
    gzwrite($gz, file_get_contents($filepath));
    gzclose($gz);
    @unlink($filepath);
    $filepath = $gzPath;
    $filename .= '.gz';
  }

  logMsg("Backup saved: {$filename} (" . round(filesize($filepath) / 1024) . ' KB)');

  // Clean old backups after successful save
  cleanOldBackups();
}

// --- Main ---

// Ensure backup directory exists
if (!is_dir($backupDir)) {
  mkdir($backupDir, 0755, true);
  // Protection: write index.html to prevent directory listing
  file_put_contents($backupDir . '/index.html', '');
}

// Acquire lock to prevent concurrent runs
if (!acquireLock()) {
  logMsg('Another backup process is already running. Exiting.');
  exit;
}

$lastTime = getLastBackupTime();
logMsg('Last backup: ' . ($lastTime ? date('Y-m-d H:i:s', $lastTime) : 'never'));

// Skip if less than 1 hour since last backup
if ($lastTime !== null && (time() - $lastTime) < $interval) {
  logMsg('Less than 1 hour since last backup. Exiting.');
  releaseLock();
  exit;
}

runBackup();
releaseLock();
