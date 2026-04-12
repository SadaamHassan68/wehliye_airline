# Wehliye Airline — Online Flight Booking Management System

PHP + MySQL project with a clear folder layout, PDO, and simple OOP classes.

## Project layout

```
booking_flight-main/
├── assets/                 # Static files
│   ├── css/app.css         # Styles (Bootstrap overrides + theme)
│   ├── js/app.js           # Client helpers (confirm dialogs, etc.)
│   └── img/                # Logos, icons (add files here)
├── config/
│   └── db.php              # Database (PDO) + session bootstrap
├── includes/
│   ├── functions.php       # Autoload, base_url(), flash, auth guards
│   ├── header.php
│   ├── footer.php
│   └── partials/           # Reusable fragments (home, admin sidebar, dashboard)
├── classes/
│   ├── User.php            # Login, registration, session user
│   ├── Flight.php          # Search, CRUD, status, seat availability
│   └── Booking.php         # PNR, bookings, reports, manifest query
├── admin/
│   ├── dashboard.php       # Admin analytics (sidebar layout)
│   └── manage_flights.php  # List / search / add flights
├── staff/
│   └── manifest.php        # Agent: flights, status updates, passenger manifest
├── index.php               # Homepage + flight search + marketing sections
├── login.php
├── signup.php              # Passenger registration
├── logout.php
├── dashboard.php           # Passenger / agent stats (admins redirect to /admin/)
├── bookings.php          # Booking history & cancellations
└── booking_process.php     # POST handler for ticket purchase (passengers)
```

## Setup (XAMPP)

1. Create database `ofbms` and import `database/schema.sql` (and `fix_demo_passwords.sql` if needed).
2. Edit `config/db.php` if your MySQL user/password differs.
3. Open `http://localhost/booking_flight-main/`

## Demo accounts

- Admin: `admin@wehliye.local` / `admin123`
- Agent: `agent@wehliye.local` / `agent123`
- Passenger: `passenger@wehliye.local` / `pass123`

If login fails, run `database/fix_demo_passwords.sql` in phpMyAdmin.

## Notes

- **Admins** use `/admin/dashboard.php` and `/admin/manage_flights.php` (sidebar UI).
- **Agents** use `/staff/manifest.php` for manifests and status updates.
- **Passengers** search and book from `index.php`; purchases POST to `booking_process.php`.
