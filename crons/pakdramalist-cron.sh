#!/bin/bash

i=0

while [ $i -lt 12 ]; do
  mysql -u root pakdramalist_pakistanidramas_channels < /home/pakdramalist/public_html/wp-content/plugins/tmu/crons/update-rating.sql &> cron_log.txt
  if [ $? -ne 0 ]; then
    echo "Error executing MySQL query at $(date)" >> cron_log.txt
  fi

  mysql -u root pakdramalist_pakistanidramas_channels < /home/pakdramalist/public_html/wp-content/plugins/tmu/crons/person-no-items.sql &> cron_log.txt
  if [ $? -ne 0 ]; then
    echo "Error executing MySQL query at $(date)" >> cron_log.txt
  fi

  sleep 5
  i=$(( i + 1 ))
done