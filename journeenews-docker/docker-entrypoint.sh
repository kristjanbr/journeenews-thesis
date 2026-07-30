#!/bin/bash
set -e

echo "Fixing Apache MPM modules..."

a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2dismod mpm_prefork 2>/dev/null || true

a2enmod mpm_prefork

echo "Enabled MPM modules:"
ls -la /etc/apache2/mods-enabled/mpm* || true

apache2ctl configtest

exec apache2-foreground