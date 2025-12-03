FixItMati Dashboard (PHP)

Structure
- `public/index.php`: Main PHP template rendering the dashboard.
- `assets/style.css`: Additional styles (beyond Tailwind CDN).
- `assets/app.js`: Basic interactions (mobile menu, tabs).

Requirements
- Internet access for CDNs: Tailwind (`cdn.tailwindcss.com`) and Lucide icons (`unpkg.com/lucide`).
- Any PHP-capable web server (Apache, Nginx, or PHP built-in server).

Run Locally (Windows PowerShell)
```
php -S localhost:8080 -t public
```
Then open `http://localhost:8080` in your browser.

Notes
- Icons are provided by Lucide via CDN. No React is used.
- Tailwind is loaded via CDN to preserve the original utility-class styling.
# fix-it-mati