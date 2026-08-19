# Petrogistix HR Admin Portal

Internal portal for HR to post jobs and review candidate applications submitted
through the petrogistix.com careers site, with an AI layer (Anthropic) that
scores each candidate's fit against the job and surfaces a calibrated top-10
shortlist per role. Access is restricted to authorized personnel via login.

This is a separate repo from the public careers site, and **connects to the
same MySQL database** (`jobs` + `applications` tables) that site writes to —
this portal doesn't duplicate that data, it reads/scores/manages it.

## Stack

- Backend: PHP 8.1+, server-rendered pages (no framework), PDO/MySQL, session auth
- Frontend: TypeScript (Vite), compiled into a couple of small progressive-enhancement
  bundles (`admin.js`/`admin.css`) included by the PHP pages — no SPA, no client routing
- AI: Anthropic Messages API via raw cURL (`backend/src/AnthropicClient.php`), same
  pattern as the careers site

## Directory layout

```
backend/
  config/        .env loader, PDO connection
  public/        webserver document root
    login/, dashboard/, jobs/, applications/, api/
    assets/       admin.css / admin.js (built by frontend/)
  src/           Auth, JobRepository, ApplicationRepository, AnthropicClient,
                 CandidateScorer, Response, views/ (header/footer/job-form)
  scripts/       create-admin.php (CLI)
  sql/           schema.sql + migrations/
  storage/uploads/   CV files (outside the public webroot)

frontend/
  src/
    api.ts, main.ts
    components/  scoreButton.ts, tableSearch.ts
    styles/admin.css
  vite.config.ts  builds into ../backend/public/assets with fixed filenames
```

## Setup

### 0. Prerequisites

