<?php
header('Permissions-Policy: geolocation=(self)');

$cities = [
    'tallinn'  => ['name' => 'Tallinn',  'lat' => 59.437,  'lon' => 24.7536],
    'tartu'    => ['name' => 'Tartu',    'lat' => 58.378,  'lon' => 26.7290],
    'parnu'    => ['name' => 'Pärnu',    'lat' => 58.3859, 'lon' => 24.4971],
    'narva'    => ['name' => 'Narva',    'lat' => 59.3797, 'lon' => 28.1791],
    'viljandi' => ['name' => 'Viljandi', 'lat' => 58.3639, 'lon' => 25.5900],
    'haapsalu' => ['name' => 'Haapsalu', 'lat' => 58.9431, 'lon' => 23.5414],
    'rakvere'  => ['name' => 'Rakvere', 'lat' => 59.3469, 'lon' => 26.3549],
    'kuressaare' => ['name' => 'Kuressaare', 'lat' => 58.2481, 'lon' => 22.5038],
    'johvi'    => ['name' => 'Jõhvi',    'lat' => 59.3540, 'lon' => 27.4217],
    'valga'    => ['name' => 'Valga',    'lat' => 57.7764, 'lon' => 26.0315],
    'voru'     => ['name' => 'Võru',     'lat' => 57.8339, 'lon' => 27.0170],
    'polva'    => ['name' => 'Põlva',    'lat' => 58.0535, 'lon' => 27.0566],
    'rapla'    => ['name' => 'Rapla',    'lat' => 59.0075, 'lon' => 24.7928],
    'paide'    => ['name' => 'Paide',    'lat' => 58.8856, 'lon' => 25.5573],
];

$page = isset($_GET['linn']) ? strtolower($_GET['linn']) : '';
$isGeo = isset($_GET['lat']) && isset($_GET['lon']);
$isSearch = isset($_GET['otsing']) && $_GET['otsing'] !== '';

// Otsing - Open-Meteo geocoding API
if ($isSearch) {
    $q = urlencode($_GET['otsing']);
    $geo = @file_get_contents("https://geocoding-api.open-meteo.com/v1/search?name={$q}&count=1&language=et");
    if ($geo) {
        $geoData = json_decode($geo, true);
        if (!empty($geoData['results'][0])) {
            $r = $geoData['results'][0];
            header("Location: ?lat={$r['latitude']}&lon={$r['longitude']}");
            exit;
        }
    }
    header('Location: ?linn=tallinn');
    exit;
}

if ($isGeo) {
    $lat = floatval($_GET['lat']);
    $lon = floatval($_GET['lon']);
    $geoName = 'Tundmatu';
    $ctx = stream_context_create(['http' => ['header' => "User-Agent: IlmApp/1.0\r\n"]]);
    $rev = @file_get_contents("https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lon}&format=json&accept-language=et", false, $ctx);
    if ($rev) {
        $revData = json_decode($rev, true);
        $a = $revData['address'] ?? [];
        $geoName = $a['city'] ?? $a['town'] ?? $a['village'] ?? $a['municipality'] ?? 'Tundmatu';
    }
    $city = ['name' => $geoName, 'lat' => $lat, 'lon' => $lon];
} elseif (isset($cities[$page])) {
    $city = $cities[$page];
} else {
    header('Location: ?linn=tallinn');
    exit;
}

$url = "https://api.open-meteo.com/v1/forecast?latitude={$city['lat']}&longitude={$city['lon']}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=Europe%2FTallinn&forecast_days=7";
$json = file_get_contents($url);
$data = json_decode($json, true);
$c = $data['current'];
$d = $data['daily'];

function weatherIcon($code) {
    if ($code === 0) return '☀️';
    if ($code <= 3) return '⛅';
    if ($code <= 48) return '🌫️';
    if ($code <= 57) return '🌦️';
    if ($code <= 67) return '🌧️';
    if ($code <= 77) return '🌨️';
    if ($code <= 82) return '🌧️';
    if ($code <= 86) return '🌨️';
    if ($code <= 99) return '⛈️';
    return '❓';
}

function weatherText($code) {
    if ($code === 0) return 'Selge';
    if ($code <= 3) return 'Osaliselt pilves';
    if ($code <= 48) return 'Udune';
    if ($code <= 57) return 'Uduvihm';
    if ($code <= 67) return 'Vihm';
    if ($code <= 77) return 'Lumi';
    if ($code <= 82) return 'Hoogsadu';
    if ($code <= 86) return 'Lumesadu';
    if ($code <= 99) return 'Äike';
    return 'Teadmata';
}

