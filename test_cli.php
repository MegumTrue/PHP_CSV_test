<?php

// Имитация веб-запроса
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['client_id'] = '1';
$_POST = [
    'rating' => '4',
    'comment' => 'Тестовый отзыв из CLI'
];

// Подключение файлов
require __DIR__ . '/app/config.php';
require __DIR__ . '/app/core/CsvDatabase.php';
require __DIR__ . '/app/models/FeedbackModel.php';
require __DIR__ . '/app/controllers/FeedbackController.php';

// Конфигурация
$config = require __DIR__ . '/app/config.php';

// Инициализация
$csvDb = new CsvDatabase($config['csv_path']);
$model = new FeedbackModel($csvDb);
$controller = new FeedbackController($model);

// Выполнение
echo $controller->handle((int)$_GET['client_id']);

echo "\n\nПроверьте файл feedbacks.csv в папке data\n";