<!DOCTYPE html>
<html>
<head>
    <title>Оставьте отзыв</title>
    <style>
        .rating { display: flex; gap: 10px; margin: 20px 0; }
        .rating-btn { 
            padding: 10px 15px; 
            font-size: 16px; 
            cursor: pointer;
            border: 1px solid #ccc;
            background: #f8f8f8;
        }
        .rating-btn.active { background: #4CAF50; color: white; }
        textarea { width: 100%; max-width: 500px; height: 100px; margin: 10px 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Оцените качество обслуживания</h1>
        <form method="POST">
            <div class="rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button 
                        type="button" 
                        class="rating-btn" 
                        onclick="setRating(<?= $i ?>)"
                    >
                        <?= $i ?>
                    </button>
                <?php endfor; ?>
                <input type="hidden" name="rating" id="rating" required>
            </div>
            
            <div>
                <label for="comment">Комментарий (не обязательно):</label><br>
                <textarea name="comment" id="comment"></textarea>
            </div>
            
            <button type="submit">Отправить отзыв</button>
        </form>
    </div>

    <script>
        function setRating(rating) {
            document.querySelectorAll('.rating-btn').forEach(btn => {
                btn.classList.remove('active');
                if (parseInt(btn.textContent) <= rating) {
                    btn.classList.add('active');
                }
            });
            document.getElementById('rating').value = rating;
        }
    </script>
</body>
</html>