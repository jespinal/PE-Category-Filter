#!/bin/bash

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress/}

# Cleanup function for temp files
cleanup() {
    echo "Cleaning up temporary files..."
    rm -f /tmp/wordpress.zip /tmp/wordpress-nightly.zip /tmp/wp-latest.json
    rm -rf /tmp/wordpress /tmp/wordpress-extract
}

# Set up cleanup trap
trap cleanup EXIT

download() {
    if command -v curl >/dev/null 2>&1; then
        curl -s "$1" > "$2";
    elif command -v wget >/dev/null 2>&1; then
        wget -nv -O "$2" "$1"
    else
        echo "Error: Neither curl nor wget is available"
        exit 1
    fi
    
    # Check if download was successful
    if [[ ! -f "$2" ]] || [[ ! -s "$2" ]]; then
        echo "Error: Failed to download $1"
        exit 1
    fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
	WP_TESTS_TAG="tags/$WP_VERSION"
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
	WP_TESTS_TAG="trunk"
else
	# Get latest version from WordPress API
	download https://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
	LATEST_VERSION=$(jq -r '.offers[0].version' /tmp/wp-latest.json)
	if [[ -z "$LATEST_VERSION" || "$LATEST_VERSION" == "null" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

install_wp() {
	if [ -d "$WP_CORE_DIR" ]; then
		echo "WordPress core already installed at $WP_CORE_DIR"
		return;
	fi

	echo "Installing WordPress core to $WP_CORE_DIR..."
	mkdir -p "$WP_CORE_DIR"

	if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
		download https://wordpress.org/nightly-builds/wordpress-latest.zip  /tmp/wordpress-nightly.zip
		unzip -q /tmp/wordpress-nightly.zip -d /tmp/wordpress-extract/
		mv /tmp/wordpress-extract/wordpress/* "$WP_CORE_DIR"
	else
		if [ $WP_VERSION == 'latest' ]; then
			# Get the actual latest version from API
			echo "Fetching latest WordPress version..."
			download https://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
			LATEST_VERSION=$(jq -r '.offers[0].version' /tmp/wp-latest.json)
			if [[ -z "$LATEST_VERSION" || "$LATEST_VERSION" == "null" ]]; then
				echo "Latest WordPress version could not be found"
				exit 1
			fi
			echo "Latest WordPress version: $LATEST_VERSION"
			ARCHIVE_NAME="wordpress-$LATEST_VERSION"
		elif [[ $WP_VERSION == "6" ]]; then
			# Handle specific major versions
			LATEST_VERSION="6.0.11"
			ARCHIVE_NAME="wordpress-$LATEST_VERSION"
		elif [[ $WP_VERSION == "6.4" ]]; then
			LATEST_VERSION="6.4.7"
			ARCHIVE_NAME="wordpress-$LATEST_VERSION"
		elif [[ $WP_VERSION == "5.9" ]]; then
			LATEST_VERSION="5.9.21"
			ARCHIVE_NAME="wordpress-$LATEST_VERSION"
		elif [[ $WP_VERSION == "5.8" ]]; then
			LATEST_VERSION="5.8.8"
			ARCHIVE_NAME="wordpress-$LATEST_VERSION"
		elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
			# Full version already provided (x.x.x)
			ARCHIVE_NAME="wordpress-$WP_VERSION"
		elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+ ]]; then
			# For other x.x versions, try to find the latest patch
			download https://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
			LATEST_VERSION=$(jq -r ".offers[] | select(.version | startswith(\"$WP_VERSION.\")) | .version" /tmp/wp-latest.json | head -1)
			if [[ -z "$LATEST_VERSION" || "$LATEST_VERSION" == "null" ]]; then
				echo "No patch version found for WordPress $WP_VERSION"
				exit 1
			fi
			ARCHIVE_NAME="wordpress-$LATEST_VERSION"
		else
			ARCHIVE_NAME="wordpress-$WP_VERSION"
		fi

		echo "Downloading WordPress ${ARCHIVE_NAME}..."
		download https://downloads.wordpress.org/release/${ARCHIVE_NAME}.zip  /tmp/wordpress.zip
		if [[ ! -f /tmp/wordpress.zip ]]; then
			echo "Error: Failed to download WordPress archive"
			exit 1
		fi
		echo "Extracting WordPress archive..."
		unzip -q /tmp/wordpress.zip -d /tmp/wordpress-extract/
		if [[ $? -ne 0 ]]; then
			echo "Error: Failed to extract WordPress archive"
			exit 1
		fi
		# Move contents from extracted directory to target directory
		mv /tmp/wordpress-extract/wordpress/* "$WP_CORE_DIR"
		echo "WordPress installation completed successfully"
	fi

	# Remove trailing slash from WP_CORE_DIR to avoid double slashes
	WP_CORE_DIR_CLEAN=$(echo "$WP_CORE_DIR" | sed 's:/*$::')
	download https://raw.githubusercontent.com/markoheijnen/wp-mysqli/master/db.php "$WP_CORE_DIR_CLEAN/wp-content/db.php"
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d $WP_TESTS_DIR ]; then
		# set up testing suite
		mkdir -p $WP_TESTS_DIR
		svn co --quiet https://develop.svn.wordpress.org/$WP_TESTS_TAG/tests/phpunit/ $WP_TESTS_DIR
	fi

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/$WP_TESTS_TAG/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php
		# remove all forward slashes in the end
		WP_CORE_DIR=$(echo $WP_CORE_DIR | sed "s:/\+$::")
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi
}

install_db() {
	if [ ${SKIP_DB_CREATE} = "true" ]; then
		return 0
	fi

	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp";
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT";
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp";
		fi
	fi

	# create database
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

install_wp
install_test_suite
install_db
