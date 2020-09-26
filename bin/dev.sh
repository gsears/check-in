#!/bin/bash

# dev.sh
# Gareth Sears - 2493194S

# A script for helping a user set up a development environment

set -eu

wait() {
    echo
    echo $1
    read -p "Press enter when ready. "
}

wait "Make sure dependencies are up to date with 'make deps' in a new terminal"
wait "Create the database container by running 'make docker/start' in a new terminal."
make db/reset backend/start

wait "Run 'make frontend/start' in a new terminal."

echo
echo "🎉 Environment ready 🎉"
echo "Run 'make backend/cron_setup' to set up the cron functionality."
echo
