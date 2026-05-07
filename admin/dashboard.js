// ================================
// LOAD DATA DASHBOARD
// ================================

async function loadDashboard() {
  try {
    const response = await fetch('../api/dashboard.php');

    // Kalau 401 berarti belum login
    if (response.status === 401) {
      window.location.href = '../index.php';
      return;
    }

    const data = await response.json();

    if (data.success) {
      renderStats(data.stats);
      renderTopRecipes(data.top_recipes);
      renderWeeklyChart(data.weekly_views);
      renderTable(data.all_recipes);
    }
  } catch (error) {
    console.error('Dashboard error:', error);
  }
}

// ================================
// RENDER STATISTIK
// ================================

function renderStats(stats) {
  document.getElementById('statRecipes').textContent = stats.total_recipes;
  document.getElementById('statRecipesSub').textContent =
    `+${stats.new_this_month} resep bulan ini`;

  document.getElementById('statLikes').textContent =
    Number(stats.total_likes).toLocaleString('id-ID');

  document.getElementById('statViews').textContent =
    Number(stats.today_views).toLocaleString('id-ID');

  const diff = stats.views_diff;
  const sub  = document.getElementById('statViewsSub');
  sub.textContent = diff >= 0
    ? `↑ ${diff} dari kemarin`
    : `↓ ${Math.abs(diff)} dari kemarin`;
  sub.style.color = diff >= 0 ? 'var(--green)' : '#c0392b';
}

// ================================
// RENDER TOP RESEP
// ================================

function renderTopRecipes(recipes) {
  const medals = ['gold', 'silver', 'bronze', '', ''];
  const maxViews = recipes[0]?.views || 1;

  document.getElementById('rankList').innerHTML = recipes.map((r, i) => `
    <li class="rank-item">
      <span class="rank-num ${medals[i]}">${i + 1}</span>
      <span class="rank-name">${r.emoji} ${r.title}</span>
      <div class="rank-bar-wrap">
        <div class="rank-bar"
          style="width: ${Math.round(r.views / maxViews * 100)}%">
        </div>
      </div>
      <span class="rank-views">
        ${(r.views / 1000).toFixed(1)}K
      </span>
    </li>
  `).join('');
}

// ================================
// RENDER GRAFIK MINGGUAN
// ================================

function renderWeeklyChart(weeklyData) {
  const chartEl = document.getElementById('weekChart');

  if (weeklyData.length === 0) {
    chartEl.innerHTML = '<p class="loading-text">Belum ada data tayangan minggu ini.</p>';
    return;
  }

  const maxVal = Math.max(...weeklyData.map(d => d.total));

  chartEl.innerHTML = weeklyData.map(day => `
    <div class="bar-col">
      <div class="bar-fill"
        style="height: ${Math.round(day.total / maxVal * 130)}px">
      </div>
      <span class="bar-label">${day.day_label}</span>
    </div>
  `).join('');
}

// ================================
// RENDER TABEL RESEP
// ================================

function renderTable(recipes) {
  // Simpan data resep ke variable global
  // supaya bisa diakses saat tombol edit diklik
  window.recipesData = recipes;

  document.getElementById('dashTableBody').innerHTML = recipes.map(r => `
    <tr>
      <td>${r.emoji} ${r.title}</td>
      <td>${r.category_name}</td>
      <td>${Number(r.views).toLocaleString('id-ID')}</td>
      <td>${Number(r.likes).toLocaleString('id-ID')}</td>
      <td>⭐ ${r.rating}</td>
      <td><span class="status-badge ${r.status}">
        ${r.status === 'published' ? 'Tayang' : 'Draft'}
      </span></td>
      <td style="display:flex; gap:0.4rem">
        <button class="btn-edit" onclick="editRecipe('${r.id}')">✏️ Edit</button>
        <button class="btn-delete" onclick="deleteRecipe('${r.id}', '${r.title.replace(/'/g, "\\'")}')">🗑️ Hapus</button>
      </td>
    </tr>
  `).join('');
}

// Fungsi helper untuk edit — cari data resep lalu buka form
function editRecipe(id) {
  const recipe = window.recipesData.find(r => r.id == id);
  if (recipe) openEditForm(recipe);
}

// ================================
// LOGOUT
// ================================

document.getElementById('logoutBtn').addEventListener('click', async () => {
  await fetch('../api/logout.php');
  window.location.href = '../index.php';
});

// ================================
// JALANKAN SAAT HALAMAN DIBUKA
// ================================

loadDashboard();

// ================================
// CRUD — STATE
// ================================

let editingId = null; // null = mode tambah, ada angka = mode edit

// ================================
// BUKA FORM TAMBAH
// ================================

function openAddForm() {
  editingId = null;
  document.getElementById('formTitle').textContent  = 'Tambah Resep Baru';
  document.getElementById('btnSave').textContent    = 'Simpan Resep';

  // Kosongkan semua field
  document.getElementById('recipeId').value     = '';
  document.getElementById('fTitle').value       = '';
  document.getElementById('fEmoji').value       = '';
  document.getElementById('fCookTime').value    = '';
  document.getElementById('fServings').value    = '';
  document.getElementById('fWeather').value     = '';
  document.getElementById('fIngredients').value = '';
  document.getElementById('fSteps').value       = '';
  document.getElementById('fCategory').value    = '1';
  document.getElementById('fDifficulty').value  = 'Mudah';
  document.getElementById('fStatus').value      = 'published';

  document.getElementById('formOverlay').classList.remove('hidden');
  setTimeout(() => document.getElementById('fTitle').focus(), 100);
}

