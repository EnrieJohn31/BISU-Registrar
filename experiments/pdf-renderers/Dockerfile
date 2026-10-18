FROM python:3.12-slim-bookworm AS qa
RUN apt-get update && apt-get install -y --no-install-recommends poppler-utils fonts-dejavu-core=2.37-6 \
    && mkdir -p /opt/pdf-fonts \
    && cp /usr/share/fonts/truetype/dejavu/DejaVuSans.ttf /opt/pdf-fonts/ \
    && cp /usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf /opt/pdf-fonts/ \
    && cp /usr/share/fonts/truetype/dejavu/DejaVuSerif-Bold.ttf /opt/pdf-fonts/ \
    && cp /usr/share/doc/fonts-dejavu-core/copyright /opt/pdf-fonts/LICENSE \
    && rm -rf /var/lib/apt/lists/*
COPY requirements.txt /opt/requirements.txt
RUN pip install --no-cache-dir --no-deps -r /opt/requirements.txt
WORKDIR /spike

FROM php:8.4-cli-bookworm AS dompdf
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libpng-dev \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY --from=qa /opt/pdf-fonts /opt/pdf-fonts
WORKDIR /opt/renderer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts \
    && composer validate --strict --no-check-all
WORKDIR /spike

FROM mcr.microsoft.com/playwright:v1.64.1-noble AS chromium
COPY --from=qa /opt/pdf-fonts /opt/pdf-fonts
COPY --from=qa /opt/pdf-fonts /usr/local/share/fonts/pdf-lab
RUN fc-cache -f
WORKDIR /opt/renderer
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts --no-audit --no-fund
WORKDIR /spike
