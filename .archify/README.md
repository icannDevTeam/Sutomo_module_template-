# Sutomo flow library

The library contains 13 source-backed Archify diagrams: one system architecture
and three workflows each for enrollment, leave/substitution, contract management
and planning.

## View after pulling

From the repository root:

```bash
php -S 127.0.0.1:8085 -t .archify
```

Open <http://127.0.0.1:8085/>. This static library does not require Composer,
Node, the Laravel server, a database or Archify installation. Alternatively,
open `.archify/index.html` directly in a browser.

GitHub displays HTML source rather than running these interactive pages.
Clone/pull the repository (or download its ZIP), then view locally. No GitHub
Pages deployment is configured by this change.

## Collections

- [System architecture](architecture-sutomo-hr-20261012-081500/sutomo-hr.html)
- [Enrollment](workflow-enrollment-20261012-084142/index.html)
- [Leave and substitution](workflow-leave-substitution-20261012-090931/index.html)
- [Contract management](workflow-contracts-20261012-131158/index.html)
- [Planning](workflow-planning-20261012-143425/index.html)

The module explorers use relative links so all collections work under a single
server or from a downloaded directory.

## Evidence and maintenance

Each diagram retains its `candidate.json` specification with repository-relative
source references and the pinned revision
`33d3bbd42e2ba76df5dfc3ac2fc1ef818c280aa3`. The diagrams describe that implementation,
including placeholders and unenforced gates, rather than promised features.

All 13 diagrams passed Archify validation, artifact/provenance checks and
real-browser checks at generation time. Machine-local receipts, screenshots and
repair evidence are excluded from Git; committed HTML and specifications are
the portable deliverables.

To regenerate, use Archify 3.0.1 with the relevant `candidate.json` and
`meta.output` path, running from the repository root:

```bash
node /path/to/archify/bin/archify.mjs finalize workflow \
  .archify/<collection>/<flow>/candidate.json \
  .archify/<collection>/<flow>/<flow>.html \
  --repo-root . --quality showcase --json
```

Use `architecture` instead of `workflow` for the system overview. Re-trace source
and update the pinned revision when application behavior changes. Archify tooling
is not vendored as part of this diagram-only commit.
