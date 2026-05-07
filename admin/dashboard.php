<?php
session_start();

// Kalau belum login, redirect ke halaman utama
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  header('Location: ../index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin — AVHIRA Cook</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="dashboard.css">
</head>
<body class="dash-body">

  <!-- HEADER -->
 <!-- Ubah dash-header jadi seperti ini -->
    <div class="dash-header">
      <div class="dash-title">🍳 AVHIRA Cook — Dashboard Admin</div>
      <div class="dash-user">
        <button class="btn-add" id="btnAddRecipe">+ Tambah Resep</button>
        <span>👋 <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></span>
        <button class="dash-logout" id="logoutBtn">Keluar</button>
      </div>
    </div>

  <div class="dash-content">

    <!-- STATISTIK -->
    <div class="dash-stats">
      <div class="stat-card">
        <div class="stat-label">Total Resep</div>
        <div class="stat-value" id="statRecipes">—</div>
        <div class="stat-sub" id="statRecipesSub">Memuat...</div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Total Likes</div>
        <div class="stat-value" id="statLikes">—</div>
        <div class="stat-sub">Dari semua resep</div>
      </div>
      <div class="stat-card blue">
        <div class="stat-label">Tayangan Hari Ini</div>
        <div class="stat-value" id="statViews">—</div>
        <div class="stat-sub" id="statViewsSub">Memuat...</div>
      </div>
    </div>

    <!-- TOP RESEP & GRAFIK -->
    <div class="dash-grid">

      <!-- Ranking -->
      <div class="dash-card">
        <h3>🏆 Resep Paling Diminati</h3>
        <ul class="rank-list" id="rankList">
          <li class="loading-text">Memuat data...</li>
        </ul>
      </div>

      <!-- Grafik -->
      <div class="dash-card">
        <h3>📊 Tayangan 7 Hari Terakhir</h3>
        <div class="chart-area" id="weekChart">
          <p class="loading-text">Memuat grafik...</p>
        </div>
      </div>

    </div>

    <!-- TABEL RESEP -->
    <div class="dash-card" style="margin-bottom: 2rem">
      <h3>📋 Semua Resep</h3>
      <table class="dash-table">
        <thead>
          <tr>
            <th>Resep</th>
            <th>Kategori</th>
            <th>Tayangan</th>
            <th>Likes</th>
            <th>Rating</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="dashTableBody">
          <tr><td colspan="6" class="loading-text">Memuat data...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- FORM MODAL TAMBAH/EDIT RESEP -->
<div class="form-overlay hidden" id="formOverlay">
  <div class="form-modal">

    <div class="form-header">
      <h3 id="formTitle">Tambah Resep Baru</h3>
      <button class="form-close" onclick="closeForm()">✕</button>
    </div>

    <div class="form-body">

      <input type="hidden" id="recipeId">

      <div class="form-row">
        <div class="form-group" style="grid-column: 1 / -1">
          <label>Nama Resep</label>
          <input type="text" id="fTitle" placeholder="Contoh: Soto Ayam Lamongan">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Kategori</label>
          <select id="fCategory">
            <option value="1">Nusantara</option>
            <option value="2">Asia</option>
            <option value="3">Internasional</option>
            <option value="4">Minuman</option>
            <option value="5">Dessert</option>
          </select>
        </div>
        <div class="form-group">
          <label>Emoji</label>
          <input type="text" id="fEmoji" placeholder="🍜" maxlength="4">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Tingkat Kesulitan</label>
          <select id="fDifficulty">
            <option value="Mudah">Mudah</option>
            <option value="Sedang">Sedang</option>
            <option value="Sulit">Sulit</option>
          </select>
        </div>
        <div class="form-group">
          <label>Waktu Masak</label>
          <input type="text" id="fCookTime" placeholder="Contoh: 30 mnt">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Porsi</label>
          <input type="text" id="fServings" placeholder="Contoh: 4 porsi">
        </div>
        <div class="form-group">
          <label>Tag Cuaca</label>
          <input type="text" id="fWeather" placeholder="Contoh: hujan,mendung">
        </div>
      </div>

      <div class="form-group">
        <label>Bahan-bahan</label>
        <textarea id="fIngredients" rows="4"
          placeholder="Tulis satu bahan per baris&#10;Contoh:&#10;1 ekor ayam&#10;3 siung bawang putih"></textarea>
        <span class="hint">Satu bahan per baris</span>
      </div>

      <div class="form-group">
        <label>Langkah Memasak</label>
        <textarea id="fSteps" rows="5"
          placeholder="Tulis satu langkah per baris&#10;Contoh:&#10;Cuci bersih ayam&#10;Rebus ayam hingga empuk"></textarea>
        <span class="hint">Satu langkah per baris</span>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select id="fStatus">
          <option value="published">Tayang</option>
          <option value="draft">Draft</option>
        </select>
      </div>

    </div>

    <div class="form-footer">
      <button class="btn-cancel" onclick="closeForm()">Batal</button>
      <button class="btn-save" id="btnSave" onclick="saveRecipe()">Simpan Resep</button>
    </div>

  </div>
</div>

  </div>

  <script src="dashboard.js"></script>
</body>
</html>