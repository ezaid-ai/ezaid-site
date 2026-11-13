# ezaid-site
ezaid.ai website

## Structure
- `index.html`, `about/`, `contact/`, `terms/`, `privacy/`: the public pages (generated).
- `sitemap.xml`, `robots.txt`: for search engines.
- `img/og.png`: social preview image (1200x630).
- `_source/template.html`: the whole site in one file. Edit this one.
- `_source/build.py`: regenerates all pages and the sitemap. Run `python3 _source/build.py` after every edit.
  Do not upload the `_source` folder to the web server.

## After publishing
1. In Google Search Console, submit `https://ezaid.ai/sitemap.xml`.
2. Use URL inspection and "Request indexing" for `/`, `/about/` and `/contact/`.
3. Old links like `ezaid.ai/#about` are redirected to `/about/` automatically.
