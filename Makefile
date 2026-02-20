up:
	podman-compose up -d

down:
	podman-compose down

logs:
	podman-compose logs -f

migrate:
	php artisan migrate

serve:
	php artisan serve --host=0.0.0.0 --port=8000

dev:
	npm run dev -- --host 127.0.0.1 --port 5173
