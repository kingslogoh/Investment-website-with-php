
# 💼 Investment Platform Installation Guide

Welcome to the official guide for installing and setting up your **Investment Web App**. Please follow the instructions carefully to ensure everything works as expected.

---

## 📁 Step 1: Extract and Upload Files

1. Download the `investment.zip` file.
2. **Unzip** the file on your computer.
3. Upload the extracted folder contents to your server's public directory.

📍 Your file structure should look like this:
```
yourdomain.com/investment/
```

---

## 🛠️ Step 2: Create the Database

1. Log in to **phpMyAdmin** or your MySQL control panel.
2. Create a new database (e.g., `investment_db`).
3. Import the provided SQL file into the newly created database.

> ⚠️ **NOTE:** The SQL file is not free. Contact the developer (see below) to get access.

---

## ⚙️ Step 3: Configure Database Connection

Open this file:

```
/investment/config/database.php
```

Update the database details as follows:

```php
$dbname = "Your_database_name";
$username = "Your_user_name";
$password = "Your_password";
```

---

## 👤 Step 4: Create Admin Account

1. Open your browser and visit:

```
yourdomain.com/investment/admin_register.php
```

2. Fill out the form to register your admin account.

---

## 💳 Step 5: Set Up Payment Settings

### 🔑 Flutterwave

- Go to [https://flutterwave.com](https://flutterwave.com)
- Generate your **API Secret Key** and **Public Key**
- Enter them in:

```
yourdomain.com/investment/admin_payment_settings.php
```

### 💰 PayPal

- Enter your **PayPal business email address** in the admin panel under payment settings.

### 🔗 Binance Smart Chain (BNB)

- Visit [https://bscscan.com](https://bscscan.com)
- Create a free account and get your **BSCScan API key**
- Add your **BNB wallet address** where you'll receive payments

---

## ⚙️ Step 6: Complete Admin Setup

- Log in to the admin dashboard
- Go to **Settings**
- Configure:
  - Website name
  - Logo
  - Investment plans
  - Withdrawal methods
  - Homepage texts and features

---

## 📞 Contact the Developer

Before proceeding with SQL setup, **you must contact the developer** for the SQL file and activation.

- 📧 **Email**: [kingslogh@gmail.com](mailto:kingslogh@gmail.com)  
- 📱 **WhatsApp**: +2347032324586

> ❗ This system is **not free**. Licensing must be confirmed before use.

---

## ✅ Final Checklist

- [ ] Unzipped and uploaded `investment` files
- [ ] Created database and imported SQL
- [ ] Updated `database.php` with correct credentials
- [ ] Created admin account
- [ ] Added Flutterwave, PayPal, and BNB credentials
- [ ] Completed admin settings setup
- [ ] Contacted the developer

---

Enjoy running your Investment Web App! 💼💰
