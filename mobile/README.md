# Optizee Staff (Flutter)

Native mobile app for **cashiers** and **kitchen staff**.

## Features

- Quick 4-digit code login or email/password
- Cashier POS: table + guests, per-guest carts, categories, search, notes, checkout
- Add guests to a table and switch between them to take separate orders
- Post-sale receipt summary
- Ready kitchen orders banner (auto-poll) + mark served
- Kitchen live board: pending → preparing → ready → served
- Auto-refresh every 8s + sound on new orders
- Configurable API server URL (for phones on Wi‑Fi)

## Run

```bash
cd mobile
flutter pub get
flutter run
```

### Server URL on a real phone

On login, open **Server settings** and set your LAN API URL, e.g.:

`http://192.168.1.20/api`

Default is `http://optizee.test/api` (local Valet/Herd).

## Laravel API

Staff endpoints (Sanctum token auth):

- `POST /api/staff/login`
- `POST /api/staff/login/code`
- `GET /api/staff/me`
- `POST /api/staff/logout`
- `GET /api/staff/pos/catalog`
- `GET /api/staff/pos/tables`
- `GET /api/staff/pos/tables/{table}/guests`
- `POST /api/staff/pos/tables/{table}/guests`
- `GET /api/staff/pos/customers`
- `GET /api/staff/pos/ready-orders`
- `POST /api/staff/pos/checkout`
- `GET /api/staff/kitchen/live`
- `POST /api/staff/kitchen/{sale}/preparing|ready|served`

Cashiers land on POS. Kitchen staff land on Kitchen.
