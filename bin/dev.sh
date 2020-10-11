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

echo "Which fixtures would you like to install? Note: Evaluation fixtures may take a while..."
select fix in "Test" "Evaluation"; do
    case $fix in
        Test ) make fixtures/test; break;;
        Evaluation ) make fixtures/evaluation break;;
    esac
done

echo "Would you like to set up the cron functionality? Requires cron / crontable."
select yn in "Yes" "No"; do
    case $yn in
        Yes ) make backend/cron_setup; break;;
        No ) break;;
    esac
done

wait "Run 'make frontend/start' in a new terminal."

echo
echo "🎉 Environment ready 🎉"
echo "Type make backend/status to view the port the server is running from."
echo
