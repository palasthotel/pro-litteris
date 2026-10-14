# CI/CD Workflows

The four workflows in `.github/workflows/` call the shared ones in
[palasthotel/github-workflows](https://github.com/palasthotel/github-workflows). How
they work, every input and what to do when a deploy fails is described there, in
[docs/wp-plugin.md](https://github.com/palasthotel/github-workflows/blob/main/docs/wp-plugin.md).

What is specific to this plugin:

| | |
|---|---|
| wordpress.org slug | `palasthotel-integration-for-prolitteris` |
| version file | `package.json` (`release-type: node`) |
| build step | `npm ci && npm run build` in `pr.yml` and the deploy - `public/dist/` (the editor sidebar) is not in the repository |
| `required-files` | the built `dist/gutenberg.js` and `.asset.php`, the composer autoloader and html2text, the WP-CLI commands |
| composer | `public/composer.json` requires `html2text/html2text`; the pack installs it without dev dependencies and drops `composer.json`/`composer.lock` from the payload. `public/vendor/` is not in the repository |
| `php -l` | 8.2 to 8.5 - `Requires PHP: 8.2` |
| `assets/` | not in the repository yet; once added, it is mirrored into the SVN `assets/` on every release |
