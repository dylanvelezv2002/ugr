document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalGuia');
  const btnNueva = document.getElementById('nuevaGuia');
  const closeModalBtn = modal.querySelector('.close-modal');
  const form = document.getElementById('formGuia');
  const submitBtn = document.getElementById('submitBtn');
  const tableBody = document.getElementById('tableBody');
  const pagination = document.getElementById('pagination');
  const infoRegistros = document.getElementById('infoRegistros');
  const modalTitle = document.getElementById('modalTitle');
  const registrosSelect = document.getElementById('registrosPorPagina');
  let editingId = null;
  let currentPage = 1;
  let searchValue = '';
  let limite = 10;

  btnNueva.onclick = () => {
    editingId = null;
    form.reset();
    modalTitle.textContent = "Registrar Nueva Guía";
    submitBtn.textContent = "Registrar";
    modal.style.display = "flex";
  };

  closeModalBtn.onclick = () => modal.style.display = "none";

  window.onclick = e => {
    if (e.target === modal) modal.style.display = "none";
  };

  submitBtn.onclick = () => {
    const fd = new FormData(form);
    let url = editingId ? 'includes/editar_guias.php' : 'includes/guardar_guias.php';
    if (editingId) fd.append('id', editingId);

    fetch(url, { method: 'POST', body: fd })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire('Éxito', data.message, 'success');
          modal.style.display = "none";
          form.reset();
          editingId = null;
          fetchTableData();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      })
      .catch(() => Swal.fire('Error', 'No se pudo conectar al servidor', 'error'));
  };

  pagination.onclick = e => {
    if (e.target.classList.contains('page-link')) {
      e.preventDefault();
      currentPage = parseInt(e.target.dataset.page);
      fetchTableData();
    }
  };

  document.getElementById('searchInput').onkeyup = () => {
    searchValue = document.getElementById('searchInput').value.trim();
    currentPage = 1;
    fetchTableData();
  };

  registrosSelect.onchange = function () {
    limite = parseInt(this.value);
    currentPage = 1;
    fetchTableData();
  };

  function fetchTableData() {
    fetch('includes/fetch_guia_data.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `query=${encodeURIComponent(searchValue)}&page=${currentPage}&limit=${limite}`
    })
      .then(res => res.json())
      .then(data => {
        tableBody.innerHTML = data.html;
        infoRegistros.innerText = `Mostrando ${data.desde} a ${data.hasta} de ${data.total} registros`;
        renderPagination(data.total_paginas, data.pagina_actual);
      })
      .catch(() => Swal.fire('Error', 'No se pudo cargar la tabla', 'error'));
  }

  function renderPagination(totalPages, currentPage) {
    pagination.innerHTML = '';
    const maxVisible = 5;
    let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let end = Math.min(start + maxVisible - 1, totalPages);
    if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);

    if (currentPage > 1) {
      pagination.innerHTML += `<a href="#" class="page-link" data-page="1">&laquo;</a>`;
      pagination.innerHTML += `<a href="#" class="page-link" data-page="${currentPage - 1}">Anterior</a>`;
    }

    for (let i = start; i <= end; i++) {
      pagination.innerHTML += `<a href="#" class="page-link ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</a>`;
    }

    if (currentPage < totalPages) {
      pagination.innerHTML += `<a href="#" class="page-link" data-page="${currentPage + 1}">Siguiente</a>`;
      pagination.innerHTML += `<a href="#" class="page-link" data-page="${totalPages}">&raquo;</a>`;
    }
  }

  tableBody.onclick = e => {
    if (e.target.closest('.delete-icon')) {
      const id = e.target.closest('.delete-icon').dataset.id;
      Swal.fire({
        title: '¿Eliminar esta guía?',
        text: "Esta acción no se puede revertir.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          fetch(`includes/eliminar_guia.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                Swal.fire('Eliminado', data.message, 'success');
                fetchTableData();
              } else {
                Swal.fire('Error', data.message, 'error');
              }
            });
        }
      });
    } else if (e.target.closest('.edit-icon')) {
      const id = e.target.closest('.edit-icon').dataset.id;
      fetch(`includes/get_guides.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            editingId = id;
            modalTitle.textContent = "Editar Guía";
            submitBtn.textContent = "Actualizar";
            form.nombre_material.value = data.data.nombre_material;
            form.codigo_onu.value = data.data.codigo_onu;
            form.guia_emergencia.value = data.data.guia_emergencia;
            form.aloha_name.value = data.data.aloha_name || '';
            form.etiqueta_dot.value = data.data.etiqueta_dot || '';
            modal.style.display = "flex";
          } else {
            Swal.fire('Error', data.message, 'error');
          }
        });
    }
  };

  const modalFiles = document.getElementById('modalFiles');
  const closeFilesModal = document.getElementById('closeFilesModal');
  const modalFilesTitle = document.getElementById('modalFilesTitle');
  const modalFilesContent = document.getElementById('modalFilesContent');

  document.body.addEventListener('click', e => {
    if (e.target.classList.contains('btn-view-files')) {
      const filesStr = e.target.dataset.files;
      const files = filesStr.split(',').map(f => f.trim()).filter(f => f);
      modalFilesTitle.textContent = 'Archivos Asociados';
      modalFilesContent.innerHTML = files.map(f => {
        const ext = f.split('.').pop().toLowerCase();
        if (ext === 'pdf') {
          return `<iframe src="uploads/documents/${f}" width="100%" height="400px" style="margin-bottom:10px;"></iframe>`;
        } else {
          return `<a href="uploads/documents/${f}" target="_blank" rel="noopener noreferrer">${f}</a><br>`;
        }
      }).join('');
      modalFiles.style.display = 'flex';
    } else if (e.target.classList.contains('btn-view-images')) {
      const imagesStr = e.target.dataset.images;
      const images = imagesStr.split(',').map(i => i.trim()).filter(i => i);
      modalFilesTitle.textContent = 'Imágenes Asociadas';
      modalFilesContent.innerHTML = images.map(img => `<img src="uploads/images/${img}" style="max-width:100%; margin-bottom:10px;" alt="${img}">`).join('');
      modalFiles.style.display = 'flex';
    }
  });

  closeFilesModal.onclick = () => modalFiles.style.display = 'none';
  window.onclick = e => { if (e.target === modalFiles) modalFiles.style.display = 'none'; };

  fetchTableData();
});
