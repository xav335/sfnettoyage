#!/bin/bash

SERVERNAME=/var/www/sfnettoyage.com

chown -R www-data.www-data $SERVERNAME
chmod -R 755 $SERVERNAME
chmod -R 755 $SERVERNAME/.htaccess
