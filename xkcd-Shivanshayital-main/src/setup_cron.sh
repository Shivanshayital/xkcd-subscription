#!/bin/bash

CRON_JOB="0 9 * * * php $(pwd)/cron.php"

# Add only if not already present
(crontab -l 2>/dev/null | grep -v -F "$CRON_JOB"; echo "$CRON_JOB") | crontab -

echo "✅ CRON job has been set to run every 24 hours at 9:00 AM."
