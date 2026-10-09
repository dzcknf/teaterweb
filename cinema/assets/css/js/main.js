/**
 * main.js  –  CinemaXXI JavaScript
 * Fitur: Hamburger menu, pemilihan kursi, hitung total, form validasi
 */

document.addEventListener('DOMContentLoaded', function () {

  /* ============================================================
     1. HAMBURGER MENU (mobile nav)
     ============================================================ */
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');

  if (hamburger && navLinks) {
    hamburger.addEventListener('click', function () {
      navLinks.classList.toggle('open');
    });
    // Tutup menu saat link diklik
    navLinks.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        navLinks.classList.remove('open');
      });
    });
  }

  /* ============================================================
     2. SEAT PICKER  (halaman pemesanan)
     ============================================================ */
  const seatEls      = document.querySelectorAll('.seat:not(.taken)');
  const inputKursi   = document.getElementById('id_kursi');
  const inputLabel   = document.getElementById('label_kursi');
  const totalDisplay = document.getElementById('total_display');
  const totalInput   = document.getElementById('total_harga');
  const hargaJadwal  = parseFloat(document.getElementById('harga_jadwal')?.value || 0);

  let selectedSeat     = null;
  let selectedSeatLabel = '';

  seatEls.forEach(function (seat) {
    seat.addEventListener('click', function () {
      // Hapus pilihan sebelumnya
      seatEls.forEach(function (s) { s.classList.remove('selected'); });

      // Pilih kursi baru
      seat.classList.add('selected');
      selectedSeat      = seat.dataset.id;
      selectedSeatLabel = seat.dataset.label;

      // Isi input tersembunyi
      if (inputKursi)  inputKursi.value  = selectedSeat;
      if (inputLabel)  inputLabel.value  = selectedSeatLabel;

      // Update tampilan ringkasan
      const kursiDisplay = document.getElementById('display_kursi');
      if (kursiDisplay) kursiDisplay.textContent = selectedSeatLabel;

      // Update total
      if (totalDisplay) totalDisplay.textContent = formatRupiah(hargaJadwal);
      if (totalInput)   totalInput.value = hargaJadwal;
    });
  });

  /* ============================================================
     3. VALIDASI FORM PEMESANAN
     ============================================================ */
  const bookingForm = document.getElementById('bookingForm');
  if (bookingForm) {
    bookingForm.addEventListener('submit', function (e) {
      const nama  = document.getElementById('nama')?.value.trim();
      const email = document.getElementById('email')?.value.trim();
      const kursi = document.getElementById('id_kursi')?.value;

      if (!nama || !email || !kursi) {
        e.preventDefault();
        showAlert('Lengkapi semua data: nama, email, dan pilih kursi.', 'error');
        return;
      }
      // Validasi email sederhana
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        showAlert('Format email tidak valid.', 'error');
        return;
      }
    });
  }

  /* ============================================================
     4. FILTER JADWAL BERDASARKAN FILM (halaman jadwal)
     ============================================================ */
  const filmBtns    = document.querySelectorAll('.film-select-btn');
  const jadwalRows  = document.querySelectorAll('.jadwal-row');

  filmBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      filmBtns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');

      const filmId = btn.dataset.filmId;

      jadwalRows.forEach(function (row) {
        if (filmId === 'all' || row.dataset.filmId === filmId) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  });

  /* ============================================================
     5. COUNTDOWN  –  Jadwal yang ditampilkan
     ============================================================ */
  // (Opsional: tampilkan label 'HARI INI' pada jadwal yang tanggalnya hari ini)
  const today = new Date().toISOString().slice(0, 10);
  document.querySelectorAll('[data-tanggal]').forEach(function (el) {
    if (el.dataset.tanggal === today) {
      el.insertAdjacentHTML('beforeend', ' <span style="color:var(--red);font-size:.7rem;font-weight:700;">HARI INI</span>');
    }
  });

  /* ============================================================
     HELPER: tampilkan alert inline
     ============================================================ */
  function showAlert(pesan, tipe) {
    const existing = document.getElementById('inline-alert');
    if (existing) existing.remove();

    const div = document.createElement('div');
    div.id = 'inline-alert';
    div.className = 'alert alert-' + (tipe === 'error' ? 'error' : 'success');
    div.textContent = '⚠️ ' + pesan;

    const form = document.getElementById('bookingForm');
    if (form) form.insertAdjacentElement('beforebegin', div);

    // Auto hapus setelah 4 detik
    setTimeout(function () { div.remove(); }, 4000);
  }

  /* ============================================================
     HELPER: format rupiah tanpa library
     ============================================================ */
  function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
  }

});
