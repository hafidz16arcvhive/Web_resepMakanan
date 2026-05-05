<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AVHIRA Cook</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

  <!-- CSS kita -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ===== NAVBAR ===== -->
  <nav class="navbar">
    <div class="logo">AVHIRA <span>Cook</span></div>
    <div class="nav-links">
      <a href="#" class="nav-btn active">Beranda</a>
      <a href="#resep" class="nav-btn">Resep</a>
      <button class="nav-btn weather-btn" id="weatherToggle">🌤 Masak Sesuai Cuaca</button>
      <button class="nav-btn login-btn" id="authBtn">Login Admin</button>
    </div>
  </nav>

  <!-- ===== WEATHER BANNER (tersembunyi dulu) ===== -->
  <div class="weather-banner hidden" id="weatherBanner">
    <div class="weather-info">
      <div class="weather-icon" id="wIcon">⛅</div>
      <div class="weather-details">
        <h3 id="wTitle">Cuaca Hari Ini</h3>
        <p id="wDesc">Memuat cuaca...</p>
      </div>
    </div>
    <div class="weather-rec" id="weatherRec">
      <strong>Rekomendasi Masakan:</strong>
      <span id="wRecText">-</span>
    </div>
    <button class="weather-suggest-btn" id="weatherFilterBtn">
      Lihat Resep Rekomendasi →
    </button>
  </div>

  <!-- ===== HERO ===== -->
  <section class="hero">
    <div class="hero-content">
      <div class="hero-tag">🍽️ Temukan Resep Terbaik</div>
      <h1>Masak dengan <span>Cinta</span>,<br>Sajikan dengan Bangga</h1>
      <p>Ribuan resep lezat nusantara & mancanegara, disesuaikan dengan selera dan cuaca harianmu.</p>
      <div class="hero-actions">
        <a href="#resep" class="btn-primary">Jelajahi Resep</a>
        <button class="btn-outline" id="heroWeatherBtn">🌤 Rekomendasi Cuaca</button>
      </div>
    </div>
  </section>

  <!-- ===== KONTEN UTAMA (akan diisi tahap berikutnya) ===== -->
  <main id="resep">

  <!-- Search & Filter -->
  <div class="search-section">
    <input
      class="search-box"
      type="text"
      placeholder="Cari resep, bahan, atau masakan..."
      id="searchInput"
    >
    <select class="filter-select" id="filterCat">
      <option value="">Semua Kategori</option>
      <option value="Nusantara">Nusantara</option>
      <option value="Asia">Asia</option>
      <option value="Internasional">Internasional</option>
      <option value="Minuman">Minuman</option>
      <option value="Dessert">Dessert</option>
    </select>
    <select class="filter-select" id="filterDiff">
      <option value="">Semua Tingkat</option>
      <option value="Mudah">Mudah</option>
      <option value="Sedang">Sedang</option>
      <option value="Sulit">Sulit</option>
    </select>
  </div>

  <!-- Header Section -->
  <div class="section-header">
    <h2 class="section-title">Resep <span>Populer</span></h2>
  </div>

  <!-- Grid Kartu Resep (diisi oleh JavaScript) -->
  <div class="recipe-grid" id="recipeGrid">
    <p class="empty-state">Memuat resep...</p>
  </div>

</main>

<!-- Modal Detail Resep -->
<div class="modal-overlay hidden" id="recipeModal">
  <div class="modal">
    <div class="modal-hero" id="modalHero">
      <button class="modal-close" id="modalClose">✕</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>
  <script src="script.js"></script>
</body>
</html>