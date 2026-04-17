<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Log;

final class GoogleDriveImageService
{
    public function uploadFromDataUrl(string $dataUrl, string $filenameStem): ?string
    {
        if (! config('services.google_drive.enabled')) {
            return null;
        }

        $credPath = config('services.google_drive.credentials_path');
        $folderId = (string) config('services.google_drive.folder_id', '');

        if ($folderId === '' || ! is_string($credPath) || $credPath === '' || ! is_readable($credPath)) {
            Log::warning('Google Drive: missing folder_id, credentials_path, or unreadable credentials file.');

            return null;
        }

        if (! preg_match('#^data:image/[\w+.-]+;base64,(.+)$#i', $dataUrl, $m)) {
            return null;
        }

        $binary = base64_decode($m[1], true);
        if ($binary === false || $binary === '') {
            return null;
        }

        $safeStem = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $filenameStem);
        $safeStem = trim((string) $safeStem, '-') ?: 'photo';
        $name = $safeStem.'-'.uniqid('', true).'.jpg';

        try {
            $client = new Client;
            $client->setAuthConfig($credPath);
            $client->setScopes([Drive::DRIVE_FILE]);

            $drive = new Drive($client);
            $meta = new DriveFile([
                'name' => $name,
                'parents' => [$folderId],
            ]);

            $created = $drive->files->create($meta, [
                'data' => $binary,
                'mimeType' => 'image/jpeg',
                'uploadType' => 'multipart',
                'fields' => 'id',
                'supportsAllDrives' => true,
            ]);

            $id = $created->getId();
            if (! $id) {
                return null;
            }

            if (config('services.google_drive.make_files_public', true)) {
                try {
                    $perm = new Permission([
                        'type' => 'anyone',
                        'role' => 'reader',
                    ]);
                    $drive->permissions->create($id, $perm, ['supportsAllDrives' => true]);
                } catch (\Throwable $e) {
                    Log::warning('Google Drive: could not set public read permission: '.$e->getMessage());
                }
            }

            return 'https://drive.google.com/uc?export=view&id='.$id;
        } catch (\Throwable $e) {
            Log::warning('Google Drive upload failed: '.$e->getMessage());

            return null;
        }
    }
}
