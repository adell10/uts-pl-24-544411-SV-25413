# ---------- Tahap 1: build aplikasi Vue dengan Node.js ----------
FROM node:22.12-alpine AS build
WORKDIR /app

# Salin file dependency dulu supaya lapisan npm ci di-cache
COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Alamat API diatur lewat variabel lingkungan saat build (bukan ditulis di komponen).
# Default "/api" → diteruskan Nginx ke service "backend" di jaringan Docker Compose.
ARG VITE_API_URL=/api
ENV VITE_API_URL=${VITE_API_URL}
RUN npm run build

# ---------- Tahap 2: sajikan hasil build dengan Nginx ----------
# Image akhir hanya berisi Nginx + folder dist (TANPA node_modules)
FROM nginx:1.27-alpine
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80