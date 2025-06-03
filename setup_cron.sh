#!/bin/bash

# Get the absolute path to cron.php
CRON_PHP_PATH="$(cd "$(dirname "$0")"; pwd)/cron.php"

# Add a cron job to run cron.php every 24 hours at midnight
CRON_JOB="0 0 * * * /usr/bin/php $CRON_PHP_PATH >> /tmp/xkcd_cron.log 2>&1"

# Check if the cron job already exists
(crontab -l 2>/dev/null | grep -F "$CRON_PHP_PATH") >/dev/null
if [ $? -eq 0 ]; then
    echo "Cron job already exists. No changes made."
else
    # Add the cron job
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "Cron job added to run cron.php every 24 hours."
fi