- PHP 8.1+ on your PATH (check with `php -v`; `zsh: command not found: php`
  means it's simply not installed yet — this is normal on a stock Mac, Xcode
  Command Line Tools don't include PHP). If you don't have it:
  - macOS: `brew install php` (takes a few minutes; installs PHP 8.x with
    curl/fileinfo/zip/pdo_mysql already enabled). Verify after with
    `php -v` and `php -m | grep -Ei "pdo|curl|fileinfo|zip"`.
  - Windows: install XAMPP, or a standalone PHP build (see the two gotchas
    at the bottom of this section if you go standalone)
- MySQL/MariaDB running locally, with a database created for this project.
  If `mysql -u root -e "SELECT 1"` fails (`command not found`, or
  `Connection refused` / `SQLSTATE[HY000] [2002]` from the PHP app — that
  error means the *app* can't reach a server, not a code bug):
  - macOS: `brew install mysql && brew services start mysql`, then connect
    with **the full path**, `/usr/local/opt/mysql/bin/mysql -u root`, since
    a Python/Anaconda install often shadows `mysql` on PATH with its own
    (client-only, no server) copy — `which mysql` shows you which one wins.
    Fresh Homebrew installs have **no root password** by default.
  - Windows/XAMPP: start MySQL from the XAMPP control panel.
- Node 18+ (`node -v`) for the frontend build

### 1. Database

```bash
# Create the database first (schema.sql assumes it already exists):
mysql -u root -e "CREATE DATABASE IF NOT EXISTS petrogistix;"

# Fresh DB (also creates jobs/applications, which the careers-site repo
# would otherwise own):
mysql -u root petrogistix < backend/sql/schema.sql

# ...or, if you already have the careers-site DB, only add what this portal
# needs on top of its existing jobs/applications tables:
mysql -u root petrogistix < backend/sql/migrations/001_hr_portal.sql
```

(On macOS with the Homebrew/Anaconda PATH clash above, use
`/usr/local/opt/mysql/bin/mysql` instead of `mysql`. Add `-p` and enter a
password if you set one — a fresh Homebrew install has none, so plain
`-u root` works.)

### 2. Backend config

```bash
cp backend/.env.example backend/.env
```

Edit `backend/.env` with real values — at minimum `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS`.
Leave `ANTHROPIC_API_KEY` blank for now if you don't have one yet; everything
except AI scoring still works.

### 3. Create your first login

```bash
php backend/scripts/create-admin.php hr@petrogistix.com "HR Admin" admin
```

It'll prompt for a password (hidden input, 8+ characters). This is the
account you'll use to sign in at `/login/`.

### 4. Start the backend (Terminal 1)

```bash
cd backend
php -S localhost:8080 -t public router.php
```

Leave this running. Visit **http://localhost:8080/** — it should redirect to
`/login/`. This works even before you touch the frontend at all, because
`backend/public/assets/admin.css`/`admin.js` are checked in as empty
placeholders; the pages just render unstyled until you build the real assets.

### 5. Build/watch the frontend (Terminal 2)

```bash
cd frontend
npm install
npm run build     # one-time build into ../backend/public/assets/
```

Then refresh http://localhost:8080/ — it should now be styled.

**Important — what `npm run dev` actually does here:** this project has no
single-page app. Every real page (`/login/`, `/dashboard/`, `/jobs/`,
`/applications/...`) is server-rendered PHP served by the backend on `:8080`.
The `frontend/` package only builds a small JS/CSS bundle
(`admin.js`/`admin.css`) that those PHP pages `<link>`/`<script src>` in.

So `npm run dev` starts a Vite dev server on **http://localhost:5173**, not
the portal — if you open `:5173` you'll see a placeholder page (or nothing),
not the HR portal, because there's nothing at `/` for Vite to serve except a
dev-only stub. If you were expecting the actual site there, that's the
"it's not starting" you ran into — you want **http://localhost:8080/**
(Terminal 1, PHP), with `npm run dev` only running in the background so its
hot-reloaded output keeps landing in `backend/public/assets/`.

Use `npm run dev` while actively editing frontend TS/CSS (auto-rebuilds on
save, faster than re-running `npm run build` each time); use `npm run build`
once when you just want it working. Either way, **always load the site from
the PHP server's port (8080)**, never from Vite's port (5173).

### Recap: the two things that must both be running

| Terminal | Command | Purpose | URL you actually visit |
|---|---|---|---|
| 1 | `cd backend && php -S localhost:8080 -t public router.php` | Serves the real portal | **http://localhost:8080/** ✅ |
| 2 | `cd frontend && npm run dev` (or one-off `npm run build`) | Rebuilds `admin.js`/`admin.css` | not this one — http://localhost:5173 is just a dev stub |

### Production / Apache

No dev server in production — `npm run build` populates
`backend/public/assets/`, and a single PHP-capable webserver serves
everything from `backend/public/`. Point `DocumentRoot` at `backend/public`
with `AllowOverride All`; `.htaccess` + Apache's own `DirectoryIndex` handle
routing, so `router.php` (only needed for PHP's built-in dev server) isn't
used at all.

### Windows standalone PHP gotchas

If PHP is installed but AI features silently do nothing:

- `extension_dir` in `php.ini` often points at the wrong folder even after
  uncommenting `extension=...` lines — point it at the real `ext` folder
  inside your PHP install.
- Windows PHP builds don't ship a CA bundle, so outbound HTTPS to the
  Anthropic API fails with `SSL certificate ... unable to get local issuer
  certificate` — download `cacert.pem` from https://curl.se/ca/cacert.pem
  and set both `curl.cainfo` and `openssl.cafile` in `php.ini` to point at it.

## Connecting to the careers-site repo

This portal and the petrogistix.com careers site are **two separate
codebases that share one MySQL database** — there is no API call between
them, no webhook, no sync job. The careers site's `apply.php` inserts a row
into `applications`; this portal's pages just query that same table. "Adding
data" (posting a job here) and "pulling data" (seeing applications here) both
happen automatically the moment both apps point at the same DB — there's
nothing to build, only to *point correctly*. Two things have to line up:

### 1. Point both `.env` files at the same database

In **this repo**, `backend/.env`:
```
DB_HOST=127.0.0.1
DB_NAME=petrogistix
DB_USER=root
DB_PASS=
```
In the **careers-site repo**, its own `backend/.env` needs the identical
`DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS` (or, in production, the same managed
MySQL instance). That's it — once both apps' PDO connections resolve to the
same schema, a job created in `/jobs/create.php` here immediately becomes
selectable by the careers site's `JobRepository::findAllActive()` (once its
status is `open`), and an application submitted there immediately shows up
in `/applications/?job=` here. Confirm they actually match with:
```bash
/usr/local/opt/mysql/bin/mysql -u root -e "SHOW DATABASES;"   # same DB name in both .env files?
```

### 2. Reconcile the schema (one-time, whichever repo's DB you keep)

The careers-site repo's `jobs`/`applications` tables predate a few columns
this portal added (`department`, `employment_type`, `requirements` on jobs;
`ai_score`, `ai_rationale`, `scored_at`, `review_status` on applications).
Decide which repo "owns" the live database:

- **If the careers-site DB is the one going into production**, run this
  portal's additive migration against it instead of `schema.sql` (which
  assumes an empty DB):
  ```bash
  mysql -u root <careers_db_name> < backend/sql/migrations/001_hr_portal.sql
  ```
- **If this portal's DB (seeded via `schema.sql`) is the one you're keeping**,
  point the careers-site repo's `.env` at it instead — its own `sql/schema.sql`
  uses `CREATE TABLE IF NOT EXISTS`, so re-running it here is a no-op and
  won't overwrite anything.

Either way, end state is one `jobs` table and one `applications` table with
every column both repos expect — check with:
```bash
mysql -u root petrogistix -e "DESCRIBE jobs; DESCRIBE applications;"
```

### 3. Point CV downloads at wherever the careers site actually saved them

The careers site's `apply.php` writes uploaded CVs to *its own*
`backend/storage/uploads/{job-id}/{random-filename}` — a path outside its
document root, on whatever server it runs on. This portal's
`/api/download-cv.php` needs to read from that same physical location, not
its own (empty, unless you're using the mock seed data) `storage/uploads/`.
Set in `backend/.env`:
```
CAREERS_UPLOADS_PATH=/absolute/path/to/careers-site-repo/backend/storage/uploads
```
If both repos run on the same host, this is just the other repo's path. If
they run on separate servers, either mount/sync that directory (e.g. an NFS
mount, or a small rsync/cron job keyed off upload timestamps) so this portal
has filesystem access to it, or swap `download-cv.php` for something that
streams from wherever CVs actually live (S3, etc.) if the careers site is
ever migrated to object storage instead of local disk.

### Local dev with both repos running

```bash
# Terminal A — careers site (its own repo, its own port)
cd /path/to/petrogistix-careers-site/backend
php -S localhost:8081 -t public router.php

# Terminal B — this HR portal
cd backend
php -S localhost:8080 -t public router.php
```
Both `.env` files point at the same `petrogistix` database. Submit a test
application at `http://localhost:8081/careers/apply/{job-uuid}/`, then refresh
`http://localhost:8080/applications/?job={id}` here — it appears immediately,
no restart needed, because it was never anything but a normal SQL row.

## How it works

- **Jobs** (`/jobs/`): HR creates/edits roles (title, location, department,
  description, requirements, status draft/open/closed). Status `open` is what
  makes a job visible on the public careers site and reachable at
  `/careers/apply/{job_uuid}/` there — this portal is the only place that flips it.
- **Applications** (`/applications/?job={id}`): every submission from the
  careers site's apply form for that job, sortable/filterable, each linking to
  a full detail view (`/applications/view.php?id=`) with contact info,
  education, experience, CV download, and a review-status dropdown
  (new/shortlisted/rejected/hired).
- **AI scoring**: clicking "Run AI scoring" on an applicants list calls
  `POST /api/score.php`, which runs `CandidateScorer` (Anthropic Messages API)
  against every *unscored* application for that job and stores a 0–100 score
  + short rationale. It's manual/on-demand here (not triggered by applicants
  submitting), so it never blocks or slows down the public apply flow.
- **Top 10 (AI calibrated)** tab on the applicants page shows the highest-scored
  candidates for that job — a triage aid for recruiters, not an auto-reject:
  always review rationale and profile before deciding.

## Notes

- `ANTHROPIC_API_KEY` blank disables the "Run AI scoring" button (it returns a
  clear error rather than failing silently, since this is a manual action HR
  explicitly triggers — unlike the careers site's best-effort CV autofill).
- CVs are read from `CAREERS_UPLOADS_PATH` (see "Connecting to the
  careers-site repo" above), or this repo's own `backend/storage/uploads/` if
  that's unset; downloads go through `/api/download-cv.php`, which requires
  login and validates the path stays inside the uploads directory.
- No automated tests yet. `frontend`: `npm run typecheck` (strict TS,
  `noUncheckedIndexedAccess`). Consider PHPUnit for `CandidateScorer`/`Auth`
  and a Playwright smoke test for login → score → review.
