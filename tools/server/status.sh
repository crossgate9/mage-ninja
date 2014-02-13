#!/bin/bash

#USAGE: ./status.sh www.example.com status@example.com

url=$1
receipt=$2
echo $url
status=`curl -sL -w "%{http_code} %{url_effective}\\n" $url -o /dev/null | awk '{print $1}'`
echo $status
if [ "$status" = "200" ]; then
  echo 'OK'
else
  echo `date`": Server Down, Status Code: $status" | mail -s "$url-Status" $receipt
fi
