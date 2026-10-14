# Contributing

## Branching

`main` is the default branch and always reflects what is released (or about to be
released). Work on a feature branch and open a pull request against `main`.

## Commit messages

Releases and the changelog are generated from the commit history, so commit messages
follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope][!]: <description>

[optional body]

[optional footer]
```

| Type | Effect on the version | Appears in changelog |
|---|---|---|
| `fix:` | patch (2.0.0 → 2.0.1) | yes, "Bug Fixes" |
| `feat:` | minor (2.0.0 → 2.1.0) | yes, "Features" |
| `feat!:` or `BREAKING CHANGE:` footer | major (2.0.0 → 3.0.0) | yes, highlighted |
| `docs:`, `refactor:`, `chore:`, `deps:`, `style:`, `test:`, `ci:` | none | no |

A pull request that should trigger a release needs at least one `fix:` or `feat:`
commit. When squash-merging, make sure the squash commit message itself is a
conventional commit — that is the message release-please reads.

### Which changes get `fix:` or `feat:`

Only changes that matter to someone using the plugin. `fix:` and `feat:` decide the
version *and* write the line that ends up in the changelog on the wordpress.org
plugin page, so the question to ask before committing is whether a user of the plugin
would care about that line.

Everything else takes a type that releases nothing — workflows and CI, release
tooling, repository documentation, internal refactoring, and anything touching files
that are not shipped. As a rule of thumb, a change confined to files outside
`public/` is almost never a `fix:`.

That includes hardening. Blocking direct access to a file that is not part of the
download is `chore:`, not `fix:` — nothing changes for anyone who installed the
plugin.

`src/` is the exception to the "outside `public/`" rule: it is compiled into
`public/dist/` and does reach users, so a change there can be a `fix:` or `feat:`
even though the files live outside `public/`.

## Repository layout

See [README.md](README.md#repository-layout).

## Local setup

```sh
npm ci
npm run build                     # → public/dist/
(cd public && composer install)   # → public/vendor/
npx wp-env start                  # http://localhost:8888, admin / password
```

`npm run pack` stages the payload in `build/palasthotel-integration-for-prolitteris/` and zips it to
`palasthotel-integration-for-prolitteris.zip` — the same payload the release deploys. It runs the shared script
from [palasthotel/github-workflows](https://github.com/palasthotel/github-workflows),
which has to be checked out next to this repository, and needs `composer`, because the
packed copy gets its dependencies installed without dev dependencies and the composer
files are dropped from it. Run `npm run build` first.

The main file `public/palasthotel-integration-for-prolitteris.php` must keep its name. WordPress identifies an
installed plugin by `<directory>/<main file>` and stores that pair in `active_plugins`;
renaming it deactivates the plugin on every site at the next update.

`public/dist/` and `public/vendor/` are generated and gitignored. The release builds them, so there is nothing
to commit and no stale asset to review.

## Versions

Never edit version numbers by hand. `package.json`, `CHANGELOG.md`,
`public/palasthotel-integration-for-prolitteris.php` and the `Stable tag:` in `public/readme.txt` are all
maintained by the release pipeline — see
[.github/WORKFLOWS.md](.github/WORKFLOWS.md).

Content changes to `public/readme.txt` (description, FAQ, screenshots, tested-up-to)
are of course done by hand; just leave `Stable tag:` and the `== Changelog ==`
entries alone.

## Checks

Every PR runs `php -l` against PHP 8.2 to 8.5, builds the editor sidebar, packs the
plugin and asserts that the payload holds every file the plugin enqueues and none of the
repository-only ones, and checks the version carriers agree.
