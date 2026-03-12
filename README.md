# Ilm - Eesti ilmaennustus

Lihtne ilmaennustus 5 Eesti linnale. Kasutab tasuta [Open-Meteo](https://open-meteo.com/) API-t.

![Node.js](https://img.shields.io/badge/Node.js-18+-green)

## Funktsioonid

- Praegune ilm (temperatuur, tuul, niiskus)
- 7 päeva prognoos
- Tallinn, Tartu, Pärnu, Narva, Haapsalu
- Tume disain, mobiilisõbralik
- API võtit pole vaja

## Käivitamine

```bash
git clone https://github.com/IvarTammela/ilm.git
cd ilm
node index.js
```

Ava brauseris: http://localhost:3000

## Avalik ligipääs (tunnel)

Kui tahad, et teised saaksid igalt poolt ligi:

```bash
# Paigalda cloudflared (macOS)
brew install cloudflared

# Käivita tunnel
cloudflared tunnel --url http://localhost:3000
```

Terminali ilmub avalik URL (nt `https://xxx.trycloudflare.com`), mida saab jagada.

## Linnad

| Linn | URL |
|------|-----|
| Tallinn | `/tallinn` |
| Tartu | `/tartu` |
| Pärnu | `/parnu` |
| Narva | `/narva` |
| Haapsalu | `/haapsalu` |
