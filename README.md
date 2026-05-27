# RestoRoot 🍔

RestoRoot is a highly portable, lightweight, self-hosted PHP CMS designed specifically for restaurants and cafes. Built with an "old school" file-based architecture, it requires **no frameworks**, no composer dependencies, and runs purely on Vanilla PHP and SQLite. Just upload and go!

## 🚀 Features
- **Zero Configuration DB**: Uses SQLite via PDO. No MySQL setup required.
- **Dynamic Branding**: Upload a logo and set brand colors. The CSS UI and dynamic favicons update automatically.
- **Menu Management**: Add categories, upload food images, and track standard vs. loyalty pricing.
- **Smart Localizations (Geo-Banners)**: Set up regional localizations (e.g., "California") to trigger special banners using a lightweight IP-lookup—no intrusive browser permissions needed!
- **Built-in QR Generator**: Generates a scannable table QR code directly in the admin dashboard.
- **Secure Architecture**: Role-Based Access utilizing `password_hash()` for Managers and Admins.

---

## 🛠️ Installation on Shared Hosting (cPanel, Plesk, FTP)
1. Download or clone this repository.
2. Upload all files to your `public_html` or web root directory via FTP.
3. Ensure the `/data` and `/uploads` folders have write permissions (`chmod 775` or `777`).
4. Navigate to `http://yourdomain.com/install.php` in your browser.
5. The database will automatically construct itself.
6. **IMPORTANT**: Delete `install.php` from your server immediately for security!
7. Login at `/login.php` with:
   - **Username**: `admin`
   - **Password**: `admin123`
8. Navigate to Settings to change your password and configure your branding.

---

## 💻 Local Development Server Setup
You do not need a heavy web server like Apache, Nginx, XAMPP, or Docker to develop and test RestoRoot locally! You can use PHP's built-in development server.

### 1. Install PHP on your Computer
- **Windows**:
  1. Download the VS16 x64 Thread Safe ZIP from [windows.php.net](https://windows.php.net/download/).
  2. Extract it to `C:\php`.
  3. Rename `php.ini-development` to `php.ini`. Open it and remove the semicolon (`;`) before `extension=pdo_sqlite` and `extension=sqlite3`.
  4. Add `C:\php` to your Windows System Environment Variables (PATH).
- **Mac**: Open Terminal and install via Homebrew: `brew install php`
- **Linux (Ubuntu/Debian)**: Open Terminal and run: `sudo apt update && sudo apt install php php-sqlite3`

### 2. Run the Server
Open your terminal or command prompt, navigate to the folder where you extracted RestoRoot, and type:

```bash
cd /path/to/RestoRoot
php -S localhost:8000
