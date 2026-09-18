#!/bin/sh
set -eu

TEST_DB="${MARIADB_DATABASE}_test"

mariadb -u root -p"${MARIADB_ROOT_PASSWORD}" <<-SQL
    CREATE DATABASE IF NOT EXISTS \`${TEST_DB}\`;
    GRANT ALL ON \`${TEST_DB}\`.* TO '${MARIADB_USER}'@'%';
    FLUSH PRIVILEGES;
SQL
