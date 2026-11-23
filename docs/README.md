# Documentation: how it is organised and maintained

This folder is the single source of truth for the platform's written documentation. The same files feed the
**Admin > Documentation** section (read online, PDF via the browser's print dialog, download as printable HTML, download as Markdown). PDFs are not generated on the server: no PDF library is installed.

```
docs/
├── README.md              this file (not shown in the admin section)
├── user-guide/            the User Guide: learners, partner learners and administrators
│   ├── 01-introduction.md
│   ├── ...
│   └── images/            screenshots used by the User Guide
└── system/                the System Documentation: architecture, modules, database, workflows, configuration
    ├── 01-overview.md
    ├── ...
    └── images/            screenshots / diagrams used by the System Documentation
```

## Rules

- **One chapter = one `.md` file** named `NN-short-name.md`. The number sets the order; the chapter title is the
  file's first line, `# Title`. Use `##` and `###` for sections (the table of contents lists the `##` headings).
- **Add a chapter** by adding a file; **remove or reorder** by deleting or renumbering files. Nothing else needs to change.
- **Images**: put the file in the book's `images/` folder and reference it as `![Description](images/name.png)`.
  Use plain file names (letters, digits, `.`, `_`, `-`).
- Raw HTML in the Markdown is ignored. Tables, fenced code blocks and blockquotes are supported.
- Write only what the system really does. When you change a feature, update its chapter in the same change.
- Never put passwords, keys or real personal data in the documentation.

## Updating when the system changes

| Change | Update |
|---|---|
| New screen, button or setting | the matching chapter in `user-guide/` |
| New admin module or permission | `user-guide/23-admin-roles-permissions.md` (matrix) and `system/03-modules.md` |
| New table or column | `system/04-database.md` |
| New business rule or workflow | `system/05-workflows.md` and the learner/admin chapter that explains it |
| New environment variable or integration | `system/06-integrations-and-configuration.md` |

The "Updated" date shown in the admin section and on downloads is the date of the most recently changed file in the book.
