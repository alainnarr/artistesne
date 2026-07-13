# Inventaire des artistes neuchâtelois·es

Application Laravel pour présenter les artistes du canton de Neuchâtel, permettre aux artistes de demander une inscription et offrir un espace de gestion dédié.

## Features

- Public directory of artists at `/artistes`
- Public artist profile pages at `/artistes/{artist}`
- Artist registration request flow at `/devenir-artiste`
- Email magic-link login for artists at `/artiste/connexion`
- Artist portal for dashboard and profile management
- Admin file downloads for registration requests

## Stack

- Laravel 13
- Livewire 4
- Filament 5
- Flux UI
- Tailwind CSS 4
- Fortify authentication

## Requirements

- PHP 8.4
- Docker with Laravel Sail
- Node.js dependencies are handled through the Sail environment

## Getting Started

1. Install PHP dependencies and prepare the application environment.

   ```bash
   composer install
   cp .env.example .env
   ```

2. Start the Sail environment.

   ```bash
   vendor/bin/sail up -d
   ```

3. Generate the application key and run the database migrations.

   ```bash
   vendor/bin/sail artisan key:generate
   vendor/bin/sail artisan migrate
   ```

4. Install frontend dependencies and build the assets.

   ```bash
   vendor/bin/sail pnpm install
   vendor/bin/sail pnpm run build
   ```

If you prefer to bootstrap everything in one pass, the repository also defines a Composer setup script.

```bash
vendor/bin/sail composer run setup
```

## Local Development

Start the full development stack with:

```bash
vendor/bin/sail composer run dev
```

This runs the Laravel server, queue listener, log viewer, and Vite in parallel.

## Useful Commands

- Run the test suite: `vendor/bin/sail artisan test --compact`
- Format PHP files: `vendor/bin/sail bin pint --dirty --format agent`
- Open the application in your browser: `vendor/bin/sail open`

## Branch & Release Workflow

```
feature/*  →  develop  →  main  →  v*  →  Production
```

| Branch / trigger | CI builds | Deploys to |
|---|---|---|
| `feature/*` PR | ✗ | — |
| `develop` push | ✓ | QA (auto) |
| `main` push | ✓ | nothing |
| `v1.2.3` tag on `main` | ✓ | Production (manual approval in Octopus) |

### Day-to-day development

```bash
# Start a feature
git checkout develop
git pull
git checkout -b feature/my-feature

# ... make changes, commit ...

# Merge back via PR targeting develop
```

### Deploy to staging

Push (or merge a PR) to `develop` — the pipeline builds automatically and deploys to QA.

### Release to production

```bash
# 1. Make sure develop is green on staging, then merge to main
git checkout main
git pull
git merge --no-ff develop
git push

# 2. Create and push a semver tag
git tag v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

The pipeline picks up the tag, builds the package, creates an Octopus release, and triggers the deployment to Production.
A **Manual Approval** step in Octopus blocks the rollout — confirm it in the Octopus UI to complete the deployment.

### Hotfix (urgent production fix)

```bash
# Branch off main
git checkout main && git pull
git checkout -b hotfix/my-fix

# ... fix, commit ...

# Merge to main AND back to develop
git checkout main && git merge --no-ff hotfix/my-fix
git checkout develop && git merge --no-ff hotfix/my-fix
git branch -d hotfix/my-fix

# Tag and push to ship
git checkout main
git tag v1.0.1 -m "Hotfix v1.0.1"
git push origin main develop v1.0.1
```

## Project Notes

- The landing page is served from `/`.
- Artist-facing pages are written in French.
- Email delivery must be configured for the magic-link login flow to work.
- The application uses the default Laravel settings pages under `/settings` for authenticated users.
