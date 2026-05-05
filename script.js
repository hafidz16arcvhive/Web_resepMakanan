// ================================
// DATA & STATE
// ================================

// ================================
// WEATHER STATE
// ================================

let weatherActive  = false;
let currentWeather = null;
let weatherFilter  = null;

// ================================
// FUNGSI CUACA
// ================================

function toggleWeather() {
  weatherActive = !weatherActive;
  const banner = document.getElementById('weatherBanner');

  if (weatherActive) {
    banner.classList.remove('hidden');
    loadWeather();
  } else {
    banner.classList.add('hidden');
    clearWeatherFilter();
  }
}

function loadWeather() {
  // Update teks dulu saat loading
  document.getElementById('wTitle').textContent = 'Mendeteksi lokasi...';
  document.getElementById('wDesc').textContent  = 'Mohon izinkan akses lokasi';
  document.getElementById('wRecText').textContent = '-';

  // Minta izin lokasi dari browser
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      // Berhasil dapat lokasi
      (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        fetchWeather(lat, lon);
      },
      // Gagal / ditolak user
      (error) => {
        console.warn('Lokasi ditolak, pakai default Surabaya');
        // Pakai koordinat default Surabaya
        fetchWeather(-7.2575, 112.7521);
      }
    );
  } else {
    // Browser tidak support geolocation
    fetchWeather(-7.2575, 112.7521);
  }
}

async function fetchWeather(lat, lon) {
  try {
    const response = await fetch(`api/get_weather.php?lat=${lat}&lon=${lon}`);
    const data = await response.json();

    if (data.success) {
      currentWeather = data;
      displayWeather(data);
    } else {
      document.getElementById('wTitle').textContent = 'Gagal memuat cuaca';
    }
  } catch (error) {
    console.error('Weather error:', error);
    document.getElementById('wTitle').textContent = 'Gagal memuat cuaca';
  }
}

function displayWeather(data) {
  // Update tampilan banner
  document.getElementById('wIcon').textContent  = data.icon;
  document.getElementById('wTitle').textContent = `${data.label} di ${data.city}`;
  document.getElementById('wDesc').textContent  =
    `${data.description} · Suhu ${data.temp}°C · Terasa ${data.feels_like}°C · Kelembaban ${data.humidity}%`;
  document.getElementById('wRecText').textContent = data.recommendation;

  // Sesuaikan warna banner dengan cuaca
  const bannerColors = {
    hujan:  'linear-gradient(90deg, #1a4a6b, #2d7db5)',
    panas:  'linear-gradient(90deg, #eb3349, #f45c43)',
    cerah:  'linear-gradient(90deg, #f7971e, #ffd200)',
    mendung:'linear-gradient(90deg, #2c3e50, #4ca1af)',
    sejuk:  'linear-gradient(90deg, #56ab2f, #a8e063)',
    dingin: 'linear-gradient(90deg, #485563, #29323c)',
  };

  document.getElementById('weatherBanner').style.background =
    bannerColors[data.category] || bannerColors.cerah;
}

function filterByWeather() {
  if (!currentWeather) return;

  weatherFilter = currentWeather.category;

  // Tampilkan tag filter aktif di atas grid
  showWeatherTag(currentWeather.icon, currentWeather.label);

  // Fetch resep dengan filter weather
  fetchRecipes({ weather: weatherFilter });

  // Scroll ke section resep
  document.getElementById('resep').scrollIntoView({ behavior: 'smooth' });
}

function showWeatherTag(icon, label) {
  // Hapus tag lama kalau ada
  const oldTag = document.getElementById('weatherTag');
  if (oldTag) oldTag.remove();

  // Buat tag baru
  const tag = document.createElement('div');
  tag.id = 'weatherTag';
  tag.className = 'weather-active-tag';
  tag.innerHTML = `
    ${icon} Filter cuaca aktif: <strong>${label}</strong>
    <button onclick="clearWeatherFilter()" title="Hapus filter">✕</button>
  `;

  // Sisipkan sebelum grid
  const grid = document.getElementById('recipeGrid');
  grid.parentNode.insertBefore(tag, grid);
}

