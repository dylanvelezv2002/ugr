document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modalGuia');
  const btnNueva = document.getElementById('nuevaGuia');
  const closeModalBtn = modal.querySelector('.close-modal');
  const formGuia = document.getElementById('formGuia');
  const submitBtn = document.getElementById('submitBtn');
  const tableBody = document.getElementById('tableBody');
  const pagination = document.querySelector('.pagination');

  let editingId = null;

  btnNueva.addEventListener('click', () => {
    editingId = null;
    formGuia.reset();
    submitBtn.textContent = 'Registrar';
    modal.style.display = 'flex';
  });

  closeModalBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  window.addEventListener('click', e => {
    if (e.target === modal) modal.style.display = 'none';
  });

  submitBtn.addEventListener('click', () => {
    const formData = new FormData(formGuia);
    let url = 'includes/guardar_guia.php';
    if (editingId) {
      url = 'includes/editar_guia.php';
      formData.append('id', editingId);
    }

    fetch(url, {
      method: 'POST',
      body: formData
    }).then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire('¡Éxito!', data.message, 'success');
          modal.style.display = 'none';
          formGuia.reset();
          editingId = null;
          submitBtn.textContent = 'Registrar';
          fetchTableData();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      }).catch(() => {
        Swal.fire('Error', 'Error al conectar con el servidor.', 'error');
      });
  });

  tableBody.addEventListener('click', e => {
    if (e.target.closest('.delete-icon')) {
      const id = e.target.closest('.delete-icon').dataset.id;
      Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esta acción!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          fetch(`includes/eliminar_guia.php?id=${id}`, { method: 'GET' })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                Swal.fire('¡Eliminado!', data.message, 'success');
                fetchTableData();
              } else {
                Swal.fire('Error', data.message, 'error');
              }
            }).catch(() => {
              Swal.fire('Error', 'Error al conectar con el servidor.', 'error');
            });
        }
      });
    } else if (e.target.closest('.edit-icon')) {
      const id = e.target.closest('.edit-icon').dataset.id;
      fetch(`includes/get_guia.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            editingId = id;
            submitBtn.textContent = 'Actualizar';
            formGuia.nombre_material.value = data.data.nombre_material || '';
            formGuia.codigo_onu.value = data.data.codigo_onu || '';
            formGuia.guia_emergencia.value = data.data.guia_emergencia || '';
            modal.style.display = 'flex';
          } else {
            Swal.fire('Error', data.message, 'error');
          }
        }).catch(() => {
          Swal.fire('Error', 'Error al conectar con el servidor.', 'error');
        });
    }
  });

  function fetchTableData(page = 1) {
    const search = document.querySelector('input[name="search"]').value || '';
    fetch(`includes/fetch_guia_data.php?page=${page}&search=${encodeURIComponent(search)}`)
      .then(res => res.json())
      .then(data => {
        tableBody.innerHTML = '';
        data.guia_data.forEach(row => {
          tableBody.insertAdjacentHTML('beforeend', `
            <tr id="row_${row.id}">
              <td>${row.nombre_material}</td>
              <td>${row.codigo_onu}</td>
              <td>${row.guia_emergencia}</td>
              <td><a href="uploads/documents/${row.archivo}" target="_blank" class="btn-file">Ver</a></td>
              <td><img src="uploads/images/${row.imagen_guia}" width="50" alt="Imagen guía"></td>
              <td>
                <a href="javascript:void(0);" class="delete-icon" data-id="${row.id}"><i class="bx bx-trash text-danger action-btn"></i></a>
                <a href="javascript:void(0);" class="edit-icon" data-id="${row.id}"><i class="bx bx-edit-alt text-primary action-btn"></i></a>
              </td>
            </tr>
          `);
        });
        updatePagination(data.total_pages, page);
      });
  }

  function updatePagination(totalPages, currentPage) {
    pagination.innerHTML = '';
    for(let i = 1; i <= totalPages; i++) {
      const a = document.createElement('a');
      a.href = '#';
      a.textContent = i;
      if (i === currentPage) a.classList.add('active');
      a.addEventListener('click', e => {
        e.preventDefault();
        fetchTableData(i);
      });
      pagination.appendChild(a);
    }
  }

  document.querySelector('input[name="search"]').addEventListener('keyup', () => fetchTableData());

  fetchTableData();
});
