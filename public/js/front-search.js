document.addEventListener('DOMContentLoaded', function () {
  const searchForm = document.querySelector('.topbar-search');
  if (!searchForm) {
    return;
  }

  const searchInput = searchForm.querySelector('input[type="search"]');
  const searchButton = searchForm.querySelector('button');
  const table = document.querySelector('.fw-table');
  if (!searchInput || !searchButton || !table) {
    return;
  }

  const getColumnIndex = () => {
    const pathname = window.location.pathname.toLowerCase();

    if (pathname.includes('journal-alimentaire.php')) {
      return 2; // colonne Aliment
    }

    if (pathname.includes('suivi-sante-unifie.php')) {
      return 0; // colonne Type d'activité
    }

    return null;
  };

  const columnIndex = getColumnIndex();
  const tbody = table.querySelector('tbody');
  if (!tbody) {
    return;
  }

  const applySearch = () => {
    const query = searchInput.value.trim().toLowerCase();
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.forEach((row) => {
      const cells = row.querySelectorAll('td');
      if (cells.length === 0) {
        return;
      }

      let textToSearch = '';
      if (columnIndex !== null && cells[columnIndex]) {
        textToSearch = cells[columnIndex].textContent.trim().toLowerCase();
      } else {
        textToSearch = Array.from(cells).map((cell) => cell.textContent.trim().toLowerCase()).join(' ');
      }

      row.style.display = !query || textToSearch.includes(query) ? '' : 'none';
    });
  };

  searchButton.addEventListener('click', applySearch);
  searchInput.addEventListener('input', applySearch);
});