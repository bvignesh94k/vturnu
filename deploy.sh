#!/usr/bin/env bash
#
# Push this folder to the VTurnU VPS.
#
# Run it from the project root:   bash deploy.sh
# One password prompt, then the live site matches this folder exactly.
#
# storage/ is NEVER pushed. It holds the live leads, the CRM metadata and the
# admin password hash on the server; the local copy is a stale, near-empty
# stand-in. Overwriting it would destroy real enquiries and reset the login.

set -euo pipefail

HOST="${VTURNU_HOST:-root@66.29.131.95}"
DEST="${VTURNU_DEST:-/var/www/html/vturnu.com}"

cd "$(dirname "$0")"

# new/ is an older duplicate of the whole site kept for reference; it must not
# reach the server, where it would be publicly reachable at /new/.
EXCLUDES=(
  --exclude=storage
  --exclude=new
  --exclude=.git
  --exclude=deploy.sh
  --exclude='*.zip'
  --exclude='.DS_Store'
  --exclude='Thumbs.db'
)

echo "Deploying $(pwd)"
echo "        → ${HOST}:${DEST}"
echo

# Everything in one tar stream so there is a single password prompt, and the
# remote side only ever sees a complete set of files.
tar -czf - "${EXCLUDES[@]}" . \
  | ssh "$HOST" "
      set -e
      mkdir -p '${DEST}'
      tar -xzf - -C '${DEST}'

      # Files arrive owned by root, which leaves Apache unable to write
      # storage/ — that is what breaks the admin panel's saves and sessions.
      chown -R apache:apache '${DEST}'
      find '${DEST}' -type d -exec chmod 755 {} +
      find '${DEST}' -type f -exec chmod 644 {} +

      # SELinux: writable dirs need the rw context or httpd is denied silently.
      if command -v restorecon >/dev/null 2>&1; then
        restorecon -R '${DEST}' >/dev/null 2>&1 || true
      fi
      if command -v semanage >/dev/null 2>&1; then
        semanage fcontext -a -t httpd_sys_rw_content_t '${DEST}/storage(/.*)?' >/dev/null 2>&1 || true
        restorecon -R '${DEST}/storage' >/dev/null 2>&1 || true
      fi

      systemctl reload httpd || systemctl restart httpd
      echo 'Remote: files updated, permissions set, Apache reloaded.'
    "

echo
echo "Done. Check https://vturnu.com/"
