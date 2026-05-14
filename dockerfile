# ============================================================
#  Dockerfile — PHP 8.2 + Apache for Render deployment
# ============================================================
FROM php:8.2-apache

# Enable mod_rewrite for clean URLs
RUN a2enmod rewrite

# Install curl extension (needed for API calls)
RUN docker-php-ext-install curl 2>/dev/null || true \
 && apt-get update -qq \
 && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
 && docker-php-ext-install curl \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy project files
COPY . /var/www/html/

# Create writable data and logs directories
RUN mkdir -p /var/www/html/data /var/www/html/logs \
 && chown -R www-data:www-data /var/www/html \
 && chmod 750 /var/www/html/data /var/www/html/logs

# Block direct web access to sensitive dirs
# ✅ Apache 2.4 syntax
RUN echo "Require all denied" > /var/www/html/data/.htaccess \
 && echo "Require all denied" > /var/www/html/logs/.htaccess

# Render sets $PORT — Apache must listen on that port
CMD bash -c "\
  PORT=\${PORT:-80}; \
  sed -i \"s/Listen 80/Listen \$PORT/\" /etc/apache2/ports.conf; \
  sed -i \"s/:80>/:'\$PORT'>/g\" /etc/apache2/sites-enabled/000-default.conf; \
  apache2-foreground"

EXPOSE 80
