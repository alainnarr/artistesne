#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Octopus Deploy — "Run a Script" step
#
# This script is executed BY CALAMARI directly on the SSH deployment target
# (vdn-typos1 for QA, vdn-typop1 for Production), exactly like the other
# SCNE projects (e.g. sien-neuchatelville-cms on vdn-typot1).
#
# The Octopus server (Windows) SSHes to the target, uploads Calamari
# linux-x64, transfers the package via SFTP, then runs this script.
#
# Octopus injects:
#   $OctopusEnvironmentName               — "QA" | "Production"
#   $OctopusOriginalPackageDirectoryPath  — extracted package on the target
#   $OctopusReleaseNumber                 — e.g. "1.0.178828"
#
# Prerequisites on each target server (vdn-typos1 / vdn-typop1):
#   • An SSH account configured in Octopus (e.g. user "octopus" or "web")
#     with the SSH key from the Octopus server in authorized_keys.
#   • That user must have write access to /data/artistes/.
#   • sudo rule for PHP-FPM reload (optional — see below).
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

# Octopus runs this script from deploy/octopus/ inside the extracted package.
# Navigate two levels up to reach the package root.
PACKAGE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

# Octopus substitutes #{...} variables into the script text before execution.
# Do NOT use $OctopusXxx env vars — they are not exported in this mode.
RELEASE_NUMBER="#{Octopus.Release.Number}"
ENVIRONMENT_NAME="#{Octopus.Environment.Name}"

DEPLOY_PATH="/data/artistes"
RELEASES_DIR="${DEPLOY_PATH}/releases"
SHARED_DIR="${DEPLOY_PATH}/shared"
RELEASE_DIR="${RELEASES_DIR}/${RELEASE_NUMBER}"
CURRENT_LINK="${DEPLOY_PATH}/current"

PHP_BIN=$(command -v php8.4 2>/dev/null || command -v php 2>/dev/null || echo "")

echo "▶ Deploying release ${RELEASE_NUMBER}"
echo "  env     : ${ENVIRONMENT_NAME}"
echo "  package : ${PACKAGE_DIR}"
echo "  target  : ${RELEASE_DIR}"
echo "  user    : $(id)"

# ── 1. Create release directory and sync package contents ─────────────────
mkdir -p "${RELEASE_DIR}"
rsync -a --delete "${PACKAGE_DIR}/" "${RELEASE_DIR}/"
echo "✔ Package synced to ${RELEASE_DIR}"

# ── 2. Link shared files (.env and storage/ from shared/) ─────────────────
mkdir -p "${SHARED_DIR}/storage/framework/"{cache,sessions,views} \
         "${SHARED_DIR}/storage/logs"

# User-uploaded files (artist portraits, attachments) must live on the
# mounted mass-storage volume (/mnt/typo3_data, same DFS share used by the
# TYPO3 sites' fileadmin/uploads folders) instead of the local /data disk,
# so they follow the same backup/retention policy as every other app.
# storage/app/public becomes a symlink to the shared mount; storage/app/private
# (registration CV/portfolio documents — never publicly reachable) stays on
# local disk under shared/storage since it doesn't need to be on the network share.
#
# NOTE: on vdn-typos1, "shared/storage/app" ended up owned solely by
# www-data (mode 0700), created by a PHP-FPM web request rather than this
# script, so this deploy account currently CANNOT write into it. These
# commands are wrapped in `|| true` (with a warning) so a pre-existing
# permissions problem here does NOT abort the whole deploy — see
# app/Http/Controllers/Admin/StoragePermissionsMaintenanceController.php
# for the temporary self-service fix, or ask ops to `chown` it to match
# storage/framework and storage/logs above.
case "${ENVIRONMENT_NAME}" in
    Production) UPLOADS_PATH="/mnt/typo3_data/artistes_uploads" ;;
    *)          UPLOADS_PATH="/mnt/typo3_data/artistes-staging_uploads" ;;
esac
mkdir -p "${UPLOADS_PATH}" 2>/dev/null || echo "⚠ Could not create/access ${UPLOADS_PATH} (mount missing or permission denied?)"
if mkdir -p "${SHARED_DIR}/storage/app/private" 2>/dev/null; then
    if rm -rf "${SHARED_DIR}/storage/app/public" 2>/dev/null; then
        if ln -sfn "${UPLOADS_PATH}" "${SHARED_DIR}/storage/app/public"; then
            echo "✔ storage/app/public → ${UPLOADS_PATH}"
        else
            echo "⚠ Could not symlink storage/app/public — check ownership of ${SHARED_DIR}/storage/app"
        fi
    else
        echo "⚠ Could not remove existing storage/app/public — check ownership of ${SHARED_DIR}/storage/app"
    fi
