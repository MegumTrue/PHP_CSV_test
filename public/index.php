<?php

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/core/CsvDatabase.php';
require_once __DIR__ . '/../app/models/FeedbackModel.php';

// Конфигурация
$config = require __DIR__ . '/../app/config.php';

// Получение client_id из URL
$clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

try {
    // Инициализация базы данных CSV
    $csvDb = new CsvDatabase($config['csv_path']);
    
    // Инициализация модели
    $model = new FeedbackModel($csvDb);
    
    // Обработка запроса
    echo handleFeedbackRequest($model, $clientId);
    
} catch (Exception $e) {
    // Обработка ошибок
    http_response_code(500);
    echo "<div class='container'><h1>Ошибка</h1><p>Произошла ошибка: " . htmlspecialchars($e->getMessage()) . "</p></div>";
}

/**
 * Обработчик запроса для системы отзывов
 */
function handleFeedbackRequest(FeedbackModel $model, int $clientId): string {
    // Проверка существования клиента
    if (!$model->clientExists($clientId)) {
        return renderView('invalid_client');
    }

    // Обработка формы
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        // Валидация
        if ($rating >= 1 && $rating <= 5) {
            // Сохранение отзыва
            $model->saveFeedback($clientId, $rating, $comment);
            return renderView('success');
        }
    }

    // Отображение формы
    return renderView('feedback_form');
}

/**
 * Рендеринг представления
 */
function renderView(string $viewName): string {
    ob_start();
    include __DIR__ . "/../app/views/{$viewName}.php";
    return ob_get_clean();
}