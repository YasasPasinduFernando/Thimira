# Village Traveler (PHP + MySQL + Tailwind + OpenStreetMap)

XAMPP runnable simulation based on your BIT SRS and system proposal.

## Features
- Location-based tourist attraction discovery (within 25km radius)
- Distance sorting from user location (live geolocation or provided fallback coordinates)
- Bilingual content support (English / Sinhala)
- Attraction detail page with travel time estimate and Google Maps navigation button
- One-day trip auto planner
- Admin login + attraction CRUD management

## Default Coordinates
- Latitude: `6.2291414`
- Longitude: `80.0573511`
- Shared map link: `https://maps.app.goo.gl/XpPW8HxJxcbdj1Zj6`

## Tech Stack
- PHP 8+
- MySQL (XAMPP)
- Tailwind CSS (CDN)
- Leaflet + OpenStreetMap tiles
- Google Maps for turn-by-turn navigation links

## Run on XAMPP
1. Copy project folder into `xampp/htdocs/` as `village-traveler`.
2. Start `Apache` and `MySQL` in XAMPP.
3. Open `http://localhost/phpmyadmin`.
4. Import `database.sql`.
5. Open `http://localhost/village-traveler/index.php`.

## Admin Access
- URL: `http://localhost/village-traveler/admin/login.php`
- Username: `admin`
- Password: `admin123`

## Notes
- DB config is in `config/config.php`.
- If your MySQL root user has a password, update `DB_PASS` in `config/config.php`.
