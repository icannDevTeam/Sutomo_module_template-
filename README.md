# Sutomo HR Module

Recruitment, onboarding, and master-database SPA for Yayasan Sutomo.

**Stack:** vanilla JavaScript · Tailwind CDN · Lucide icons · Chart.js 4

## Local development

```sh
python3 -m http.server 8000
# open http://127.0.0.1:8000
```

## Deployment

Static site — deploys to Vercel with zero config (`vercel.json` included).

## Files

- `index.html` — entry shell
- `style.css` — design tokens + components
- `data.js` — mock data layer (teachers, vacancies, applications, ...)
- `components.js` — shared UI primitives (sidebar, topbar, cards)
- `pages.js` — page renderers (dashboard, master DB, recruitment, ...)
- `script.js` — router + state + event delegation
