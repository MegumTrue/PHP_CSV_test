<?php

class FeedbackModel {
    private CsvDatabase $db;

    public function __construct(CsvDatabase $db) {
        $this->db = $db;
    }

    public function clientExists(int $clientId): bool {
        return $this->db->clientExists($clientId);
    }

    public function saveFeedback(int $clientId, int $rating, ?string $comment): bool {
        $data = [
            'client_id' => $clientId,
            'rating' => $rating,
            'comment' => $comment ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->write('feedbacks.csv', $data);
    }
}