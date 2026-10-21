# Cafe Hopper 

Aplikasi catatan *cafe hopping*: simpan kafe yang sudah atau mau didatangi, lengkap dengan rating, harga, fasilitas, dan cocok tidaknya untuk nugas.

UTS Konstruksi dan Evolusi Perangkat Lunak — (24/544411/SV/25413, B2)

## Teknologi
| Bagian | Teknologi |
|---|---|
| Backend | Laravel + Sanctum (REST API, token), FrankenPHP |
| Frontend | Vue 3 + Vue Router + Axios (Vite) |
| Database | MySQL 8.4 |
| Container | Docker, Docker Compose |
| CI/CD | GitHub Actions → GitHub Container Registry (ghcr.io) |

## Struktur
```
backend/    Laravel API
frontend/   Vue
docker-compose.yml
.github/workflows/ci.yml
```

## Menjalankan dengan Docker
```powershell
cp .env.example .env        # isi APP_KEY, DB_PASSWORD, DB_ROOT_PASSWORD
docker compose up -d --build
```
- Aplikasi: http://localhost:8080
- API langsung: http://localhost:8000/api
- Akun demo: `hailo@cafehopper.test` / `password123`

## Endpoint API
| Method | URL | Keterangan |
|---|---|---|
| POST | `/api/login` | satu-satunya endpoint tanpa token |
| GET | `/api/me` | data user |
| POST | `/api/logout` | hapus token |
| GET | `/api/cafes` | tampil semua (`?status=visited\|wishlist&work_friendly=1&q=`) |
| POST | `/api/cafes` | tambah → 201 |
| GET | `/api/cafes/{id}` | tampil satu |
| PUT/PATCH | `/api/cafes/{id}` | ubah |
| DELETE | `/api/cafes/{id}` | hapus |

Postman Collection: `backend/postman/CafeHopper.postman_collection.json`
