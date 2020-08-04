#!/bin/bash
php=$(which php)
line="* * * * * $php $PWD/bin/console ts:run >> /dev/null 2>&1"
(crontab -l; echo "$line" ) | crontab -
echo "$php"