function clearWeatherFilter() {
  weatherFilter = null;

  // Hapus tag filter
  const tag = document.getElementById('weatherTag');
  if (tag) tag.remove();

  // Tampilkan semua resep lagi
  fetchRecipes();
}

// Simpan semua resep yang sudah diambil dari API
let allRecipes = [];

// Simpan ID resep yang di-like user
let likedRecipes = new Set();

// ================================
// FUNGSI AMBIL DATA DARI API
// ================================

async function fetchRecipes(params = {}) {
  // Bangun query string dari params
  // Contoh: {category: 'Nusantara'} → '?category=Nusantara'
  const query = new URLSearchParams(params).toString();
  const url = `api/get_recipes.php${query ? '?' + query : ''}`;

  try {
    const response = await fetch(url);
    const result = await response.json();

    if (result.success) {
      allRecipes = result.data;
      renderRecipes(allRecipes);
    } else {
      showEmptyState('Gagal memuat resep.');
    }
  } catch (error) {
    console.error('Error:', error);
    showEmptyState('Terjadi kesalahan saat memuat resep.');
  }
}

// ================================
// FUNGSI TAMPILKAN KARTU RESEP
// ================================

function renderRecipes(recipes) {
  const grid = document.getElementById('recipeGrid');

  if (recipes.length === 0) {
    showEmptyState('Tidak ada resep yang ditemukan.');
    return;
  }

  // Warna background kartu berdasarkan kategori
  const bgColors = {
    'Nusantara':     '#FEF3E2',
    'Asia':          '#E8F5EE',
    'Internasional': '#E8F0FE',
    'Minuman':       '#E8F4FD',
    'Dessert':       '#FCE4EC'
  };

  // Buat HTML untuk setiap kartu
  grid.innerHTML = recipes.map((recipe, index) => {
    const bg = bgColors[recipe.category_name] || '#F5F5F5';
    const isLiked = likedRecipes.has(recipe.id);
    const likeCount = parseInt(recipe.likes) + (isLiked ? 1 : 0);

    // Badge hanya untuk resep dengan views tertinggi
    const badge = index === 0 ? 'Terlaris' : (index === 1 ? 'Populer' : '');

    return `
      <div class="recipe-card" onclick="openRecipe('${recipe.id}')">
        <div class="recipe-img" style="background: ${bg}">
          ${recipe.emoji}
          ${badge ? `<span class="recipe-badge">${badge}</span>` : ''}
        </div>
        <div class="recipe-body">
          <div class="recipe-cat">${recipe.category_name}</div>
          <div class="recipe-title">${recipe.title}</div>
          <div class="recipe-meta">
            <span>⏱ ${recipe.cook_time}</span>
            <span>👥 ${recipe.servings}</span>
            <span>📊 ${recipe.difficulty}</span>
          </div>
          <div class="recipe-footer">
            <div class="recipe-rating">
              ⭐ ${recipe.rating}
              <span style="font-weight:400; color:var(--text-muted)">
                (${recipe.likes})
              </span>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem">
              <span class="recipe-views">👁 ${Number(recipe.views).toLocaleString('id-ID')}</span>
              <button
                class="like-btn ${isLiked ? 'liked' : ''}"
                onclick="toggleLike(event, '${recipe.id}')"
              >
                ${isLiked ? '❤' : '🤍'} ${likeCount}
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function showEmptyState(message) {
  document.getElementById('recipeGrid').innerHTML =
    `<p class="empty-state">${message}</p>`;
}

// ================================
// FUNGSI LIKE
// ================================

function toggleLike(event, recipeId) {
  // Hentikan supaya tidak trigger openRecipe
  event.stopPropagation();

  if (likedRecipes.has(recipeId)) {
    likedRecipes.delete(recipeId);
  } else {
    likedRecipes.add(recipeId);
  }

  // Re-render kartu dengan state like yang baru
  renderRecipes(allRecipes);
}

// ================================
// FUNGSI MODAL DETAIL RESEP
// ================================

function openRecipe(recipeId) {
  // Cari resep berdasarkan ID
  const recipe = allRecipes.find(r => r.id == recipeId);
  if (!recipe) return;

  const bgColors = {
    'Nusantara':     '#FEF3E2',
    'Asia':          '#E8F5EE',
    'Internasional': '#E8F0FE',
    'Minuman':       '#E8F4FD',
    'Dessert':       '#FCE4EC'
  };
  const bg = bgColors[recipe.category_name] || '#F5F5F5';

  // Set background emoji di modal
  const modalHero = document.getElementById('modalHero');
  modalHero.style.background = bg;
  modalHero.innerHTML = `
    <span style="font-size:6rem">${recipe.emoji}</span>
    <button class="modal-close" onclick="closeModal()">✕</button>
  `;

  // Isi konten modal
  document.getElementById('modalBody').innerHTML = `
    <h2>${recipe.title}</h2>
    <div class="modal-meta">
      <span>⏱ <strong>${recipe.cook_time}</strong></span>
      <span>👥 <strong>${recipe.servings}</strong></span>
      <span>📊 <strong>${recipe.difficulty}</strong></span>
      <span>⭐ <strong>${recipe.rating}</strong></span>
      <span>👁 <strong>${Number(recipe.views).toLocaleString('id-ID')} tayangan</strong></span>
    </div>

    <div class="ingr-title">🛒 Bahan-bahan</div>
    <ul class="ingr-list">
      ${recipe.ingredients.map(i => `<li>${i}</li>`).join('')}
    </ul>

    <div class="steps-title">👨‍🍳 Langkah Memasak</div>
    <ol class="steps-list">
      ${recipe.steps.map(s => `<li>${s}</li>`).join('')}
    </ol>
  `;

  // Tampilkan modal
  document.getElementById('recipeModal').classList.remove('hidden');

  // Update views ke database
  updateViews(recipeId);
}

function closeModal() {
  document.getElementById('recipeModal').classList.add('hidden');
}

// ================================
// FUNGSI UPDATE VIEWS
// ================================

async function updateViews(recipeId) {
  await fetch(`api/update_views.php?id=${recipeId}`);
}

// ================================
// FUNGSI SEARCH & FILTER
// ================================

// Debounce: tunggu user selesai mengetik dulu (300ms)
// supaya tidak fetch setiap 1 huruf
let searchTimeout;
function onSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
}

function applyFilters() {
  const search   = document.getElementById('searchInput').value;
  const category = document.getElementById('filterCat').value;
  const diff     = document.getElementById('filterDiff').value;

  const params = {};
  if (search)   params.search   = search;
  if (category) params.category = category;

  // Filter difficulty dilakukan di frontend (tidak perlu ke server)
  fetchRecipes(params).then(() => {
    if (diff) {
      const filtered = allRecipes.filter(r => r.difficulty === diff);
      renderRecipes(filtered);
    }
  });
}

// ================================
// EVENT LISTENERS
// ================================

document.addEventListener('DOMContentLoaded', () => {
  // Muat semua resep saat halaman pertama dibuka
  fetchRecipes();

  // Search saat mengetik
  document.getElementById('searchInput')
    .addEventListener('input', onSearch);

  // Filter saat dropdown berubah
  document.getElementById('filterCat')
    .addEventListener('change', applyFilters);

  document.getElementById('filterDiff')
    .addEventListener('change', applyFilters);

  // Tutup modal saat klik di luar
  document.getElementById('recipeModal')
    .addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });

    // Tombol cuaca di navbar
  document.getElementById('weatherToggle')
    .addEventListener('click', toggleWeather);

  // Tombol cuaca di hero
  document.getElementById('heroWeatherBtn')
    .addEventListener('click', toggleWeather);

  // Tombol "Lihat Resep Rekomendasi" di banner
  document.getElementById('weatherFilterBtn')
    .addEventListener('click', filterByWeather);

});