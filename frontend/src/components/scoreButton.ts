import { scoreJob } from '../api';

export function mountScoreButton(): void {
  const button = document.querySelector<HTMLButtonElement>('#score-job-btn');
  const status = document.querySelector<HTMLDivElement>('#score-status');
  if (!button) return;

  button.addEventListener('click', async () => {
    const jobId = Number(button.dataset.jobId);
    if (!jobId) return;

    button.disabled = true;
    const originalLabel = button.textContent;
    button.textContent = 'Scoring…';
    if (status) {
      status.style.display = 'block';
      status.className = 'alert alert-info';
      status.textContent = 'Contacting Anthropic to score unscored applicants…';
    }

    try {
      const result = await scoreJob(jobId);
      if (status) {
        status.className = 'alert alert-success';
        status.textContent = `Scored ${result.scored} application(s)${result.failed ? `, ${result.failed} failed` : ''}. Reloading…`;
      }
      setTimeout(() => window.location.reload(), 900);
    } catch (err) {
      button.disabled = false;
      button.textContent = originalLabel;
      if (status) {
        status.className = 'alert alert-error';
        status.textContent = err instanceof Error ? err.message : 'Scoring failed';
      }
    }
  });
}
