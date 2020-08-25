#!/bin/bash

# setup_cron.sh
# Gareth Sears - 2493194S

# A small shell script to set up crontab for use with the TaskSchedulerBundle
php=$(which php)
line="* * * * * $php $PWD/bin/console ts:run >> /dev/null 2>&1"
(crontab -l; echo "$line" ) | crontab -
echo "$php"
