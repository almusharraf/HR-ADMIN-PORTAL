<?php
declare(strict_types=1);

final class ApplicationRepository
{
    public static function findByJob(int $jobId, string $sort = 'score_desc', ?string $reviewStatus = null): array
    {
        $where = 'WHERE job_id = ?';
        $params = [$jobId];

        if ($reviewStatus !== null && $reviewStatus !== '') {
            $where .= ' AND review_status = ?';
            $params[] = $reviewStatus;
        }

        $order = match ($sort) {
            'newest' => 'created_at DESC',
            'oldest' => 'created_at ASC',
            'name' => 'full_name ASC',
            default => 'ai_score IS NULL, ai_score DESC, created_at DESC',
        };

        $stmt = db()->prepare("SELECT * FROM applications {$where} ORDER BY {$order}");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function topByJob(int $jobId, int $limit = 10): array
    {
        $stmt = db()->prepare(
            'SELECT * FROM applications
             WHERE job_id = ? AND ai_score IS NOT NULL
             ORDER BY ai_score DESC
             LIMIT ' . max(1, $limit)
        );
        $stmt->execute([$jobId]);

        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM applications WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $application = $stmt->fetch();

        return $application ?: null;
    }

    public static function unscoredByJob(int $jobId): array
    {
        $stmt = db()->prepare('SELECT * FROM applications WHERE job_id = ? AND ai_score IS NULL');
        $stmt->execute([$jobId]);

        return $stmt->fetchAll();
    }

    public static function setScore(int $id, int $score, string $rationale): void
    {
        $stmt = db()->prepare(
            'UPDATE applications SET ai_score = ?, ai_rationale = ?, scored_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$score, $rationale, $id]);
    }

    public static function setReviewStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE applications SET review_status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public static function counts(): array
    {
        $stmt = db()->query(
            "SELECT job_id, COUNT(*) AS total,
                    SUM(ai_score IS NULL) AS unscored,
                    SUM(review_status = 'new') AS new_count
             FROM applications GROUP BY job_id"
        );

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int) $row['job_id']] = $row;
        }

        return $result;
    }
}
