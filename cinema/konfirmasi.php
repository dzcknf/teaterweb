<?php
/**
 * konfirmasi.php  –  Halaman Konfirmasi Pesanan
 * Ditampilkan setelah transaksi berhasil disimpan.
 * URL: konfirmasi.php?id=TRANSAKSI_ID
 */

require_once 'config.php';

// ----------------------------------------------------------------
// Ambil ID transaksi dari URL
// ----------------------------------------------------------------
$trxId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($trxId <= 0) {
    header('Location: index.php');
    exit;
}

// ----------------------------------------------------------------
// Ambil data transaksi lengkap dengan JOIN ke semua tabel terkait
// ----------------------------------------------------------------
$stmt = $conn->prepare(
    "SELECT
        t.id           AS trx_id,
        t.total_harga,
        t.waktu_pesan,
        t.status_pembayaran,
        p.nama,
        p.email,
        p.no_telepon,
        f.judul        AS judul_film,
        f.genre,
        f.durasi,
        f.rating,
        j.tanggal,
        j.jam_tayang,
        s.nama_studio,
        k.nomor_kursi,
        k.baris
     FROM transaksi t
     INNER JOIN pelanggan p ON p.id = t.id_pelanggan
     INNER JOIN jadwal    j ON j.id = t.id_jadwal
     INNER JOIN film      f ON f.id = j.id_film
     INNER JOIN studio    s ON s.id = j.id_studio
     INNER JOIN kursi     k ON k.id = t.id_kursi
     WHERE t.id = ?"
);
$stmt->bind_param('i', $trxId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Jika transaksi tidak ditemukan
if (!$data) {
    header('Location: index.php');
    exit;
}

// ----------------------------------------------------------------
// Buat kode booking yang mudah dibaca dari ID transaksi
// Format: CXX-000001
// ----------------------------------------------------------------
$kodeBooking = 'CXX-' . str_pad($data['trx_id'], 6, '0', STR_PAD_LEFT);

$pageTitle  = 'Konfirmasi Pesanan';
$activeMenu = '';

require_once 'includes/header.php';
?>

<!-- ================================================================
     KONFIRMASI CARD
     ================================================================ -->
<div class="konfirmasi-wrap">

  <div class="konfirmasi-card">

    <!-- Header sukses -->
    <div class="konfirmasi-header">
      <div class="check-icon">🎉</div>
      <h2>PESANAN BERHASIL!</h2>
      <p>Tiket kamu sudah kami catat. Tunjukkan kode booking di kasir.</p>
    </div>

    <div class="konfirmasi-body">

      <!-- Kode Booking -->
      <div class="kode-booking">
        <div class="kode-label">Kode Booking</div>
        <div class="kode-num"><?= $kodeBooking ?></div>
        <div style="font-size:.75rem;color:var(--muted);margin-top:6px;">
          Simpan kode ini baik-baik
        </div>
      </div>

      <!-- Detail Pesanan -->
      <div class="detail-rows">

        <div class="detail-row">
          <span>Film</span>
          <span class="dr-val"><?= htmlspecialchars($data['judul_film']) ?></span>
        </div>

        <div class="detail-row">
          <span>Genre / Rating</span>
          <span class="dr-val">
            <?= htmlspecialchars($data['genre']) ?>
            &nbsp;<span class="badge-rating"><?= htmlspecialchars($data['rating']) ?></span>
          </span>
        </div>

        <div class="detail-row">
          <span>Tanggal Tayang</span>
          <span class="dr-val"><?= date('l, d F Y', strtotime($data['tanggal'])) ?></span>
        </div>

        <div class="detail-row">
          <span>Jam Tayang</span>
          <span class="dr-val"><?= substr($data['jam_tayang'], 0, 5) ?> WIB</span>
        </div>

        <div class="detail-row">
          <span>Studio</span>
          <span class="dr-val"><?= htmlspecialchars($data['nama_studio']) ?></span>
        </div>

        <div class="detail-row">
          <span>Kursi</span>
          <span class="dr-val" style="color:var(--gold);">
            <?= htmlspecialchars($data['baris']) ?> –
            <?= htmlspecialchars($data['nomor_kursi']) ?>
          </span>
        </div>

        <div class="detail-row">
          <span>Penonton</span>
          <span class="dr-val"><?= htmlspecialchars($data['nama']) ?></span>
        </div>

        <div class="detail-row">
          <span>Email</span>
          <span class="dr-val"><?= htmlspecialchars($data['email']) ?></span>
        </div>

        <div class="detail-row">
          <span>Telepon</span>
          <span class="dr-val"><?= htmlspecialchars($data['no_telepon']) ?></span>
        </div>

        <div class="detail-row">
          <span>Waktu Pesan</span>
          <span class="dr-val">
            <?= date('d M Y, H:i', strtotime($data['waktu_pesan'])) ?> WIB
          </span>
        </div>

        <div class="detail-row">
          <span>Status Pembayaran</span>
          <span class="dr-val">
            <?php
            $statusMap = [
              'pending'     => '🟡 Menunggu Pembayaran',
              'lunas'       => '🟢 Lunas',
              'dibatalkan'  => '🔴 Dibatalkan',
            ];
            echo $statusMap[$data['status_pembayaran']] ?? $data['status_pembayaran'];
            ?>
          </span>
        </div>

      </div><!-- end detail-rows -->

      <!-- Total Harga -->
      <div class="summary-total" style="margin-bottom:28px;">
        <span class="label">Total Dibayar</span>
        <span class="amount"><?= rupiah($data['total_harga']) ?></span>
      </div>

      <!-- Info Pembayaran -->
      <?php if ($data['status_pembayaran'] === 'pending'): ?>
        <div class="alert" style="background:rgba(201,168,76,.1);border:1px solid var(--gold);color:var(--gold);">
          💳 Silakan lakukan pembayaran di kasir bioskop dengan menunjukkan kode booking di atas.
        </div>
      <?php endif; ?>

      <!-- Tombol Aksi -->
      <div class="konfirmasi-actions">
        <a href="index.php" class="btn btn-primary">
          🎬 Kembali ke Beranda
        </a>
        <a href="jadwal.php" class="btn btn-outline">
          📅 Lihat Jadwal Lain
        </a>
        <button
          onclick="window.print()"
          class="btn btn-outline"
          style="border-color:var(--muted);color:var(--muted);">
          🖨️ Cetak
        </button>
      </div>

    </div><!-- end konfirmasi-body -->
  </div><!-- end konfirmasi-card -->

  <p style="text-align:center;margin-top:20px;font-size:.8rem;color:var(--muted);">
    ID Transaksi: #<?= $data['trx_id'] ?> &bull; <?= SITE_NAME ?>
  </p>
</div>

<?php require_once 'includes/footer.php'; ?>
