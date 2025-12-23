# Crypto Trading Dashboard

A real-time crypto trading dashboard built with **Laravel API** (backend) and **Vue 3 + Tailwind CSS** (frontend). Features include buy/sell order placement, balance management, and automated order matching with live updates.

---

## Table of Contents

- [Features](#features)
- [Project Setup](#project-setup)
- [Backend (Laravel)](#backend-laravel)
- [Frontend (Vue 3 + Tailwind)](#frontend-vue-3--tailwind)
- [Database Schema](#database-schema)
- [How It Works](#how-it-works)
- [Adding Test Assets](#adding-test-assets)
- [Technologies Used](#technologies-used)

---

## Features

- **User Authentication**: Register and login functionality
- **Real-time Updates**: Live balance and order updates via Pusher
- **Order Management**: Place, view, and cancel buy/sell orders
- **Automated Matching**: Orders are automatically matched based on price
- **Asset Tracking**: Monitor available and locked assets
- **Commission System**: 1.5% commission on completed trades

---

## Project Setup

### Backend (Laravel)

1. **Clone the repository:**
```bash
git clone <your-repo-url>
cd trading-backend
```

2. **Install PHP dependencies:**
```bash
composer install
```

3. **Configure environment:**

Create a `.env` file from `.env.example` and configure your database and Pusher credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8809
DB_DATABASE=trading
DB_USERNAME=root
DB_PASSWORD=

VITE_PUSHER_APP_KEY=your_pusher_key
VITE_PUSHER_APP_CLUSTER=your_cluster
```

4. **Generate application key:**
```bash
php artisan key:generate
```

5. **Run migrations:**
```bash
php artisan migrate
```

6. **Start the Laravel server:**
```bash
php artisan serve
```

7. **Seed database for test assets:**
```bash
php artisan db:seed
```

The API will be available at `http://127.0.0.1:8000/`

---

### Frontend (Vue 3 + Tailwind)

1. **Navigate to the frontend directory:**
```bash
cd resources/js
# or your frontend folder location
```

2. **Install dependencies:**
```bash
npm install
```

3. **Verify Tailwind configuration:**

Ensure `postcss.config.cjs` and `tailwind.config.cjs` are properly configured.

4. **Start the development server:**
```bash
npm run dev
```

5. **Access the application:**

Visit `http://localhost:5173` (or the URL provided by Vite).

---

## Database Schema

### Users Table

| Column   | Type     | Description          |
|----------|----------|----------------------|
| id       | Integer  | Primary key          |
| name     | String   | User's full name     |
| email    | String   | Email address        |
| password | String   | Hashed password      |
| balance  | Decimal  | USD balance          |

### Assets Table

| Column        | Type    | Description                    |
|---------------|---------|--------------------------------|
| id            | Integer | Primary key                    |
| user_id       | Integer | Foreign key to users           |
| symbol        | String  | Asset symbol (BTC, ETH, etc.)  |
| amount        | Decimal | Available asset amount         |
| locked_amount | Decimal | Amount locked in sell orders   |

### Orders Table

| Column  | Type     | Description                          |
|---------|----------|--------------------------------------|
| id      | Integer  | Primary key                          |
| user_id | Integer  | Foreign key to users                 |
| symbol  | String   | Trading pair symbol                  |
| side    | String   | Order type (buy/sell)                |
| price   | Decimal  | Order price                          |
| amount  | Decimal  | Order amount                         |
| status  | Integer  | 1=open, 2=filled, 3=cancelled        |

---

## How It Works

### 1. User Authentication
- Users register or login to access the trading dashboard
- Upon login, user balances and assets are fetched and displayed

### 2. Placing a Buy Order
- **Validation**: System checks if `user.balance >= price * amount`
- **Balance Lock**: USD amount is deducted from available balance
- **Order Creation**: Order is created with status `open`

### 3. Placing a Sell Order
- **Validation**: System checks if `assets.amount >= order amount`
- **Asset Lock**: Specified amount is moved to `locked_amount`
- **Order Creation**: Order is created with status `open`

### 4. Order Matching (Full Match Only)
- **New BUY order**: Matches with first available SELL order where `sell.price ≤ buy.price`
- **New SELL order**: Matches with first available BUY order where `buy.price ≥ sell.price`
- **Commission**: 1.5% commission is deducted from the buyer
- **Asset Transfer**: USD and assets are transferred between buyer and seller
- **Status Update**: Both orders are marked as `filled`
- **Real-time Broadcast**: `OrderMatched` event is sent via Pusher to both users

### 5. Frontend Updates
- Real-time balance, asset, and order list updates via Pusher WebSocket
- Users can cancel open orders to release locked USD or assets

---

## Adding Test Assets

To test sell orders or order matching, users need assets in their account. Add test data directly to the database:
```sql
-- Add Bitcoin to user with ID 1
INSERT INTO assets (user_id, symbol, amount, locked_amount, created_at, updated_at)
VALUES (1, 'BTC', 1.5, 0, NOW(), NOW());

-- Add Ethereum to user with ID 1
INSERT INTO assets (user_id, symbol, amount, locked_amount, created_at, updated_at)
VALUES (1, 'ETH', 10, 0, NOW(), NOW());
```

---

## Notes

- Orders are matched on a first-come, first-served basis
- Only full matches are supported (partial fills not implemented)
- Commission is automatically calculated and deducted on trade completion
- Ensure Pusher credentials are correctly configured for real-time features
- All monetary values use decimal precision for accuracy

---

## License

This project is open-source and available under the [MIT License](LICENSE).
