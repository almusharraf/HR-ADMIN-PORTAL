<?php
declare(strict_types=1);

final class CandidateScorer
{
    public static function scoreJob(int $jobId): array
    {
        $job = JobRepository::findById($jobId);
        if (!$job) {
            throw new InvalidArgumentException('Job not found');
        }

        if (!AnthropicClient::isConfigured()) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured');
        }

        $client = new AnthropicClient((string) config('ANTHROPIC_API_KEY'));
        $model = config('ANTHROPIC_SCORE_MODEL', 'claude-sonnet-5');

        $results = ['scored' => 0, 'failed' => 0];

        foreach (ApplicationRepository::unscoredByJob($jobId) as $application) {
            try {
                [$score, $rationale] = self::scoreOne($client, $model, $job, $application);
                ApplicationRepository::setScore((int) $application['id'], $score, $rationale);
                $results['scored']++;
            } catch (Throwable) {
                $results['failed']++;
            }
        }

        return $results;
    }

    private static function scoreOne(AnthropicClient $client, string $model, array $job, array $application): array
    {
        $profile = [
            'full_name' => $application['full_name'],
            'education_level' => $application['education_level'],
            'institution' => $application['institution'],
            'specialization' => $application['specialization'],
            'status' => $application['status'],
            'experience' => json_decode((string) $application['experience_json'], true),
            'education' => json_decode((string) $application['education_json'], true),
        ];

        $system = 'You are an HR recruiting assistant scoring how well a candidate fits a job. '
            . 'Respond ONLY with strict JSON: {"score": <integer 0-100>, "rationale": "<2-3 sentence explanation>"}. '
            . 'Score based on fit between the candidate profile and the job requirements. Be honest and calibrated.';

        $user = "Job title: {$job['title']}\n"
            . "Job description: {$job['description']}\n"
            . "Job requirements: {$job['requirements']}\n\n"
            . 'Candidate profile (JSON): ' . json_encode($profile);

        $raw = $client->messages($model, [['role' => 'user', 'content' => $user]], 512, $system);

        $parsed = json_decode(trim($raw), true);
        if (!is_array($parsed) || !isset($parsed['score'])) {
            preg_match('/\{.*\}/s', $raw, $m);
            $parsed = $m ? json_decode($m[0], true) : null;
        }

        if (!is_array($parsed) || !isset($parsed['score'])) {
            throw new RuntimeException('Could not parse AI scoring response');
        }

        $score = max(0, min(100, (int) $parsed['score']));
        $rationale = (string) ($parsed['rationale'] ?? '');

        return [$score, $rationale];
    }
}
