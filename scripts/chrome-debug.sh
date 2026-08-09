#!/usr/bin/env bash
# Starts the WINDOWS Chrome with a remote-debugging port so the
# chrome-devtools MCP server (running inside WSL) can drive it via
# "--browserUrl http://127.0.0.1:9222".
#
# Idempotent: does nothing if Chrome is already listening on the port.
# A dedicated user-data-dir is used so your normal Chrome profile is never
# touched (Chrome 136+ also blocks remote debugging on the default profile).
#
# Prerequisite (WSL2 -> Windows localhost bridge):
#   %USERPROFILE%\.wslconfig  (on Windows):
#     [wsl2]
#     networkingMode = mirrored
#     [experimental]
#     hostAddressLoopback = true
#   then run: wsl --shutdown   (from PowerShell/cmd, then restart WSL)
#
# Usage: ./scripts/chrome-debug.sh [port]   (default 9222)
set -euo pipefail

PORT="${1:-9222}"
CHROME_WIN='C:\Program Files\Google\Chrome\Application\chrome.exe'
USER_DATA='C:\Temp\chrome-mcp'

if curl -sf -m 2 "http://127.0.0.1:${PORT}/json/version" > /dev/null 2>&1; then
  echo "Chrome is already listening on 127.0.0.1:${PORT} - nothing to do."
  exit 0
fi

# Launch via cmd.exe /c start so the process detaches and bash returns immediately.
cmd.exe /c start "" "${CHROME_WIN}" \
  --remote-debugging-port=${PORT} \
  --user-data-dir="${USER_DATA}" \
  --no-first-run --no-default-browser-check \
  > /dev/null 2>&1 || true

echo "Launching Windows Chrome on port ${PORT} (profile: ${USER_DATA})..."

for _ in $(seq 1 20); do
  if curl -sf -m 2 "http://127.0.0.1:${PORT}/json/version" > /dev/null 2>&1; then
    echo "OK - Chrome reachable on http://127.0.0.1:${PORT}"
    exit 0
  fi
  sleep 0.5
done

echo "Chrome did not become reachable on 127.0.0.1:${PORT} within 10s."
echo "Likely cause: WSL is in NAT mode - enable mirrored networking in"
echo "%USERPROFILE%\\\\.wslconfig (see header comment) then restart WSL."
exit 1
