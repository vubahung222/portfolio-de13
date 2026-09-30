#!/bin/bash
# ============================================================
#  Create a read-only monitoring user for mysqld-exporter.
#  Runs after init.sql on first container start.
#  Uses env vars passed to the mysql container.
# ============================================================
set -e

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" <<-EOSQL
    CREATE USER IF NOT EXISTS '${MYSQL_EXPORTER_USER}'@'%' IDENTIFIED BY '${MYSQL_EXPORTER_PASSWORD}' WITH MAX_USER_CONNECTIONS 3;
    GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO '${MYSQL_EXPORTER_USER}'@'%';
    FLUSH PRIVILEGES;
EOSQL

echo "Monitoring user '${MYSQL_EXPORTER_USER}' created."
