// table-interactions.js - Gestion du tri et du filtrage pour les tables

document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour trier la table
    function sortTable(table, columnIndex, ascending) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const aText = a.cells[columnIndex].textContent.trim();
            const bText = b.cells[columnIndex].textContent.trim();

            // Essayer de parser comme nombre
            const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
            const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));

            if (!isNaN(aNum) && !isNaN(bNum)) {
                return ascending ? aNum - bNum : bNum - aNum;
            }

            // Sinon, tri alphabétique
            return ascending ? aText.localeCompare(bText) : bText.localeCompare(aText);
        });

        // Réorganiser les lignes
        rows.forEach(row => tbody.appendChild(row));
    }

    // Ajouter les écouteurs d'événements aux en-têtes de table
    document.querySelectorAll('.fw-table th, .sortable-table th').forEach((th, index) => {
        if (index < th.closest('table').querySelectorAll('th').length - 1) { // Ne pas trier la dernière colonne (Actions)
            th.style.cursor = 'pointer';
            th.addEventListener('click', function() {
                const table = this.closest('table');
                const currentSort = this.dataset.sort || 'asc';
                const newSort = currentSort === 'asc' ? 'desc' : 'asc';
                this.dataset.sort = newSort;

                // Retirer les indicateurs de tri des autres colonnes
                table.querySelectorAll('th').forEach(otherTh => {
                    if (otherTh !== this) {
                        delete otherTh.dataset.sort;
                        const icon = otherTh.querySelector('.sort-icon');
                        if (icon) icon.remove();
                    }
                });

                // Ajouter ou mettre à jour l'indicateur
                let icon = this.querySelector('.sort-icon');
                if (!icon) {
                    icon = document.createElement('span');
                    icon.className = 'sort-icon';
                    icon.style.marginLeft = '5px';
                    this.appendChild(icon);
                }
                icon.textContent = newSort === 'asc' ? '↑' : '↓';

                sortTable(table, index, newSort === 'asc');
            });
        }
    });

    // Fonction de filtrage
    function filterTable() {
        const userFilter = document.querySelector('.filter-bar input[aria-label="Utilisateur"]').value.trim().toLowerCase();
        const dateFilter = document.querySelector('.filter-bar input[aria-label="Date"]').value.trim();
        const table = document.querySelector('.fw-table');
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const userCell = row.cells[1].textContent.trim().toLowerCase();
            const dateCell = row.cells[2].textContent.trim();

            const userMatch = !userFilter || userCell.includes(userFilter);
            const dateMatch = !dateFilter || dateCell === dateFilter;

            row.style.display = userMatch && dateMatch ? '' : 'none';
        });
    }

    // Fonction de réinitialisation du filtre
    function resetFilter() {
        document.querySelector('.filter-bar input[aria-label="Utilisateur"]').value = '';
        document.querySelector('.filter-bar input[aria-label="Date"]').value = '';
        const table = document.querySelector('.fw-table');
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => row.style.display = '');
    }

    // Ajouter les écouteurs aux boutons de filtrage
    const filterBtn = document.querySelector('.filter-bar .btn-primary');
    const resetBtn = document.querySelector('.filter-bar .btn-outline');

    if (filterBtn) {
        filterBtn.addEventListener('click', filterTable);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', resetFilter);
    }

    // Recherche globale (topbar)
    const searchInput = document.querySelector('.topbar-search input[type="search"]');
    const searchBtn = document.querySelector('.topbar-search button');

    if (searchInput && searchBtn) {
        function globalSearch() {
            const query = searchInput.value.trim().toLowerCase();
            const table = document.querySelector('.fw-table');
            const tbody = table.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr');

            rows.forEach(row => {
                const text = Array.from(row.cells).slice(0, -1).map(cell => cell.textContent.trim().toLowerCase()).join(' ');
                row.style.display = !query || text.includes(query) ? '' : 'none';
            });
        }

        searchBtn.addEventListener('click', globalSearch);
        searchInput.addEventListener('input', globalSearch); // Recherche en temps réel
    }
});