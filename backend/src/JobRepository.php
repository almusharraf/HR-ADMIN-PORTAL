<?php
declare(strict_types=1);

final class JobRepository
{
    public static function findAll(): array
    {
        $stmt = db()->query(
            'SELECT j.*,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS application_count
             FROM jobs j
             ORDER BY j.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM jobs WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $job = $stmt->fetch();

        return $job ?: null;
    }

    public static function findByUuid(string $uuid): ?array
    {
        $stmt = db()->prepare('SELECT * FROM jobs WHERE job_uuid = ? LIMIT 1');
        $stmt->execute([$uuid]);
        $job = $stmt->fetch();

        return $job ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO jobs (job_uuid, title, location, department, employment_type, description, requirements, status)
             VALUES (:job_uuid, :title, :location, :department, :employment_type, :description, :requirements, :status)'
        );
        $stmt->execute([
            'job_uuid' => self::uuid4(),
            'title' => $data['title'],
            'location' => $data['location'],
            'department' => $data['department'] ?: null,
            'employment_type' => $data['employment_type'] ?: null,
            'description' => $data['description'],
            'requirements' => $data['requirements'] ?: null,
            'status' => $data['status'],
        ]);

        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = db()->prepare(
            'UPDATE jobs SET title = :title, location = :location, department = :department,
                    employment_type = :employment_type, description = :description,
                    requirements = :requirements, status = :status
             WHERE id = :id'
        );
        $stmt->execute([
            'title' => $data['title'],
            'location' => $data['location'],
            'department' => $data['department'] ?: null,
            'employment_type' => $data['employment_type'] ?: null,
            'description' => $data['description'],
            'requirements' => $data['requirements'] ?: null,
            'status' => $data['status'],
            'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM jobs WHERE id = ?');
        $stmt->execute([$id]);
    }

    private static function uuid4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
