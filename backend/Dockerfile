# =============================================================================
# PDM Backend - Laravel 13 on PHP-FPM with Oracle Database support
# =============================================================================
# Base image can be overridden via build arg (e.g. php:8.4-fpm-bookworm).
# Default is pinned to a stable Debian release (bookworm) so that the
# `libaio1` package (renamed to libaio1t64 on trixie) is still available.
ARG PHP_BASE_IMAGE=php:8.4-fpm-bookworm
FROM ${PHP_BASE_IMAGE} AS base

# NOTE: Oracle publishes Instant Client RPMs for BOTH x86_64 and aarch64 under
# the same build folder. Version 23.26.3.0.0 (folder 2326300) is the latest
# that ships aarch64 RPMs; the 23.5 series (2350000) only has x86_64 RPMs.
# Keep ORACLE_INSTANTCLIENT_VERSION and ORACLE_INSTANTCLIENT_ROOT in sync.
ARG ORACLE_INSTANTCLIENT_VERSION=23.26.3.0.0
ARG ORACLE_INSTANTCLIENT_ROOT=2326300
# Auto-detected by BuildKit (amd64/arm64). Override via .env if needed.
ARG TARGETARCH
ARG ORACLE_ARCH=""

# Map Docker/Go architecture to Oracle RPM architecture suffix.
RUN case "${ORACLE_ARCH:-${TARGETARCH}}" in \
        amd64|x86_64) echo "x86_64"  > /tmp/oracle_arch ;; \
        arm64|aarch64) echo "aarch64" > /tmp/oracle_arch ;; \
        *) echo "Unsupported architecture: ${ORACLE_ARCH:-${TARGETARCH}}" >&2; exit 1 ;; \
    esac

RUN apt-get update && apt-get install -y \
        git curl unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev \
        libaio1 alien build-essential libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# --- Oracle Instant Client (needed for pdo_oci / oci8) ----------------------
# Downloads the Basic Light + SDK packages from Oracle's public CDN.
# The RPM architecture is selected automatically based on the build platform
# (x86_64 for Intel/AMD, aarch64 for Apple Silicon / ARM64).
# If your network blocks this, download the two RPMs manually and copy them
# into backend/oracle-client/ before building, then adjust the COPY lines
# below to `COPY oracle-client/*.rpm /tmp/`.
RUN mkdir -p /opt/oracle && cd /opt/oracle \
    && ORACLE_ARCH=$(cat /tmp/oracle_arch) \
    && curl -fSL -o instantclient-basiclite.rpm \
        "https://download.oracle.com/otn_software/linux/instantclient/${ORACLE_INSTANTCLIENT_ROOT}/oracle-instantclient-basiclite-${ORACLE_INSTANTCLIENT_VERSION}-1.el9.${ORACLE_ARCH}.rpm" \
    && curl -fSL -o instantclient-sdk.rpm \
        "https://download.oracle.com/otn_software/linux/instantclient/${ORACLE_INSTANTCLIENT_ROOT}/oracle-instantclient-devel-${ORACLE_INSTANTCLIENT_VERSION}-1.el9.${ORACLE_ARCH}.rpm" \
    && alien -i instantclient-basiclite.rpm \
    && alien -i instantclient-sdk.rpm \
    && rm -f instantclient-basiclite.rpm instantclient-sdk.rpm \
    && echo "/usr/lib/oracle/23/client64/lib" > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

ENV ORACLE_HOME=/usr/lib/oracle/23/client64
ENV ORACLE_LIB_DIR=$ORACLE_HOME/lib
ENV LD_LIBRARY_PATH=$ORACLE_LIB_DIR

# --- PHP extensions ----------------------------------------------------------
# Point pecl at the Oracle Instant Client *lib* directory. The oci8 configure
# script (config.m4) checks for libclntsh.so / libnnz.so there, and for RPM
# installs it maps `.../client64/lib` back to the SDK header dir
# `/usr/include/oracle/23/client64` automatically.
RUN apt-get update && apt-get install -y libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd intl \
    && printf "instantclient,${ORACLE_LIB_DIR}\n" | pecl install oci8-3.2.1 \
    && docker-php-ext-enable oci8 intl \
    && rm -rf /var/lib/apt/lists/*

# --- Composer (for runtime use, e.g. artisan commands) -----------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# --- Copy the full Laravel application (including vendor/) -------------------
# Dependencies are installed on the host via `composer install` and committed
# to the repo (or mounted as a volume), so no composer create-project/install
# is needed at build time. This makes builds fast and reproducible.
COPY . /var/www/

RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]