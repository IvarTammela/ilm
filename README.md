# Ilm - Eesti ilmaennustus

**Live:** [ilm.serveriteenused.ee](https://ilm.serveriteenused.ee)

Lihtne ilmaennustus Eesti linnadele. Kasutab tasuta [Open-Meteo](https://open-meteo.com/) API-t.

## Funktsioonid

- Praegune ilm (temperatuur, tuul, niiskus)
- 7 päeva prognoos
- 14 Eesti linna
- Kohanime otsing (töötab iga kohaga maailmas)
- Geolokatsioon (brauseri GPS)
- Kaart (Leaflet + OpenStreetMap)
- Tume disain, mobiilisõbralik
- API võtit pole vaja

## PHP versioon (veebimajutus)

Lae `index.php` ja `.htaccess` üles veebimajutuse kausta. Töötab igal PHP hostingul.

## Node.js versioon (lokaalne)

```bash
git clone https://github.com/IvarTammela/ilm.git
cd ilm
node index.js
```

Ava brauseris: http://localhost:3000
