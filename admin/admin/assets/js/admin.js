/**
 * admin/assets/js/admin.js
 * Fitur: toggle sidebar mobile, konfirmasi hapus, preview upload poster
 */

document.addEventListener('DOMContentLoaded', function () {

  /* ============================================================
     1. TOGGLE SIDEBAR (mobile)
     ============================================================ */
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('admSidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  }

  /* ============================================================
     2. KONFIRMASI HAPUS (dipakai di list film & jadwal)
     Tombol harus punya: class="btn-delete" data-name="Nama Item" href="hapus.php?id=X"
     ============================================================ */
  document.querySelectorAll('.btn-delete').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const nama = btn.dataset.name || 'item ini';
      const url  = btn.getAttribute('href');

      if (confirm('Yakin ingin menghapus "' + nama + '"?\nTindakan ini tidak bisa dibatalkan.')) {
        window.location.href = url;
      }
    });
  });

  /* ============================================================
     3. PREVIEW UPLOAD POSTER (form tambah/edit film)
     ============================================================ */
  const posterInput = document.getElementById('poster');
  const previewImg   = document.getElementById('posterPreview');

  if (posterInput && previewImg) {
    posterInput.addEventListener('change', function () {
      const file = posterInput.files[0];
      if (!file) return;

      // Validasi cepat di client (server tetap validasi ulang)
      const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
      if (!validTypes.includes(file.type)) {
        alert('Format file harus JPG atau PNG.');
        posterInput.value = '';
        return;
      }
      if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB.');
        posterInput.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        previewImg.src = e.target.result;
        previewImg.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });
  }

  /* ============================================================
     4. AUTO-DISMISS ALERT setelah 5 detik
     ============================================================ */
  document.querySelectorAll('.alert').forEach(function (alertEl) {
    setTimeout(function () {
      alertEl.style.transition = 'opacity .4s';
      alertEl.style.opacity = '0';
      setTimeout(function () { alertEl.remove(); }, 400);
    }, 5000);
  });

  /* ============================================================
     5. VALIDASI SEDERHANA FORM FILM (client-side, server tetap utama)
     ============================================================ */
  const filmForm = document.getElementById('filmForm');
  if (filmForm) {
    filmForm.addEventListener('submit', function (e) {
      const judul   = document.getElementById('judul')?.value.trim();
      const durasi  = document.getElementById('durasi')?.value;

      if (!judul) {
        e.preventDefault();
        alert('Judul film wajib diisi.');
        return;
      }
      if (!durasi || isNaN(durasi) || durasi <= 0) {
        e.preventDefault();
        alert('Durasi harus berupa angka lebih dari 0.');
        return;
      }
    });
  }

});
