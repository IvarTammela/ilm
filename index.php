<?php
$cities = [
    'tallinn'  => ['name' => 'Tallinn',  'lat' => 59.437,  'lon' => 24.7536],
    'tartu'    => ['name' => 'Tartu',    'lat' => 58.378,  'lon' => 26.7290],
    'parnu'    => ['name' => 'Pärnu',    'lat' => 58.3859, 'lon' => 24.4971],
    'narva'    => ['name' => 'Narva',    'lat' => 59.3797, 'lon' => 28.1791],
    'haapsalu' => ['name' => 'Haapsalu', 'lat' => 58.9431, 'lon' => 23.5414],
];

$page = isset($_GET['linn']) ? strtolower($_GET['linn']) : '';
$isGeo = isset($_GET['lat']) && isset($_GET['lon']);

if ($isGeo) {
    $lat = floatval($_GET['lat']);
    $lon = floatval($_GET['lon']);
    $geoName = 'Minu asukoht';
    $ctx = stream_context_create(['http' => ['header' => "User-Agent: IlmApp/1.0\r\n"]]);
    $rev = @file_get_contents("https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lon}&format=json&accept-language=et", false, $ctx);
    if ($rev) {
        $revData = json_decode($rev, true);
        $a = $revData['address'] ?? [];
        $geoName = $a['city'] ?? $a['town'] ?? $a['village'] ?? $a['municipality'] ?? 'Minu asukoht';
    }
    $city = ['name' => $geoName, 'lat' => $lat, 'lon' => $lon];
} elseif ($page === 'minu') {
    ?><!DOCTYPE html>
<html lang="et"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Ilm - Asukoht</title>
<style>*{margin:0;padding:0}body{font-family:-apple-system,system-ui,sans-serif;background:#0f172a;color:#94a3b8;min-height:100vh;display:flex;align-items:center;justify-content:center;font-size:1.2rem}</style>
</head><body>Asukoha tuvastamine...<script>
function goTo(lat,lon){window.location.href='?lat='+lat+'&lon='+lon}
function ipFallback(){fetch('https://ipwho.is/').then(r=>r.json()).then(d=>{if(d.latitude&&d.longitude)goTo(d.latitude,d.longitude);else window.location.href='?linn=tallinn'}).catch(()=>{window.location.href='?linn=tallinn'})}
if(navigator.geolocation){navigator.geolocation.getCurrentPosition(function(p){goTo(p.coords.latitude,p.coords.longitude)},ipFallback,{timeout:3000})}else{ipFallback()}
</script></body></html><?php
    exit;
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
    nav{text-align:center;margin-bottom:2rem}
    nav a{color:#94a3b8;text-decoration:none;padding:.4rem .8rem;border-radius:6px}
    nav a:hover{color:#e2e8f0;background:#1e293b}
    nav a.active{color:#38bdf8;background:#1e293b}
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
    <nav>
      <a href="?linn=minu"<?= $isGeo ? ' class="active"' : '' ?>>Minu asukoht</a>
      <?php foreach ($cities as $key => $val): ?>
        <a href="?linn=<?= $key ?>"<?= (!$isGeo && $page === $key) ? ' class="active"' : '' ?>><?= $val['name'] ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="current">
      <div class="icon"><?= weatherIcon($c['weather_code']) ?></div>
      <div class="temp"><?= round($c['temperature_2m']) ?>°C</div>
      <div class="desc"><?= weatherText($c['weather_code']) ?></div>
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
</body>
</html>