// ================================
// BUKA FORM EDIT
// ================================

function openEditForm(recipe) {
  editingId = recipe.id;
  document.getElementById('formTitle').textContent = 'Edit Resep';
  document.getElementById('btnSave').textContent   = 'Perbarui Resep';

  // Isi field dengan data resep yang dipilih
  document.getElementById('recipeId').value  = recipe.id;
  document.getElementById('fTitle').value    = recipe.title;
  document.getElementById('fEmoji').value    = recipe.emoji;
  document.getElementById('fCookTime').value = recipe.cook_time;
  document.getElementById('fServings').value = recipe.servings;
  document.getElementById('fStatus').value   = recipe.status;

  // Weather tags: dari "hujan,mendung" tetap seperti itu
  document.getElementById('fWeather').value = recipe.weather_tags.join(',');

  // Ingredients & steps: dari array balik ke teks per baris
  document.getElementById('fIngredients').value = recipe.ingredients.join('\n');
  document.getElementById('fSteps').value       = recipe.steps.join('\n');

  // Set dropdown kategori berdasarkan nama
  const categoryMap = {
    'Nusantara': '1', 'Asia': '2', 'Internasional': '3',
    'Minuman': '4', 'Dessert': '5'
  };
  document.getElementById('fCategory').value =
    categoryMap[recipe.category_name] || '1';

  document.getElementById('fDifficulty').value = recipe.difficulty;

  document.getElementById('formOverlay').classList.remove('hidden');
}

// ================================
// TUTUP FORM
// ================================

function closeForm() {
  document.getElementById('formOverlay').classList.add('hidden');
  editingId = null;
}

// ================================
// SIMPAN RESEP (TAMBAH / EDIT)
// ================================

async function saveRecipe() {
  // Ambil nilai dari semua field
  const title       = document.getElementById('fTitle').value.trim();
  const emoji       = document.getElementById('fEmoji').value.trim();
  const cookTime    = document.getElementById('fCookTime').value.trim();
  const servings    = document.getElementById('fServings').value.trim();
  const categoryId  = document.getElementById('fCategory').value;
  const difficulty  = document.getElementById('fDifficulty').value;
  const weather     = document.getElementById('fWeather').value.trim();
  const status      = document.getElementById('fStatus').value;

  // Ubah teks per baris jadi string dengan pemisah |
  const ingredients = document.getElementById('fIngredients').value
    .split('\n')
    .map(i => i.trim())
    .filter(i => i !== '')  // hapus baris kosong
    .join('|');

  const steps = document.getElementById('fSteps').value
    .split('\n')
    .map(s => s.trim())
    .filter(s => s !== '')
    .join('|');

  // Validasi field wajib
  if (!title || !ingredients || !steps || !cookTime || !servings) {
    showToast('Mohon lengkapi semua field yang wajib diisi!', 'error');
    return;
  }

  // Nonaktifkan tombol saat proses
  const btn = document.getElementById('btnSave');
  btn.disabled    = true;
  btn.textContent = 'Menyimpan...';

  try {
    const response = await fetch('../api/manage_recipe.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action:       editingId ? 'update' : 'create',
        id:           editingId,
        title,
        category_id:  categoryId,
        emoji:        emoji || '🍽️',
        difficulty,
        cook_time:    cookTime,
        servings,
        ingredients,
        steps,
        weather_tags: weather,
        status,
      })
    });

    const data = await response.json();

    if (data.success) {
      showToast(data.message, 'success');
      closeForm();
      loadDashboard(); // Refresh data dashboard
    } else {
      showToast(data.message || 'Terjadi kesalahan', 'error');
    }
  } catch (error) {
    showToast('Gagal menyimpan resep', 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = editingId ? 'Perbarui Resep' : 'Simpan Resep';
  }
}

// ================================
// HAPUS RESEP
// ================================

async function deleteRecipe(id, title) {
  // Minta konfirmasi dulu sebelum hapus
  const confirmed = confirm(`Hapus resep "${title}"?\n\nTindakan ini tidak bisa dibatalkan.`);
  if (!confirmed) return;

  try {
    const response = await fetch('../api/manage_recipe.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id })
    });

    const data = await response.json();

    if (data.success) {
      showToast(data.message, 'success');
      loadDashboard(); // Refresh data
    } else {
      showToast('Gagal menghapus resep', 'error');
    }
  } catch (error) {
    showToast('Terjadi kesalahan', 'error');
  }
}

// ================================
// NOTIFIKASI TOAST
// ================================

function showToast(message, type = 'success') {
  // Hapus toast lama kalau masih ada
  const old = document.getElementById('toast');
  if (old) old.remove();

  const toast = document.createElement('div');
  toast.id        = 'toast';
  toast.className = `toast ${type}`;
  toast.textContent = (type === 'success' ? '✅ ' : '❌ ') + message;

  document.body.appendChild(toast);

  // Hilang otomatis setelah 3 detik
  setTimeout(() => toast.remove(), 3000);
}