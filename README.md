<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## Local development

The project ships with a SQLite-first configuration so you can get a dev environment running without external services.

1. Install dependencies and build the frontend:
   ```bash
   composer run setup
   ```
   The script also copies `.env.example` to `.env` if it does not exist and generates the app key.

2. Create the SQLite database file if it is missing and run migrations (skipped automatically when the file already exists):
   ```bash
   touch database/database.sqlite
   php artisan migrate --force
   ```

3. Start the full dev stack (Laravel server on port 8000, queue listener, log viewer, and Vite on port 5173):
   ```bash
   composer run dev
   ```
   Visit [http://localhost:8000](http://localhost:8000) while Vite serves assets from [http://localhost:5173](http://localhost:5173).

4. To stop the stack, press `Ctrl+C` in the terminal running the `composer run dev` command.

### Environment quick reference

The default `.env.example` is already configured for local SQLite development:

- `DB_CONNECTION=sqlite` and `database/database.sqlite` as the database file
- `SESSION_DRIVER=database` and `QUEUE_CONNECTION=database` so the database migrations create the required tables
- `APP_URL=http://localhost` and `VITE_APP_NAME="${APP_NAME}"` to keep Laravel and Vite URLs in sync

If you change the app URL or ports, update both `APP_URL` and the dev server commands accordingly.
