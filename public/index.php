<?php
// Включение отладки
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Подключение системы отзывов
require_once __DIR__ . '/../FeedbackSystem.php';

// Получение ID клиента
$clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

// Проверка валидности ID клиента
if ($clientId <= 0) {
    echo "<div style='padding:20px;border:1px solid red;margin:20px;border-radius:5px;'>
            <h2>Ошибка в запросе</h2>
            <p>Не указан или неверный идентификатор клиента.</p>
            <p>Пожалуйста, используйте корректную ссылку.</p>
        </div>";
    exit;
}

// Создание и запуск системы
try {
    $feedbackSystem = new FeedbackSystem($clientId);
    echo $feedbackSystem->run();
} catch (Exception $e) {
    echo "<div style='padding:20px;border:1px solid red;margin:20px;border-radius:5px;'>
            <h2>Критическая ошибка</h2>
            <p>{$e->getMessage()}</p>
            <p>Пожалуйста, свяжитесь с технической поддержкой.</p>
        </div>";
}