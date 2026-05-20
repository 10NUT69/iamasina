<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use ZipArchive;

class AdminBackupController extends Controller
{
    private const DB_CONFIRM_TEXT = 'CONFIRM IMPORT';
    private const MEDIA_CONFIRM_TEXT = 'CONFIRM MEDIA IMPORT';
    private const MEDIA_ARCHIVE_ROOTS = ['services', 'dealers'];
    private const ALLOWED_MEDIA_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

    public function index()
    {
        return view('admin.backups.index', [
            'mysqldumpAvailable' => (bool) $this->findExecutable('mysqldump'),
            'mysqlAvailable' => (bool) $this->findExecutable('mysql'),
            'zipAvailable' => class_exists(ZipArchive::class),
            'mediaDirectories' => $this->mediaDirectories(),
            'safetyDirectory' => $this->safetyBackupDirectory(),
            'manualMediaImportDirectory' => $this->manualMediaImportDirectory(),
            'manualMediaImports' => $this->manualMediaImportFiles(),
            'maxUploadSize' => $this->formatBytes($this->maxUploadBytes()),
        ]);
    }

    public function exportDatabase()
    {
        $this->logBackupEvent('db_export', 'started');

        try {
            $fileName = 'iaauto-db-backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
            $filePath = $this->temporaryBackupDirectory() . DIRECTORY_SEPARATOR . $fileName;

            $this->exportDatabaseToFile($filePath);

            $this->logBackupEvent('db_export', 'success', [
                'file' => $fileName,
                'bytes' => filesize($filePath) ?: 0,
            ]);

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/sql',
            ])->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            $this->logBackupEvent('db_export', 'failed', [
                'error' => $this->safeErrorMessage($e),
            ]);

