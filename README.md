Project notes — partial includes

- Header and footer are now split into `partials/header.html` and `partials/footer.html`.
- `assets/js/includes.js` injects partials into elements that have `data-include="partials/xxx.html"`.
- For local testing, serve the folder with a static server (e.g., `python -m http.server 8000`) because `fetch()` may be blocked when opening files via `file://`.

If you want, I can also add a build step (Gulp, eleventy, or a simple `npm` script) to produce a single static site without client-side includes.
