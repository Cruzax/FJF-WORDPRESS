/* Simple include loader for static sites
   Usage: <div data-include="partials/header.html"></div>
*/
document.addEventListener('DOMContentLoaded', () => {
  const includes = document.querySelectorAll('[data-include]');
  includes.forEach(async (el) => {
    const path = el.dataset.include;
    try {
      const res = await fetch(path);
      if (!res.ok) throw new Error('Not found');
      const html = await res.text();
      el.innerHTML = html;
      // after injecting header, set active nav
      if (path.toLowerCase().includes('header')) setActiveNav();
    } catch (e) {
      // graceful fallback: show nothing and log (local file access may be blocked without a server)
      console.warn('Include failed for', path, e);
    }
  });
});

function setActiveNav() {
  const filename = location.pathname.split('/').pop() || 'index.html';
  const links = document.querySelectorAll('nav a');
  links.forEach((a) => {
    a.classList.remove('active');
    a.removeAttribute('aria-current');
    const href = a.getAttribute('href');
    if (!href) return;
    // compare just filename part
    const hrefFile = href.split('/').pop();
    if (hrefFile === filename) {
      a.classList.add('active');
      a.setAttribute('aria-current', 'page');
    }
  });
}
