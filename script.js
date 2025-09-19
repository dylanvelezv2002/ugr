document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.getElementById('searchInput');
  const tableBody = document.getElementById('tableBody');
  const pagination = document.getElementById('pagination');
  let currentPage = 1;
  let searchValue = '';

  function fetchTableData(page = 1) {
    fetch(`includes/fetch_guia_data.php?page=${page}&search=${encodeURIComponent(searchValue)}`)
      .then(res => res.json())
      .then(data => {
        currentPage = page;
        tableBody.innerHTML = '';

        if (data.guia_data.length === 0) {
          tableBody.innerHTML = '<tr><td colspan="9">No se encontraron resultados</td></tr>';
        } else {
          data.guia_data.forEach(row => {
            tableBody.insertAdjacentHTML('beforeend', `
              <tr>
                <td>${row.nombre_material}</td>
                <td>${row.codigo_onu}</td>
                <td>${row.guia_emergencia}</td>
                <td>${row.aloha_name}</td>
                <td>${row.etiqueta_dot}</td>
                <td>${row.nfpa704_imagen ? `<img src="uploads/nfpa704/${row.nfpa704_imagen}" style="height:40px;">` : 'No hay imagen'}</td>
                <td>${row.archivo ? '<button class="btn-view-files btn-primary" data-files="' + row.archivo + '">Ver</button>' : 'No hay'}</td>
                <td>${row.imagen_guia ? '<button class="btn-view-images btn-primary" data-images="' + row.imagen_guia + '">Ver</button>' : 'No hay'}</td>
                <td>
                  <a href="#" class="edit-icon" data-id="${row.id}"><i class="bx bx-edit-alt"></i></a>
                  <a href="#" class="delete-icon" data-id="${row.id}"><i class="bx bx-trash"></i></a>
                </td>
              </tr>
            `);
          });
        }

        updatePagination(data.total_pages, data.total_items);
      });
  }

  function updatePagination(totalPages, totalItems) {
    pagination.innerHTML = '';
    const limit = 5;
    const start = Math.max(1, currentPage - 2);
    const end = Math.min(totalPages, start + limit - 1);

    if (currentPage > 1) {
      pagination.innerHTML += `<a href="#" class="page-link" data-page="${currentPage - 1}"><i class="bx bx-chevron-left"></i></a>`;
    }

    for (let i = start; i <= end; i++) {
      pagination.innerHTML += `<a href="#" class="page-link ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</a>`;
    }

    if (end < totalPages) {
      pagination.innerHTML += `<a href="#" class="page-link" data-page="${totalPages}"><i class="bx bx-chevrons-right"></i></a>`;
    }

    pagination.innerHTML += `<span style="font-size:0.75em; margin-left:10px;">Total: ${totalItems}</span>`;
  }

  // Paginación clic
  pagination.addEventListener('click', e => {
    if (e.target.closest('.page-link')) {
      e.preventDefault();
      const page = parseInt(e.target.closest('.page-link').dataset.page);
      if (!isNaN(page)) {
        fetchTableData(page);
      }
    }
  });

  // Búsqueda en tiempo real
  searchInput.addEventListener('input', () => {
    searchValue = searchInput.value.trim();
    currentPage = 1;
    fetchTableData();
  });

  // Inicial
  fetchTableData();
});
