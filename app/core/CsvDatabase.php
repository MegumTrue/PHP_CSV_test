<?php

class CsvDatabase {
    private string $path;

    public function __construct(string $path) {
        $this->path = $path;
    }

    public function read(string $filename): array {
        $filepath = $this->path . '/' . $filename;
        if (!file_exists($filepath)) {
            return [];
        }
        
        $rows = [];
        $handle = fopen($filepath, 'r');
        if ($handle !== false) {
            $header = fgetcsv($handle);
            if ($header !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $rows[] = array_combine($header, $data);
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    public function write(string $filename, array $data): bool {
        $filepath = $this->path . '/' . $filename;
        
        // Создаем директорию, если её нет
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
        
        $handle = fopen($filepath, 'a');
        if ($handle === false) {
            return false;
        }
        
        // Если файл пустой, добавляем заголовки
        if (filesize($filepath) === 0) {
            fputcsv($handle, array_keys($data));
        }
        
        $success = fputcsv($handle, $data);
        fclose($handle);
        return $success !== false;
    }

    public function clientExists(int $clientId): bool {
        $clients = $this->read('clients.csv');
        foreach ($clients as $client) {
            if ((int)$client['id'] === $clientId) {
                return true;
            }
        }
        return false;
    }
}