export interface ScoreJobResult {
  ok: boolean;
  scored: number;
  failed: number;
}

export async function scoreJob(jobId: number): Promise<ScoreJobResult> {
  const res = await fetch('/api/score.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ job_id: jobId }),
  });

  const data = await res.json();
  if (!res.ok) {
    throw new Error(data.error ?? 'Scoring failed');
  }

  return data as ScoreJobResult;
}
