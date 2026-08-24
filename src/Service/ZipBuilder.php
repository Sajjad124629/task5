<?php

namespace App\Service;

use ZipArchive;

class ZipBuilder
{
    private array $files = [];

    public function addFile(string $filename, string $data): void
    {
        $this->files[] = [
            'name' => $filename,
            'data' => $data,
        ];
    }

    public function getZipContent(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'zip_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($this->files as $file) {
                $zip->addFromString($file['name'], $file['data']);
            }
            $zip->close();
            $content = file_get_contents($tempFile);
            @unlink($tempFile);
            return $content;
        }

        return '';
    }
}