else
    echo "⚠ Could not create ${SHARED_DIR}/storage/app/private (permission denied?) — registration document uploads/exports will fail until ownership is fixed. See StoragePermissionsMaintenanceController."
fi

# Remove any packaged copies so symlinks can be placed
rm -rf "${RELEASE_DIR}/storage"
rm -f  "${RELEASE_DIR}/.env"

ln -sfn "${SHARED_DIR}/storage" "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/.env"    "${RELEASE_DIR}/.env"
echo "✔ Shared symlinks created"

# ── 2b. Fix shared/storage permissions so PHP-FPM (www-data) can write ───
# The deploy user (UID 1001) is in www-data (GID 33).
# Use || true — chmod fails silently on files owned by www-data (e.g. view
# cache written by PHP-FPM); those files are already writable by their owner.
chmod -R u+rwx,g+rwx "${SHARED_DIR}/storage" 2>/dev/null || true
chgrp -R www-data "${SHARED_DIR}/storage" 2>/dev/null || true
echo "✔ Storage permissions fixed (errors on www-data-owned files are normal)"

# ── 3. Laravel bootstrap cache ────────────────────────────────────────────
mkdir -p "${RELEASE_DIR}/bootstrap/cache"
chmod ug+rwx "${RELEASE_DIR}/bootstrap/cache"

if [ -n "${PHP_BIN}" ]; then
    cd "${RELEASE_DIR}"

    # Safety backup before any migration — prevents data loss on all environments
    BACKUP_DIR="/data/artistes/backups"
    mkdir -p "${BACKUP_DIR}"
    DB_NAME=$(grep "^DB_DATABASE=" "${SHARED_DIR}/.env" | cut -d= -f2 | tr -d '"')
    DB_USER=$(grep "^DB_USERNAME=" "${SHARED_DIR}/.env" | cut -d= -f2 | tr -d '"')
    DB_PASS=$(grep "^DB_PASSWORD=" "${SHARED_DIR}/.env" | cut -d= -f2 | tr -d '"')
    DB_HOST=$(grep "^DB_HOST=" "${SHARED_DIR}/.env" | cut -d= -f2 | tr -d '"')
    DB_PORT=$(grep "^DB_PORT=" "${SHARED_DIR}/.env" | cut -d= -f2 | tr -d '"')
    BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_pre-deploy_${RELEASE_NUMBER}.sql.gz"
    mysqldump \
        --host="${DB_HOST}" --port="${DB_PORT}" \
        --user="${DB_USER}" --password="${DB_PASS}" \
        --single-transaction --routines --triggers \
        "${DB_NAME}" | gzip -9 > "${BACKUP_FILE}" \
        && echo "✔ Pre-deploy backup: ${BACKUP_FILE}" \
        || echo "⚠ Pre-deploy backup failed — continuing anyway"

    "${PHP_BIN}" artisan migrate --force && echo "✔ migrate"

    "${PHP_BIN}" artisan optimize        && echo "✔ optimize"
else
    echo "⚠ php binary not found — skipping artisan commands"
fi

# ── 4. Atomic symlink swap ────────────────────────────────────────────────
ln -sfn "${RELEASE_DIR}" "${CURRENT_LINK}.new"
mv -Tf "${CURRENT_LINK}.new" "${CURRENT_LINK}"
echo "✔ Symlink: ${CURRENT_LINK} → ${RELEASE_DIR}"

# ── 5. Reload PHP-FPM (requires sudo rule on the target) ─────────────────
if sudo -n /bin/systemctl reload php8.4-fpm 2>/dev/null; then
    echo "✔ php8.4-fpm reloaded"
else
    echo "⚠ Could not reload php8.4-fpm (no sudo rule?) — reload manually if needed"
fi

# ── 5b. Restart the queue worker (systemd --user unit, no sudo needed) ───
# QUEUE_CONNECTION=database: exports, magic-link emails and approval
# notifications are only ever processed by a persistent `queue:work` worker.
# Restarting (not reloading) picks up the new code for already-running jobs.
# This runs as this script's own user (the `octopus` SSH/deploy account) via
# its systemd --user instance — see deploy/octopus/artistes-queue-worker.service
# for the unit to install (requires `loginctl enable-linger` once so it
# survives past this deploy's SSH session).
if systemctl --user restart artistes-queue-worker 2>/dev/null; then
    echo "✔ artistes-queue-worker restarted"
else
    echo "⚠ Could not restart artistes-queue-worker (user unit not installed/enabled?) — queued jobs (exports, emails) will NOT be processed until a worker runs. See deploy/octopus/artistes-queue-worker.service."
fi

# ── 6. Prune old releases (keep last 5) ──────────────────────────────────
ls -1dt "${RELEASES_DIR}/"* 2>/dev/null | tail -n +6 | xargs -r rm -rf
echo "✔ Old releases pruned"

echo "✔ Deployment of ${RELEASE_NUMBER} complete"
