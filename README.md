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

### Variant 1: ngrok

1. Paigalda: `brew install ngrok`
2. Loo tasuta konto: https://dashboard.ngrok.com/signup
3. Lisa authtoken: `ngrok config add-authtoken SINU_TOKEN`
4. Loo tasuta domeen: https://dashboard.ngrok.com/domains

```bash
ngrok http 3000 --url=sinu-domeen.ngrok-free.dev
```

### Variant 2: Cloudflare (kontota)

```bash
brew install cloudflared
cloudflared tunnel --url http://localhost:3000
```

Terminali ilmub avalik URL, mida saab jagada.

## Linnad

| Linn | URL |
|------|-----|
| Tallinn | `/tallinn` |
| Tartu | `/tartu` |
| Pärnu | `/parnu` |
| Narva | `/narva` |
| Haapsalu | `/haapsalu` |