$weekdays = ['Pühapäev','Esmaspäev','Teisipäev','Kolmapäev','Neljapäev','Reede','Laupäev'];
?><!DOCTYPE html>
<html lang="et">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ilm - <?= htmlspecialchars($city['name']) ?></title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:-apple-system,system-ui,sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;padding:2rem}
    .container{max-width:600px;margin:0 auto}
    nav{text-align:center;margin-bottom:1rem;display:flex;flex-wrap:wrap;justify-content:center;gap:.3rem}
    nav a{color:#94a3b8;text-decoration:none;padding:.3rem .6rem;border-radius:6px;font-size:.9rem}
    nav a:hover{color:#e2e8f0;background:#1e293b}
    nav a.active{color:#38bdf8;background:#1e293b}
    .search{text-align:center;margin-bottom:1.5rem}
    .search form{display:inline-flex;gap:.5rem}
    .search input{background:#1e293b;border:1px solid #334155;color:#e2e8f0;padding:.5rem .8rem;border-radius:8px;font-size:1rem;width:200px}
    .search input::placeholder{color:#64748b}
    .search button{background:#1e40af;color:#fff;border:none;padding:.5rem 1rem;border-radius:8px;cursor:pointer;font-size:1rem}
    .search button:hover{background:#1d4ed8}
    .geo-btn{background:none;border:1px solid #334155;color:#38bdf8;padding:.3rem .6rem;border-radius:6px;cursor:pointer;font-size:.9rem}
    .geo-btn:hover{background:#1e293b}
    .current{text-align:center;padding:2rem;background:#1e293b;border-radius:12px;margin-bottom:1.5rem}
    .current .icon{font-size:4rem}
    .current .temp{font-size:3.5rem;font-weight:700}
    .current .desc{color:#94a3b8;font-size:1.1rem;margin-top:.3rem}
    .current .details{margin-top:1rem;color:#94a3b8}
    .forecast{background:#1e293b;border-radius:12px;overflow:hidden}
    .day{display:flex;align-items:center;padding:.8rem 1.2rem;border-bottom:1px solid #334155;gap:.8rem}
    .day:last-child{border-bottom:none}
    .day-name{width:100px;font-weight:500}
    .day-icon{font-size:1.3rem;width:35px;text-align:center}
    .day-desc{flex:1;color:#94a3b8;font-size:.9rem}
    .day-temp{width:80px;text-align:right}
    .day-rain{width:55px;text-align:right;color:#38bdf8;font-size:.85rem}
  </style>
</head>
<body>
  <div class="container">
    <div class="search">
      <form method="get">
        <input type="text" name="otsing" placeholder="Otsi kohta..." value="<?= htmlspecialchars($_GET['otsing'] ?? '') ?>">
        <button type="submit">Otsi</button>
      </form>
    </div>
    <nav>
      <button class="geo-btn" onclick="getGPS()">📍 Minu asukoht</button>
      <?php foreach ($cities as $key => $val): ?>
        <a href="?linn=<?= $key ?>"<?= (!$isGeo && $page === $key) ? ' class="active"' : '' ?>><?= $val['name'] ?></a>
      <?php endforeach; ?>
    </nav>
    <div id="map" style="width:100%;height:200px;border-radius:12px;margin-bottom:1.5rem"></div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
      var m=L.map('map',{zoomControl:false,attributionControl:false}).setView([<?= $city['lat'] ?>,<?= $city['lon'] ?>],12);
      L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(m);
      L.marker([<?= $city['lat'] ?>,<?= $city['lon'] ?>]).addTo(m);
    </script>
    <div class="current">
      <div class="icon"><?= weatherIcon($c['weather_code']) ?></div>
      <div class="temp"><?= round($c['temperature_2m']) ?>°C</div>
      <div class="desc"><?= htmlspecialchars($city['name']) ?> · <?= weatherText($c['weather_code']) ?></div>
      <div class="details">Tuul <?= $c['wind_speed_10m'] ?> km/h · Niiskus <?= $c['relative_humidity_2m'] ?>%</div>
    </div>
    <div class="forecast">
      <?php for ($i = 0; $i < count($d['time']); $i++):
        $date = new DateTime($d['time'][$i]);
        if ($i === 0) $dayName = 'Täna';
        elseif ($i === 1) $dayName = 'Homme';
        else $dayName = $weekdays[(int)$date->format('w')];
      ?>
      <div class="day">
        <span class="day-name"><?= $dayName ?></span>
        <span class="day-icon"><?= weatherIcon($d['weather_code'][$i]) ?></span>
        <span class="day-desc"><?= weatherText($d['weather_code'][$i]) ?></span>
        <span class="day-temp"><?= round($d['temperature_2m_min'][$i]) ?>° / <?= round($d['temperature_2m_max'][$i]) ?>°</span>
        <span class="day-rain"><?= $d['precipitation_sum'][$i] ?> mm</span>
      </div>
      <?php endfor; ?>
    </div>
  </div>
<script>
function getGPS(){
  if(!navigator.geolocation){return}
  navigator.geolocation.getCurrentPosition(
    function(p){window.location.href='?lat='+p.coords.latitude+'&lon='+p.coords.longitude},
    function(e){
      var btn=document.querySelector('.geo-btn');
      if(e.code===1) btn.textContent='⚠ Asukoht blokeeritud – kliki lukuikoonil → Lähtesta load';
      else btn.textContent='⚠ Asukohta ei saadud – kasuta otsingut';
    },
    {timeout:10000,enableHighAccuracy:true}
  );
}
</script>
</body>
</html>
