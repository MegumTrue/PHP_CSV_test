<?php

class FeedbackSystem {
    private $csvPath;
    private $clientId;
    private $debug = true;

    public function __construct($clientId) {
        $this->clientId = (int)$clientId;
        $this->csvPath = __DIR__ . '/data';
        
        // Автоматическое создание папки data
        if (!is_dir($this->csvPath)) {
            mkdir($this->csvPath, 0777, true);
        }
    }

    public function run() {
        try {
            // Проверка существования клиента
            $clientExists = $this->clientExists();
            
            if (!$clientExists) {
                return $this->renderInvalidClient();
            }

            // Обработка формы
            $showSuccess = false;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $rating = (int)($_POST['rating'] ?? 0);
                $comment = trim($_POST['comment'] ?? '');
                
                if ($rating >= 1 && $rating <= 5) {
                    $this->saveFeedback($rating, $comment);
                    $showSuccess = true;
                }
            }

            // Отображение формы или сообщения об успехе
            return $this->renderForm($showSuccess);
            
        } catch (Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    private function clientExists() {
        $clients = $this->readCsv('clients.csv');
        $found = false;
        
        foreach ($clients as $client) {
            // Пробуем разные варианты ключей
            $id = $client['id'] ?? $client['ID'] ?? $client['Id'] ?? null;
            
            // Пробуем преобразовать ID в число
            if ($id !== null) {
                $id = is_numeric($id) ? (int)$id : trim($id);
                
                if ($id === $this->clientId) {
                    $found = true;
                    break;
                }
            }
        }
        
        return $found;
    }

    private function saveFeedback($rating, $comment) {
        $data = [
            'client_id' => $this->clientId,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->writeCsv('feedbacks.csv', $data);
    }

    private function readCsv($filename) {
        $filepath = $this->csvPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [];
        }
        
        $rows = [];
        $handle = fopen($filepath, 'r');
        if ($handle) {
            // Пропуск BOM (UTF-8 маркер)
            if (fgets($handle, 4) !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            
            // Чтение заголовков
            $header = $this->safeFgetcsv($handle);
            
            while (($data = $this->safeFgetcsv($handle)) !== false) {
                if ($header && count($header) === count($data)) {
                    $rows[] = array_combine($header, $data);
                }
            }
            fclose($handle);
        }
        return $rows;
    }

    private function writeCsv($filename, $data) {
        $filepath = $this->csvPath . '/' . $filename;
        $handle = fopen($filepath, 'a');
        
        if (!$handle) {
            return false;
        }
        
        // Добавляем заголовки если файл новый
        if (!file_exists($filepath) || filesize($filepath) === 0) {
            fwrite($handle, "\xEF\xBB\xBF"); // Добавляем BOM для UTF-8
            $this->safeFputcsv($handle, array_keys($data));
        }
        
        $success = $this->safeFputcsv($handle, $data);
        fclose($handle);
        
        return $success !== false;
    }

    // Безопасное чтение CSV с поддержкой PHP 8.3+
    private function safeFgetcsv($handle) {
        if (version_compare(PHP_VERSION, '8.3.0') >= 0) {
            return fgetcsv($handle, 0, ',', '"', '\\');
        } else {
            return fgetcsv($handle, 0, ',', '"');
        }
    }

    // Безопасная запись CSV с поддержкой PHP 8.3+
    private function safeFputcsv($handle, $fields) {
        if (version_compare(PHP_VERSION, '8.3.0') >= 0) {
            return fputcsv($handle, $fields, ',', '"', '\\');
        } else {
            return fputcsv($handle, $fields, ',', '"');
        }
    }

    private function renderForm($showSuccess = false) {
        // Текущие значения для сохранения в форме
        $rating = $_POST['rating'] ?? '';
        $comment = $_POST['comment'] ?? '';
        
        ob_start(); ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Оставьте отзыв</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background-color: #f4f4f4;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                h1 {
                    color: #333;
                    margin-top: 0;
                    text-align: center;
                }
                .rating {
                    display: flex;
                    justify-content: center;
                    gap: 10px;
                    margin: 20px 0;
                    flex-wrap: wrap;
                }
                .rating-btn {
                    padding: 12px 18px;
                    font-size: 18px;
                    cursor: pointer;
                    border: 1px solid #ddd;
                    background: #f8f8f8;
                    border-radius: 4px;
                    transition: all 0.2s;
                    min-width: 50px;
                    text-align: center;
                }
                .rating-btn:hover {
                    background: #e8e8e8;
                }
                .rating-btn.active {
                    background: #4CAF50;
                    color: white;
                    border-color: #4CAF50;
                }
                textarea {
                    width: 100%;
                    height: 120px;
                    margin: 15px 0;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 16px;
                    resize: vertical;
                    box-sizing: border-box;
                }
                .submit-btn {
                    padding: 12px 25px;
                    background-color: #007bff;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 16px;
                    display: block;
                    width: 100%;
                    transition: background-color 0.3s;
                }
                .submit-btn:hover {
                    background-color: #0069d9;
                }
                .success-message {
                    background-color: #dff0d8;
                    border: 1px solid #d0e9c6;
                    border-radius: 4px;
                    padding: 15px;
                    margin-bottom: 20px;
                    text-align: center;
                    display: <?= $showSuccess ? 'block' : 'none' ?>;
                }
                .new-feedback-btn {
                    padding: 10px 20px;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 16px;
                    margin-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Оцените качество обслуживания</h1>
                
                <div class="success-message" id="success-message" style="<?= $showSuccess ? 'display:block;' : 'display:none;' ?>">
                    <h2>Спасибо за ваш отзыв!</h2>
                    <p>Ваша оценка успешно сохранена.</p>
                    <button class="new-feedback-btn" onclick="resetForm()">Оставить новый отзыв</button>
                </div>
                
                <form method="POST" id="feedback-form" style="<?= $showSuccess ? 'display:none;' : 'display:block;' ?>">
                    <div class="rating">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button 
                                type="button" 
                                class="rating-btn <?= $rating == $i ? 'active' : '' ?>" 
                                onclick="setRating(<?= $i ?>)"
                            >
                                <?= $i ?>
                            </button>
                        <?php endfor; ?>
                        <input type="hidden" name="rating" id="rating-input" value="<?= $rating ?>" required>
                    </div>
                    
                    <div>
                        <label for="comment">Комментарий (не обязательно):</label><br>
                        <textarea name="comment" id="comment" placeholder="Ваши впечатления..."><?= htmlspecialchars($comment) ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">Отправить отзыв</button>
                </form>
            </div>

            <script>
                function setRating(rating) {
                    document.querySelectorAll('.rating-btn').forEach(btn => {
                        btn.classList.remove('active');
                        if (parseInt(btn.textContent) === rating) {
                            btn.classList.add('active');
                        }
                    });
                    document.getElementById('rating-input').value = rating;
                }
                
                function resetForm() {
                    document.getElementById('rating-input').value = '';
                    document.querySelectorAll('.rating-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    document.getElementById('comment').value = '';
                    document.getElementById('success-message').style.display = 'none';
                    document.getElementById('feedback-form').style.display = 'block';
                }
            </script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function renderInvalidClient() {
        $clients = $this->readCsv('clients.csv');
        
        ob_start(); ?>
        <div class="container">
            <h1>Ссылка на голосование недоступна</h1>
            <p>Пожалуйста, свяжитесь с нами по телефону для уточнения деталей.</p>
            
            <div style="background:#f0f0f0;padding:15px;margin-top:20px;border-radius:5px;">
                <h3>Отладочная информация:</h3>
                <p>Запрошенный Client ID: <strong><?= $this->clientId ?></strong></p>
                <p>Путь к данным: <?= $this->csvPath ?></p>
                <p>Файл clients.csv: <?= file_exists($this->csvPath . '/clients.csv') ? 'существует' : 'НЕ НАЙДЕН' ?></p>
                <p>Файл feedbacks.csv: <?= file_exists($this->csvPath . '/feedbacks.csv') ? 'существует' : 'НЕ НАЙДЕН' ?></p>
                
                <h4>Клиенты в системе:</h4>
                <?php if (empty($clients)): ?>
                    <p>В системе нет ни одного клиента</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($clients as $client): ?>
                            <li>
                                ID: <?= $client['id'] ?? $client['ID'] ?? $client['Id'] ?? 'N/A' ?>,
                                Имя: <?= $client['name'] ?? $client['Name'] ?? $client['NAME'] ?? 'N/A' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <h4>Проверка доступа:</h4>
                <p>Директория доступна для записи: <?= is_writable($this->csvPath) ? 'Да' : 'НЕТ' ?></p>
                <p>Файл clients.csv доступен для чтения: <?= is_readable($this->csvPath . '/clients.csv') ? 'Да' : 'НЕТ' ?></p>
            </div>
        </div>
        <style>
            .container {
                max-width: 800px;
                margin: 30px auto;
                padding: 25px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            }
            h1 {
                color: #d32f2f;
                text-align: center;
            }
            h3, h4 {
                color: #333;
                border-bottom: 1px solid #eee;
                padding-bottom: 5px;
            }
            ul {
                list-style-type: none;
                padding: 0;
            }
            li {
                padding: 5px 0;
                border-bottom: 1px solid #f0f0f0;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    private function renderError($message) {
        return "<div style='padding:20px;border:1px solid red;margin:20px;border-radius:5px;'>
                <h2>Ошибка системы</h2>
                <p>$message</p>
                <p>Пожалуйста, попробуйте позже или свяжитесь с поддержкой.</p>
            </div>";
    }
}