            return back()->with('error', $this->publicDatabaseExportError($e));
        }
    }

    public function importDatabase(Request $request)
    {
        $this->validateDatabaseImportRequest($request);
        $this->logBackupEvent('db_import', 'started');

        $importPath = null;
        $safetyPath = null;

        try {
            $importPath = $this->storeUploadedImportFile($request->file('sql_file'), 'sql');
            $safetyPath = $this->safetyBackupDirectory() . DIRECTORY_SEPARATOR
                . 'iaauto-db-safety-' . now()->format('Y-m-d-H-i-s') . '.sql';

            $this->exportDatabaseToFile($safetyPath);
            $this->importDatabaseFromFile($importPath);

            $this->logBackupEvent('db_import', 'success', [
                'safety_backup' => basename($safetyPath),
            ]);

            return back()->with('success', 'Baza de date a fost importata cu succes. Backupul de siguranta a fost salvat in storage/app/backups/safety/.');
        } catch (Throwable $e) {
            $this->logBackupEvent('db_import', 'failed', [
                'error' => $this->safeErrorMessage($e),
                'safety_backup' => $safetyPath ? basename($safetyPath) : null,
            ]);

            return back()->with('error', 'Importul bazei de date a esuat. Daca backupul de siguranta a fost creat, acesta a fost pastrat in storage/app/backups/safety/. Detalii: ' . $this->publicImportError($e));
        } finally {
            if ($importPath && is_file($importPath)) {
                @unlink($importPath);
            }
        }
    }

    public function exportMedia()
    {
        $this->logBackupEvent('media_export', 'started');

        try {
            $fileName = 'iaauto-media-backup-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $filePath = $this->temporaryBackupDirectory() . DIRECTORY_SEPARATOR . $fileName;

            $this->createMediaZip($filePath);

            $this->logBackupEvent('media_export', 'success', [
                'file' => $fileName,
                'bytes' => filesize($filePath) ?: 0,
            ]);

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            $this->logBackupEvent('media_export', 'failed', [
                'error' => $this->safeErrorMessage($e),
            ]);

            return back()->with('error', 'Exportul media nu a putut fi realizat. Detalii: ' . $this->publicImportError($e));
        }
    }

    public function importMedia(Request $request)
    {
        $this->validateMediaImportRequest($request);
        $this->logBackupEvent('media_import', 'started', [
            'delete_existing' => $request->boolean('delete_existing_media'),
        ]);

        $importPath = null;
        $safetyPath = null;

        try {
            $importPath = $this->storeUploadedImportFile($request->file('media_file'), 'zip');
            $this->validateMediaZip($importPath);

            $safetyPath = $this->safetyBackupDirectory() . DIRECTORY_SEPARATOR
                . 'iaauto-media-safety-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $this->createMediaZip($safetyPath);

            $this->ensureMediaDirectoriesExist();

            if ($request->boolean('delete_existing_media')) {
                $this->emptyMediaDirectories();
            }

            $this->extractMediaZip($importPath);

            $this->logBackupEvent('media_import', 'success', [
                'safety_backup' => basename($safetyPath),
                'delete_existing' => $request->boolean('delete_existing_media'),
            ]);

            return back()->with('success', 'Media a fost importata cu succes. Backupul de siguranta a fost salvat in storage/app/backups/safety/.');
        } catch (Throwable $e) {
            $this->logBackupEvent('media_import', 'failed', [
                'error' => $this->safeErrorMessage($e),
                'safety_backup' => $safetyPath ? basename($safetyPath) : null,
            ]);

            return back()->with('error', 'Importul media a esuat. Daca backupul de siguranta a fost creat, acesta a fost pastrat in storage/app/backups/safety/. Detalii: ' . $this->publicImportError($e));
        } finally {
            if ($importPath && is_file($importPath)) {
                @unlink($importPath);
            }
        }
    }

    public function importMediaFromServer(Request $request)
    {
        $this->validateServerMediaImportRequest($request);
        $importPath = $this->manualMediaImportPath($request->input('server_media_file'));

        $this->logBackupEvent('media_import_server', 'started', [
            'source_file' => basename($importPath),
            'delete_existing' => $request->boolean('delete_existing_server_media'),
        ]);

        $safetyPath = null;

        try {
            $this->validateMediaZip($importPath);

            $safetyPath = $this->safetyBackupDirectory() . DIRECTORY_SEPARATOR
                . 'iaauto-media-safety-' . now()->format('Y-m-d-H-i-s') . '.zip';
            $this->createMediaZip($safetyPath);

            $this->ensureMediaDirectoriesExist();

            if ($request->boolean('delete_existing_server_media')) {
                $this->emptyMediaDirectories();
            }

            $this->extractMediaZip($importPath);

            $this->logBackupEvent('media_import_server', 'success', [
                'source_file' => basename($importPath),
                'safety_backup' => basename($safetyPath),
                'delete_existing' => $request->boolean('delete_existing_server_media'),
            ]);

            return back()->with('success', 'Media a fost importata cu succes din fisierul de pe server. Backupul de siguranta a fost salvat in storage/app/backups/safety/.');
        } catch (Throwable $e) {
            $this->logBackupEvent('media_import_server', 'failed', [
                'source_file' => basename($importPath),
                'error' => $this->safeErrorMessage($e),
                'safety_backup' => $safetyPath ? basename($safetyPath) : null,
            ]);

            return back()->with('error', 'Importul media din fisierul de pe server a esuat. Daca backupul de siguranta a fost creat, acesta a fost pastrat in storage/app/backups/safety/. Detalii: ' . $this->publicImportError($e));
        }
    }

    private function validateDatabaseImportRequest(Request $request): void
    {
        $request->validate([
            'sql_file' => ['required', 'file', 'max:' . $this->maxUploadKilobytes()],
            'understand_db_import' => ['accepted'],
            'db_confirm' => ['required', 'string'],
        ], [
            'sql_file.required' => 'Alege un fisier .sql pentru import.',
            'sql_file.file' => 'Fisierul SQL incarcat nu este valid.',
            'sql_file.max' => 'Fisierul SQL depaseste limita serverului (' . $this->formatBytes($this->maxUploadBytes()) . ').',
            'understand_db_import.accepted' => 'Trebuie sa confirmi ca intelegi efectul importului.',
            'db_confirm.required' => 'Scrie CONFIRM IMPORT pentru a porni importul.',
        ]);

        $file = $request->file('sql_file');

        if (!$file || strtolower($file->getClientOriginalExtension()) !== 'sql') {
            throw ValidationException::withMessages([
                'sql_file' => 'Se accepta doar fisiere cu extensia .sql.',
            ]);
        }

        if ($file->getSize() < 1) {
            throw ValidationException::withMessages([
                'sql_file' => 'Fisierul SQL nu poate fi gol.',
            ]);
        }

        if ($request->input('db_confirm') !== self::DB_CONFIRM_TEXT) {
            throw ValidationException::withMessages([
                'db_confirm' => 'Textul de confirmare trebuie sa fie exact CONFIRM IMPORT.',
            ]);
        }
    }

    private function validateMediaImportRequest(Request $request): void
    {
        $request->validate([
            'media_file' => ['required', 'file', 'max:' . $this->maxUploadKilobytes()],
            'understand_media_import' => ['accepted'],
            'media_confirm' => ['required', 'string'],
        ], [
            'media_file.required' => 'Alege o arhiva .zip pentru import.',
            'media_file.file' => 'Arhiva incarcata nu este valida.',
            'media_file.max' => 'Arhiva depaseste limita serverului (' . $this->formatBytes($this->maxUploadBytes()) . ').',
            'understand_media_import.accepted' => 'Trebuie sa confirmi ca intelegi efectul importului media.',
            'media_confirm.required' => 'Scrie CONFIRM MEDIA IMPORT pentru a porni importul.',
        ]);

        $file = $request->file('media_file');

        if (!$file || strtolower($file->getClientOriginalExtension()) !== 'zip') {
            throw ValidationException::withMessages([
                'media_file' => 'Se accepta doar fisiere cu extensia .zip.',
            ]);
        }

        if ($file->getSize() < 1) {
            throw ValidationException::withMessages([
                'media_file' => 'Arhiva ZIP nu poate fi goala.',
            ]);
        }

        if ($request->input('media_confirm') !== self::MEDIA_CONFIRM_TEXT) {
            throw ValidationException::withMessages([
                'media_confirm' => 'Textul de confirmare trebuie sa fie exact CONFIRM MEDIA IMPORT.',
            ]);
        }
    }

    private function validateServerMediaImportRequest(Request $request): void
    {
        $request->validate([
            'server_media_file' => ['required', 'string'],
            'understand_server_media_import' => ['accepted'],
            'server_media_confirm' => ['required', 'string'],
        ], [
            'server_media_file.required' => 'Alege o arhiva .zip din folderul de import manual.',
            'understand_server_media_import.accepted' => 'Trebuie sa confirmi ca intelegi efectul importului media.',
            'server_media_confirm.required' => 'Scrie CONFIRM MEDIA IMPORT pentru a porni importul.',
        ]);

        try {
            $this->manualMediaImportPath($request->input('server_media_file'));
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'server_media_file' => 'Alege o arhiva .zip valida din folderul de import manual.',
            ]);
        }

        if ($request->input('server_media_confirm') !== self::MEDIA_CONFIRM_TEXT) {
            throw ValidationException::withMessages([
                'server_media_confirm' => 'Textul de confirmare trebuie sa fie exact CONFIRM MEDIA IMPORT.',
            ]);
        }
    }

    private function exportDatabaseToFile(string $filePath): void
    {
        $executable = $this->findExecutable('mysqldump');

        if (!$executable) {
            throw new \RuntimeException('mysqldump_unavailable');
        }

        $database = $this->databaseConnectionConfig();
        File::ensureDirectoryExists(dirname($filePath));

        $command = array_merge([$executable], $this->databaseClientArguments($database), [
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--add-drop-table',
            $database['database'],
        ]);

        $result = $this->runCliProcess($command, [
            'output_file' => $filePath,
            'password' => $database['password'],
        ]);

        if ($result['exit_code'] !== 0 || !is_file($filePath) || filesize($filePath) < 1) {
            @unlink($filePath);
            throw new \RuntimeException('mysqldump_failed: ' . $result['stderr']);
        }
    }

    private function importDatabaseFromFile(string $filePath): void
    {
        $executable = $this->findExecutable('mysql');

        if (!$executable) {
            throw new \RuntimeException('mysql_unavailable');
        }

        $database = $this->databaseConnectionConfig();
        $command = array_merge([$executable], $this->databaseClientArguments($database), [
            $database['database'],
        ]);

        $result = $this->runCliProcess($command, [
            'input_file' => $filePath,
            'password' => $database['password'],
        ]);

        if ($result['exit_code'] !== 0) {
            throw new \RuntimeException('mysql_import_failed: ' . $result['stderr']);
        }
    }

    private function databaseConnectionConfig(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}", []);

        if (($config['driver'] ?? null) !== 'mysql') {
            throw new \RuntimeException('unsupported_database_driver');
        }

        if (empty($config['database']) || empty($config['username'])) {
            throw new \RuntimeException('missing_database_credentials');
        }

        return [
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => $config['port'] ?? 3306,
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => (string) ($config['password'] ?? ''),
            'socket' => $config['unix_socket'] ?? null,
        ];
    }

    private function databaseClientArguments(array $database): array
    {
        $arguments = [
            '--user=' . $database['username'],
            '--default-character-set=utf8mb4',
        ];

        if (!empty($database['socket'])) {
            $arguments[] = '--socket=' . $database['socket'];
        } else {
            $arguments[] = '--host=' . $database['host'];
            $arguments[] = '--port=' . $database['port'];
        }

        return $arguments;
    }

    private function runCliProcess(array $command, array $options = []): array
    {
        $temporaryDirectory = $this->temporaryBackupDirectory();
        $stdoutPath = $options['output_file'] ?? $temporaryDirectory . DIRECTORY_SEPARATOR . 'process-out-' . uniqid('', true) . '.txt';
        $stderrPath = $temporaryDirectory . DIRECTORY_SEPARATOR . 'process-err-' . uniqid('', true) . '.txt';

        $descriptors = [
            0 => isset($options['input_file']) ? ['file', $options['input_file'], 'r'] : ['pipe', 'r'],
            1 => ['file', $stdoutPath, 'w'],
            2 => ['file', $stderrPath, 'w'],
        ];

        $environment = getenv();
        if (!is_array($environment)) {
            $environment = null;
        }

        if (($options['password'] ?? '') !== '') {
            $environment = $environment ?: [];
            $environment['MYSQL_PWD'] = $options['password'];
        }

        $process = proc_open($command, $descriptors, $pipes, base_path(), $environment);

        if (!is_resource($process)) {
            throw new \RuntimeException('process_start_failed');
        }

        if (isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }

        $exitCode = proc_close($process);
        $stderr = is_file($stderrPath) ? trim((string) file_get_contents($stderrPath)) : '';
        $stdout = (!isset($options['output_file']) && is_file($stdoutPath)) ? trim((string) file_get_contents($stdoutPath)) : '';

        if (!isset($options['output_file']) && is_file($stdoutPath)) {
            @unlink($stdoutPath);
        }
        if (is_file($stderrPath)) {
            @unlink($stderrPath);
        }

        return [
            'exit_code' => $exitCode,
            'stdout' => mb_substr($stdout, 0, 1000),
            'stderr' => mb_substr($stderr, 0, 2000),
        ];
    }

    private function createMediaZip(string $zipPath): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('zip_extension_unavailable');
        }

        File::ensureDirectoryExists(dirname($zipPath));

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('zip_create_failed');
        }

        foreach ($this->mediaDirectories() as $archiveRoot => $directory) {
            $zip->addEmptyDir($archiveRoot);

            if (is_dir($directory)) {
                $this->addDirectoryToZip($zip, $directory, $archiveRoot);
            }
        }

        $zip->close();
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $archiveRoot): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = $this->relativePath($directory, $item->getPathname());
            $archivePath = $archiveRoot . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            if ($item->isDir()) {
                $zip->addEmptyDir($archivePath);
                continue;
            }

            if (!$this->isAllowedMediaExtension($item->getFilename())) {
                continue;
            }

            $zip->addFile($item->getPathname(), $archivePath);
        }
    }

    private function validateMediaZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Arhiva ZIP nu poate fi citita.');
        }

        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entryName = $zip->getNameIndex($index);
                if ($entryName === false || str_ends_with($entryName, '/')) {
                    continue;
                }

                $normalized = $this->normalizeZipEntry($entryName);

                if (!$this->isSafeMediaArchiveEntry($normalized)) {
                    throw new \RuntimeException('Arhiva contine fisiere sau cai nepermise: ' . basename($entryName));
                }
            }
        } finally {
            $zip->close();
        }
    }

    private function extractMediaZip(string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Arhiva ZIP nu poate fi citita.');
        }

        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entryName = $zip->getNameIndex($index);
                if ($entryName === false || str_ends_with($entryName, '/')) {
                    continue;
                }

                $normalized = $this->normalizeZipEntry($entryName);
                if (!$this->isSafeMediaArchiveEntry($normalized)) {
                    throw new \RuntimeException('Arhiva contine fisiere sau cai nepermise: ' . basename($entryName));
                }

                $targetPath = $this->publicStorageDirectory() . DIRECTORY_SEPARATOR
                    . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
                $this->ensurePathIsInsideMediaDirectories($targetPath);
                File::ensureDirectoryExists(dirname($targetPath));

                $source = $zip->getStream($entryName);
                if (!$source) {
                    throw new \RuntimeException('Nu se poate citi fisierul din arhiva: ' . basename($entryName));
                }

                $target = fopen($targetPath, 'wb');
                if (!$target) {
                    fclose($source);
                    throw new \RuntimeException('Nu se poate scrie fisierul media: ' . basename($entryName));
                }

                stream_copy_to_stream($source, $target);
                fclose($source);
                fclose($target);
            }
        } finally {
            $zip->close();
        }
    }

    private function isSafeMediaArchiveEntry(string $entry): bool
    {
        $allowedRoot = collect(self::MEDIA_ARCHIVE_ROOTS)
            ->contains(fn (string $root) => str_starts_with($entry, $root . '/'));

        return $allowedRoot
            && !str_contains($entry, '../')
            && !str_contains($entry, '/..')
            && !str_starts_with($entry, '/')
            && !preg_match('/^[a-zA-Z]:/', $entry)
            && $this->isAllowedMediaExtension($entry);
    }

    private function normalizeZipEntry(string $entry): string
    {
        $entry = str_replace('\\', '/', $entry);
        $parts = [];

        foreach (explode('/', $entry) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            $parts[] = $part;
        }

        return implode('/', $parts);
    }

    private function emptyMediaDirectories(): void
    {
        foreach ($this->mediaDirectories() as $directory) {
            File::ensureDirectoryExists($directory);
            $this->ensurePathIsInside($directory, $this->publicStorageDirectory());

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    @rmdir($item->getPathname());
                } else {
                    @unlink($item->getPathname());
                }
            }
        }
    }

    private function ensureMediaDirectoriesExist(): void
    {
        foreach ($this->mediaDirectories() as $directory) {
            File::ensureDirectoryExists($directory);
        }
    }

    private function storeUploadedImportFile($uploadedFile, string $extension): string
    {
        $directory = storage_path('app/backups/imports');
        File::ensureDirectoryExists($directory);

        $fileName = 'import-' . now()->format('Y-m-d-H-i-s') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $uploadedFile->move($directory, $fileName);

        return $directory . DIRECTORY_SEPARATOR . $fileName;
    }

    private function manualMediaImportPath(string $fileName): string
    {
        $fileName = str_replace('\\', '/', trim($fileName));

        if ($fileName === '' || $fileName !== basename($fileName) || strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'zip') {
            throw new \RuntimeException('manual_media_file_invalid');
        }

        $path = $this->manualMediaImportDirectory() . DIRECTORY_SEPARATOR . $fileName;
        $this->ensurePathIsInside($path, $this->manualMediaImportDirectory());

        if (!is_file($path)) {
            throw new \RuntimeException('manual_media_file_missing');
        }

        if (filesize($path) < 1) {
            throw new \RuntimeException('manual_media_file_empty');
        }

        return $path;
    }

    private function manualMediaImportFiles(): array
    {
        $directory = $this->manualMediaImportDirectory();
        $files = [];

        foreach (File::files($directory) as $file) {
            if (strtolower($file->getExtension()) !== 'zip' || $file->getSize() < 1) {
                continue;
            }

            $files[] = [
                'name' => $file->getFilename(),
                'size' => $this->formatBytes($file->getSize()),
                'modified_at' => date('Y-m-d H:i', $file->getMTime()),
                'timestamp' => $file->getMTime(),
            ];
        }

        usort($files, fn (array $a, array $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_slice($files, 0, 50);
    }

    private function findExecutable(string $name): ?string
    {
        $fileNames = PHP_OS_FAMILY === 'Windows' ? [$name . '.exe', $name] : [$name];
        $candidates = [];

        foreach (explode(PATH_SEPARATOR, getenv('PATH') ?: '') as $directory) {
            foreach ($fileNames as $fileName) {
                $candidates[] = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $laragonCandidates = glob('C:\\laragon\\bin\\mysql\\*\\bin\\' . $name . '.exe') ?: [];
            rsort($laragonCandidates, SORT_NATURAL);

            foreach ($laragonCandidates as $path) {
                $candidates[] = $path;
            }
        } else {
            foreach (['/usr/bin', '/usr/local/bin', '/bin'] as $directory) {
                $candidates[] = $directory . DIRECTORY_SEPARATOR . $name;
            }
        }

        foreach (array_unique($candidates) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isAllowedMediaExtension(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::ALLOWED_MEDIA_EXTENSIONS, true);
    }

    private function ensurePathIsInsideMediaDirectories(string $path): void
    {
        foreach ($this->mediaDirectories() as $directory) {
            try {
                $this->ensurePathIsInside($path, $directory);

                return;
            } catch (Throwable) {
                continue;
            }
        }

        throw new \RuntimeException('Calea tinta nu este permisa.');
    }

    private function ensurePathIsInside(string $path, string $baseDirectory): void
    {
        $base = $this->normalizeFilesystemPath($baseDirectory);
        $target = $this->normalizeFilesystemPath($path);

        if (!str_starts_with($target, $base . DIRECTORY_SEPARATOR) && $target !== $base) {
            throw new \RuntimeException('Calea tinta nu este permisa.');
        }
    }

    private function normalizeFilesystemPath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $segments = [];

        foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            $segments[] = $segment;
        }

        $prefix = str_starts_with($path, DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';

        return $prefix . implode(DIRECTORY_SEPARATOR, $segments);
    }

    private function relativePath(string $baseDirectory, string $path): string
    {
        $base = rtrim($this->normalizeFilesystemPath($baseDirectory), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $target = $this->normalizeFilesystemPath($path);

        return ltrim(str_replace($base, '', $target), DIRECTORY_SEPARATOR);
    }

    private function mediaDirectories(): array
    {
        return [
            'services' => storage_path('app/public/services'),
            'dealers' => storage_path('app/public/dealers'),
        ];
    }

    private function publicStorageDirectory(): string
    {
        return storage_path('app/public');
    }

    private function safetyBackupDirectory(): string
    {
        $directory = storage_path('app/backups/safety');
        File::ensureDirectoryExists($directory);

        return $directory;
    }

    private function manualMediaImportDirectory(): string
    {
        $directory = storage_path('app/backups/imports/manual');
        File::ensureDirectoryExists($directory);

        return $directory;
    }

    private function temporaryBackupDirectory(): string
    {
        $directory = storage_path('app/backups/tmp');
        File::ensureDirectoryExists($directory);

        return $directory;
    }

    private function maxUploadKilobytes(): int
    {
        return max(1, (int) floor($this->maxUploadBytes() / 1024));
    }

    private function maxUploadBytes(): int
    {
        $uploadMax = $this->iniBytes((string) ini_get('upload_max_filesize'));
        $postMax = $this->iniBytes((string) ini_get('post_max_size'));

        $limits = array_filter([$uploadMax, $postMax], fn (int $value) => $value > 0);

        return $limits ? min($limits) : 0;
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return round($bytes / (1024 * 1024 * 1024), 1) . ' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    private function publicDatabaseExportError(Throwable $e): string
    {
        return match ($e->getMessage()) {
            'mysqldump_unavailable' => 'mysqldump nu este disponibil pe server. Exportul bazei de date nu poate fi realizat automat.',
            'unsupported_database_driver' => 'Exportul automat este disponibil doar pentru conexiuni MySQL/MariaDB.',
            default => 'Exportul bazei de date nu a putut fi realizat. Verifica disponibilitatea mysqldump si permisiunile serverului.',
        };
    }

    private function publicImportError(Throwable $e): string
    {
        return match ($e->getMessage()) {
            'mysqldump_unavailable' => 'mysqldump nu este disponibil pe server.',
            'mysql_unavailable' => 'Restaurarea automata necesita mysql CLI pe server.',
            'unsupported_database_driver' => 'Operatiunea automata este disponibila doar pentru conexiuni MySQL/MariaDB.',
            'zip_extension_unavailable' => 'Extensia PHP ZipArchive nu este disponibila.',
            'manual_media_file_invalid' => 'Alege o arhiva .zip valida din folderul de import manual.',
            'manual_media_file_missing' => 'Arhiva selectata nu mai exista in folderul de import manual.',
            'manual_media_file_empty' => 'Arhiva selectata este goala.',
            default => 'Verifica fisierul incarcat si permisiunile serverului.',
        };
    }

    private function safeErrorMessage(Throwable $e): string
    {
        return mb_substr($e->getMessage(), 0, 500);
    }

    private function logBackupEvent(string $operation, string $result, array $context = []): void
    {
        Log::info('Admin backup operation', array_merge([
            'operation' => $operation,
            'result' => $result,
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()?->email,
            'timestamp' => now()->toIso8601String(),
        ], $context));
    }
}
