# index.html → Multi-Page Migration Phases

## Overview
13 views currently hidden/shown via `switchPage()` in a single file.  
Goal: proper `.html` files per route, static-site compatible (Netlify).

---

## Phase 1 — Foundation
**Goal:** Set up shared infrastructure before touching any page content.

### 1.1 Create shared partials
Extract the three repeated blocks into standalone files:

- `partials/_head.html` — `<head>` contents (meta, Tailwind CDN, fonts, FA, styles.css link)
- `partials/_nav.html` — full `<nav>` block (lines 26–82), with `href` links replacing `switchPage` calls
- `partials/_footer.html` — full `<footer>` block (lines 2091–2136), with `href` links

### 1.2 Create nav highlight script
Replace the `switchPage` active-state logic with a URL-based approach.

Create `nav-highlight.js`:
```js
document.addEventListener('DOMContentLoaded', () => {
  const path = window.location.pathname;
  const map = {
    '/index.html': 'nav-home',
    '/': 'nav-home',
    '/about.html': 'nav-about',
    '/services.html': 'nav-services',
    '/contact.html': 'nav-contact',
  };
  const id = map[path];
  if (id) document.getElementById(id)?.classList.add('nav-active');
});
```

### 1.3 Define page shell template
Standard wrapper every new `.html` file will use:
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- contents of _head.html -->
  <title>PAGE TITLE | Red Wolf Security</title>
</head>
<body class="antialiased selection:bg-redwolf selection:text-white flex flex-col min-h-screen">
  <!-- contents of _nav.html -->
  <main class="flex-grow pt-20 relative">
    <div class="fixed inset-0 z-[-1] bg-[radial-gradient(...)] pointer-events-none"></div>
    <!-- PAGE CONTENT HERE -->
  </main>
  <!-- contents of _footer.html -->
  <script src="/nav-highlight.js"></script>
  <script src="/main.js"></script>
</body>
</html>
```

**Deliverables:** `partials/_head.html`, `partials/_nav.html`, `partials/_footer.html`, `nav-highlight.js`

---

## Phase 2 — Top-Level Pages
**Goal:** Migrate the 4 main nav pages. These are standalone and don't depend on service sub-pages.

### Files to create

| File | Source in index.html | Lines |
|---|---|---|
| `index.html` (rewrite) | `#home` div content only | 89–513 |
| `about.html` | `#about` div content | 515–725 |
| `services.html` | `#services` div content | 727–920 |
| `contact.html` | `#contact` div content | 1962–2088 |

### Changes per file

**`index.html`**
- Strip everything except `#home` section content
- Change hero buttons: `onclick="switchPage('services')"` → `href="/services.html"`
- Change hero buttons: `onclick="switchPage('contact')"` → `href="/contact.html"`
- Change service preview cards: `onclick="switchPage('service-*')"` → `href="/services/*.html"`
- Change CTA button: `onclick="switchPage('contact')"` → `href="/contact.html"`
- Remove `<script src="index.js">` (contains only switchPage)

**`about.html`**
- Wrap `#about` section content in page shell
- No internal nav calls to update

**`services.html`**
- Wrap `#services` section content in page shell
- Change all 9 service cards: `onclick="switchPage('service-*')"` → `href="/services/*.html"`

**`contact.html`**
- Wrap `#contact` section content in page shell
- Netlify form `data-netlify="true"` and `name="contact-form"` — keep as-is, works fine

**Deliverables:** Updated `index.html`, new `about.html`, `services.html`, `contact.html`

---

## Phase 3 — Service Detail Pages
**Goal:** Create the 9 service sub-pages under `/services/`.

### Files to create

| File | Source `id` | Lines |
|---|---|---|
| `services/guarding.html` | `#service-guarding` | 922–1180 |
| `services/cctv.html` | `#service-cctv` | 1182–1273 |
| `services/fire.html` | `#service-fire` | 1276–1333 |
| `services/solar.html` | `#service-solar` | 1335–1403 |
| `services/access-control.html` | `#service-access` | 1404–1557 |
| `services/smart-home.html` | `#service-smarthome` | 1558–1638 |
| `services/investigation.html` | `#service-investigation` | 1639–1732 |
| `services/consulting.html` | `#service-consulting` | 1733–1833 |
| `services/tracking.html` | `#service-tracking` | 1834–1961 |

### Changes per file (all identical pattern)
- Wrap content in page shell
- Change "Back to Services" button: `onclick="switchPage('services')"` → `href="/services.html"`
- Asset paths (`/assets/...`) already root-relative — no changes needed
- Remove the outer `pt-12 pb-20` from the `page-section` div (now handled by `<main>` wrapper)

**Deliverables:** 9 files in `/services/`

---

## Phase 4 — Cleanup
**Goal:** Remove all dead code and verify the migration.

### 4.1 Remove `index.js`
The entire file exists to run `switchPage()`. Once all links are `href`-based, delete or empty it. Also remove `<script src="index.js">` from `index.html`.

### 4.2 Remove `.page-section` CSS rules
In `styles.css`, find and remove rules tied to the old JS router:
- `.page-section { display: none }` / `.page-section.active`
- `.page-section.hidden`
- Any `switchPage`-related transitions

### 4.3 Verify all internal links
Checklist:
- [ ] All nav links in `_nav.html` point to correct pages
- [ ] All footer links point to correct pages
- [ ] All "Back to Services" buttons on service pages point to `/services.html`
- [ ] All service cards on `services.html` and `index.html` point to `/services/[name].html`
- [ ] Active nav highlight works on each page
- [ ] Netlify contact form submits correctly
- [ ] Video and asset paths resolve from all pages

### 4.4 Netlify `_redirects` (optional)
If you want clean URLs (e.g., `/about` instead of `/about.html`), add a `_redirects` file:
```
/about        /about.html        200
/services     /services.html     200
/contact      /contact.html      200
/services/*   /services/:splat   200
```

**Deliverables:** Cleaned `styles.css`, removed `index.js`, verified link audit, optional `_redirects`

---

## Phase Summary

| Phase | Work | Files Affected |
|---|---|---|
| 1 — Foundation | Partials + nav script | 4 new files |
| 2 — Top-Level Pages | Home, About, Services, Contact | 4 files |
| 3 — Service Pages | 9 service detail pages | 9 new files |
| 4 — Cleanup | Dead code, CSS, link audit | `index.js`, `styles.css` |

**Total new files:** 17  
**Files modified:** `index.html`, `styles.css`  
**Files deleted:** `index.js`
