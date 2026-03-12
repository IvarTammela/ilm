const http = require('http');
const https = require('https');

const PORT = 3000;

// Mõned Eesti linnad
const CITIES = {
  tallinn: { name: 'Tallinn', lat: 59.437, lon: 24.7536 },
  tartu: { name: 'Tartu', lat: 58.378, lon: 26.7290 },
  parnu: { name: 'Pärnu', lat: 58.3859, lon: 24.4971 },
  narva: { name: 'Narva', lat: 59.3797, lon: 28.1791 },
  haapsalu: { name: 'Haapsalu', lat: 58.9431, lon: 23.5414 },
};

function fetch(url) {
  return new Promise((resolve, reject) => {
    https.get(url, (res) => {
      let data = '';
      res.on('data', (chunk) => (data += chunk));
      res.on('end', () => resolve(JSON.parse(data)));
      res.on('error', reject);
    }).on('error', reject);
  });
}

async function getWeather(lat, lon) {
  const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=Europe%2FTallinn&forecast_days=7`;
  return fetch(url);
}

function weatherIcon(code) {
  if (code === 0) return '☀️';
  if (code <= 3) return '⛅';
  if (code <= 48) return '🌫️';
  if (code <= 57) return '🌦️';
  if (code <= 67) return '🌧️';
  if (code <= 77) return '🌨️';
  if (code <= 82) return '🌧️';
  if (code <= 86) return '🌨️';
  if (code <= 99) return '⛈️';
  return '❓';
}

function weatherText(code) {
  if (code === 0) return 'Selge';
  if (code <= 3) return 'Osaliselt pilves';
  if (code <= 48) return 'Udune';
  if (code <= 57) return 'Uduvihm';
  if (code <= 67) return 'Vihm';
  if (code <= 77) return 'Lumi';
  if (code <= 82) return 'Hoogsadu';
  if (code <= 86) return 'Lumesadu';
  if (code <= 99) return 'Äike';
  return 'Teadmata';
}

const WEEKDAYS = ['Pühapäev', 'Esmaspäev', 'Teisipäev', 'Kolmapäev', 'Neljapäev', 'Reede', 'Laupäev'];

function renderPage(city, data) {
  const c = data.current;
  const d = data.daily;

  let forecastRows = '';
  for (let i = 0; i < d.time.length; i++) {
    const date = new Date(d.time[i] + 'T00:00:00');
    const day = i === 0 ? 'Täna' : i === 1 ? 'Homme' : WEEKDAYS[date.getDay()];
    forecastRows += `
      <div class="day">
        <span class="day-name">${day}</span>
        <span class="day-icon">${weatherIcon(d.weather_code[i])}</span>
        <span class="day-desc">${weatherText(d.weather_code[i])}</span>
        <span class="day-temp">${Math.round(d.temperature_2m_min[i])}° / ${Math.round(d.temperature_2m_max[i])}°</span>
        <span class="day-rain">${d.precipitation_sum[i]} mm</span>
      </div>`;
  }

  let cityLinks = '<a href="/minu"' + (city.isGeo ? ' class="active"' : '') + '>Minu asukoht</a> ';
  for (const [key, val] of Object.entries(CITIES)) {
    const active = !city.isGeo && val.name === city.name ? ' class="active"' : '';
    cityLinks += `<a href="/${key}"${active}>${val.name}</a> `;
  }

  return `<!DOCTYPE html>
<html lang="et">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ilm - ${city.name}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; padding: 2rem; }
    .container { max-width: 600px; margin: 0 auto; }
    nav { text-align: center; margin-bottom: 2rem; }
    nav a { color: #94a3b8; text-decoration: none; padding: 0.4rem 0.8rem; border-radius: 6px; }
    nav a:hover { color: #e2e8f0; background: #1e293b; }
    nav a.active { color: #38bdf8; background: #1e293b; }
    .current { text-align: center; padding: 2rem; background: #1e293b; border-radius: 12px; margin-bottom: 1.5rem; }
    .current .icon { font-size: 4rem; }
    .current .temp { font-size: 3.5rem; font-weight: 700; }
    .current .desc { color: #94a3b8; font-size: 1.1rem; margin-top: 0.3rem; }
    .current .details { margin-top: 1rem; color: #94a3b8; }
    .forecast { background: #1e293b; border-radius: 12px; overflow: hidden; }
    .day { display: flex; align-items: center; padding: 0.8rem 1.2rem; border-bottom: 1px solid #334155; gap: 0.8rem; }
    .day:last-child { border-bottom: none; }
    .day-name { width: 100px; font-weight: 500; }
    .day-icon { font-size: 1.3rem; width: 35px; text-align: center; }
    .day-desc { flex: 1; color: #94a3b8; font-size: 0.9rem; }
    .day-temp { width: 80px; text-align: right; }
    .day-rain { width: 55px; text-align: right; color: #38bdf8; font-size: 0.85rem; }
  </style>
</head>
<body>
  <div class="container">
    <nav>${cityLinks}</nav>
    <div class="current">
      <div class="icon">${weatherIcon(c.weather_code)}</div>
      <div class="temp">${Math.round(c.temperature_2m)}°C</div>
      <div class="desc">${weatherText(c.weather_code)}</div>
      <div class="details">Tuul ${c.wind_speed_10m} km/h · Niiskus ${c.relative_humidity_2m}%</div>
    </div>
    <div class="forecast">${forecastRows}</div>
  </div>
</body>
</html>`;
}

function renderGeoPage() {
  return `<!DOCTYPE html>
<html lang="et">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ilm - Minu asukoht</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; padding: 2rem; display: flex; align-items: center; justify-content: center; }
    .msg { text-align: center; color: #94a3b8; font-size: 1.2rem; }
  </style>
</head>
<body>
  <div class="msg">Asukoha tuvastamine...</div>
  <script>
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (pos) => { window.location.href = '/geo?lat=' + pos.coords.latitude + '&lon=' + pos.coords.longitude; },
        () => { window.location.href = '/tallinn'; }
      );
    } else {
      window.location.href = '/tallinn';
    }
  </script>
</body>
</html>`;
}

async function reverseGeocode(lat, lon) {
  try {
    const data = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&accept-language=et`);
    return data.address.city || data.address.town || data.address.village || data.address.municipality || 'Minu asukoht';
  } catch {
    return 'Minu asukoht';
  }
}

const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://localhost');
  const path = url.pathname.replace(/\/$/, '') || '/tallinn';

  if (path === '/minu') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(renderGeoPage());
  }

  if (path === '/geo') {
    const lat = parseFloat(url.searchParams.get('lat'));
    const lon = parseFloat(url.searchParams.get('lon'));
    if (isNaN(lat) || isNaN(lon)) {
      res.writeHead(302, { Location: '/tallinn' });
      return res.end();
    }
    try {
      const [data, name] = await Promise.all([getWeather(lat, lon), reverseGeocode(lat, lon)]);
      const city = { name, lat, lon, isGeo: true };
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      return res.end(renderPage(city, data));
    } catch (err) {
      res.writeHead(500, { 'Content-Type': 'text/plain' });
      return res.end('Viga: ' + err.message);
    }
  }

  const cityKey = path.slice(1).toLowerCase();
  const city = CITIES[cityKey];

  if (!city) {
    res.writeHead(302, { Location: '/tallinn' });
    return res.end();
  }

  try {
    const data = await getWeather(city.lat, city.lon);
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(renderPage(city, data));
  } catch (err) {
    res.writeHead(500, { 'Content-Type': 'text/plain' });
    res.end('Viga ilmaandmete laadimisel: ' + err.message);
  }
});

server.listen(PORT, '0.0.0.0', () => {
  const nets = require('os').networkInterfaces();
  const ips = Object.values(nets).flat().filter(n => n.family === 'IPv4' && !n.internal).map(n => n.address);
  console.log(`Ilmaennustus töötab:`);
  console.log(`  Local:   http://localhost:${PORT}`);
  ips.forEach(ip => console.log(`  Network: http://${ip}:${PORT}`));
});
