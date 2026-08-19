export function mountTableSearch(): void {
  const table = document.querySelector<HTMLTableElement>('#applications-table');
  if (!table) return;

  const bar = document.querySelector('.filter-bar');
  if (!bar) return;

  const input = document.createElement('input');
  input.type = 'search';
  input.placeholder = 'Search candidates…';
  input.className = 'table-search';
  bar.appendChild(input);

  const rows = Array.from(table.querySelectorAll<HTMLTableRowElement>('tbody tr'));

  input.addEventListener('input', () => {
    const query = input.value.trim().toLowerCase();
    for (const row of rows) {
      const matches = row.textContent?.toLowerCase().includes(query) ?? true;
      row.style.display = matches ? '' : 'none';
    }
  });
}
