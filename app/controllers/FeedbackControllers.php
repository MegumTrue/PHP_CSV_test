<?php

class FeedbackController {
    private FeedbackModel $model;

    public function __construct(FeedbackModel $model) {
        $this->model = $model;
    }

    public function handle(int $clientId): string {
        // Проверка существования клиента
        if (!$this->model->clientExists($clientId)) {
            return $this->renderView('invalid_client');
        }

        // Обработка формы
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rating = (int)($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');
            
            // Валидация
            if ($rating >= 1 && $rating <= 5) {
                // Сохранение отзыва
                $this->model->saveFeedback($clientId, $rating, $comment);
                return $this->renderView('success');
            }
        }

        // Отображение формы
        return $this->renderView('feedback_form');
    }

    private function renderView(string $viewName): string {
        ob_start();
        include __DIR__ . "/../views/{$viewName}.php";
        return ob_get_clean();
    }
}