<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Ganti dengan API key kamu
define('WEATHER_API_KEY', '434af372e6de47615932459b77dabff1');
define('WEATHER_API_URL', 'https://api.openweathermap.org/data/2.5/weather');

// Ambil koordinat dari request JavaScript
// Kalau tidak ada, pakai default Kota Surabaya
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : -7.2575;
$lon = isset($_GET['lon']) ? floatval($_GET['lon']) : 112.7521;

// Bangun URL request ke OpenWeatherMap
$url = WEATHER_API_URL
     . "?lat={$lat}"
     . "&lon={$lon}"
     . "&appid=" . WEATHER_API_KEY
     . "&units=metric"    // suhu dalam Celsius
     . "&lang=id";        // deskripsi dalam Bahasa Indonesia

// Ambil data dari OpenWeatherMap
$response = file_get_contents($url);

if ($response === false) {
  echo json_encode(['success' => false, 'message' => 'Gagal mengambil data cuaca']);
  exit;
}

$weather = json_decode($response, true);

// Ambil info penting dari response
$temp        = round($weather['main']['temp']);
$feels_like  = round($weather['main']['feels_like']);
$humidity    = $weather['main']['humidity'];
$description = $weather['weather'][0]['description'];
$weather_id  = $weather['weather'][0]['id'];
$city        = $weather['name'];

// ================================
// TENTUKAN KATEGORI CUACA KITA
// Berdasarkan kode cuaca OpenWeatherMap
// ================================
// Kode 2xx = Badai/Petir
// Kode 3xx = Gerimis
// Kode 5xx = Hujan
// Kode 6xx = Salju
// Kode 7xx = Kabut/Berkabut
// Kode 800 = Cerah
// Kode 80x = Berawan

if ($weather_id >= 200 && $weather_id < 300) {
  $category = 'hujan';
  $icon     = '⛈️';
  $label    = 'Badai & Petir';
} elseif ($weather_id >= 300 && $weather_id < 400) {
  $category = 'hujan';
  $icon     = '🌦️';
  $label    = 'Gerimis';
} elseif ($weather_id >= 500 && $weather_id < 600) {
  $category = 'hujan';
  $icon     = '🌧️';
  $label    = 'Hujan';
} elseif ($weather_id >= 700 && $weather_id < 800) {
  $category = 'mendung';
  $icon     = '🌫️';
  $label    = 'Berkabut';
} elseif ($weather_id === 800) {
  // Cerah tapi cek suhunya
  if ($temp >= 33) {
    $category = 'panas';
    $icon     = '🌡️';
    $label    = 'Panas & Terik';
  } else {
    $category = 'cerah';
    $icon     = '☀️';
    $label    = 'Cerah';
  }
} elseif ($weather_id > 800) {
  if ($temp <= 24) {
    $category = 'sejuk';
    $icon     = '🌤️';
    $label    = 'Berawan & Sejuk';
  } else {
    $category = 'mendung';
    $icon     = '⛅';
    $label    = 'Mendung';
  }
} else {
  $category = 'cerah';
  $icon     = '🌤️';
  $label    = 'Cerah';
}

// ================================
// REKOMENDASI MASAKAN PER CUACA
// ================================
$recommendations = [
  'hujan'  => 'Soto, bakso, atau sup hangat sangat cocok dinikmati saat hujan!',
  'panas'  => 'Minuman dingin, es krim, atau salad segar — pas banget buat cuaca terik!',
  'cerah'  => 'Nasi goreng, grill, atau barbecue — hari yang sempurna untuk masak apa saja!',
  'mendung'=> 'Rendang atau makanan berkuah yang mengenyangkan cocok untuk cuaca mendung.',
  'sejuk'  => 'Makanan hangat berkuah atau kue hangat sangat cocok untuk hari yang sejuk.',
  'dingin' => 'Semua makanan hangat dan berat sangat cocok untuk menghangatkan badan!',
];

echo json_encode([
  'success'        => true,
  'city'           => $city,
  'temp'           => $temp,
  'feels_like'     => $feels_like,
  'humidity'       => $humidity,
  'description'    => ucfirst($description),
  'category'       => $category,
  'icon'           => $icon,
  'label'          => $label,
  'recommendation' => $recommendations[$category],
]);
?>