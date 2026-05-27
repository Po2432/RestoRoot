
# RestoRoot 🍔

RestoRoot is a lightweight, portable, self-hosted PHP CMS designed specifically for restaurants, cafes, and bars. Built on a clean, "old school" file-based architecture, it requires **no frameworks**, no composer packages, and runs on Vanilla PHP and SQLite.

This software is designed to be easily deployed on standard shared hosting environments or run locally for development.

---

## ✨ Key Highlights & Features
- **One-Click Bootstrapper (`installer.php`)**: Dynamically pulls the latest stable source code directly from GitHub, creates folders, and secures server directories.
- **Product Dashboard**: Manage categories, upload item images, toggle item availability, and define custom variations/add-ons (e.g., "+ Cheese").
- **Smart Localizations (Geo-Banners)**: Displays targeted regional banners using a lightweight IP-lookup service without requiring browser permissions.
- **Dismissible Announcement Banner**: Maintain a global, dismissible banner (managed from the settings panel) to communicate current specials or alerts.
- **Searchable Menu & Allergy Indicators**: Includes real-time client-side search filtering and an allergy guide displaying tags (Vegan, Gluten-Free, Dairy, Nuts).
- **Viral Share Loops**: Built-in item sharing generates social share URLs (`/share.php?id=X`) to foster organic user advertising.
- **Customer Feedback Logs**: A private channel for customers to leave feedback, accessible only to admins.
- **Adaptive Branding**: Customize the currency symbol, opening hours, theme colors, and restaurant logo dynamically from the settings panel.

---

## 🚀 Installation & Setup Workflow

RestoRoot utilizes a clean **two-step setup** to ensure complete file integrity and directory security:

```text
  [1. Upload installer.php]
             │
             ▼
[Run installer.php in Browser] ──► Auto-downloads latest files & secures directories
             │
             ▼
 [Self-Deletes installer.php]  ──► The bootstrapper cleans itself up automatically
             │
             ▼
  [Run install.php Setup]      ──► Builds the SQLite schema & default Admin account
             │
             ▼
 [Manually Delete install.php] ──► Mandatory security step to protect database
```

---

## 💾 Method A: Production Web Hosting Installation

> [!WARNING]
> Some hosts may block you from accessing your site untill an index.html or index.php file is created. If you get errors from your browser when you try to access the installer file, create an index.html file with no content or just `hi`. You will delete that file after installation is completed to make sure all users land up at correct index.php page.
### Step 1: Bootstrap the Files
1. Download the standalone `installer.php` file from this repository.
2. Upload `installer.php` to your web server's root directory (e.g., `public_html/`) via FTP or cPanel File Manager.
3. Ensure your root directory is writable (`chmod 775` or `777` depending on host).
4. Access the bootstrapper via your browser:
   ```text
   http://example.com/installer.php
   ```
5. Click **Pull Files & Secure System**. The script will fetch all necessary code and generate the standard `/data` and `/uploads` directories.

### Step 2: Initialize the System
1. Once the downloads finish, click **Launch Setup & Self-Delete**.
2. **Note:** This action automatically and permanently deletes `installer.php` from your server.
3. You will be redirected to the wizard setup (`install.php`).
4. Review the success confirmation. Your database file (`data/database.sqlite`) is now created, and the default administrator user has been initialized:
   - **Default User**: `admin`
   - **Default Password**: `admin123`

### Step 3: Mandatory Cleanup
1. ⚠️ **IMPORTANT SECURITY STEP:** Once the installation screen confirms success, you must **manually delete `install.php`** from your server. This script does not self-delete to prevent accidental database resets, so you must remove it manually via FTP, cPanel, or command line.
2. Log in at `http://example.com/login.php` to change your password, accept the EULA, and begin customizing your menu.

---

## 💻 Method B: Local Development Server Setup

You do not need heavy local environments like Apache, Nginx, or Docker to run RestoRoot locally. You can use PHP's built-in development server.

### 1. Install PHP on your Computer
- **macOS**: Install via Homebrew:
  ```bash
  brew install php
  ```
- **Linux (Ubuntu/Debian)**:
  ```bash
  sudo apt update && sudo apt install php php-sqlite3
  ```
- **Windows**:
  1. Download the Thread Safe ZIP package from [windows.php.net](https://windows.php.net/download/).
  2. Extract it to `C:\php`.
  3. Rename `php.ini-development` to `php.ini`. Open it in a text editor and uncomment these lines by removing the semicolon (`;`):
     ```ini
     extension=pdo_sqlite
     extension=sqlite3
     ```
  4. Add `C:\php` to your Windows System Environment Variables (PATH).

### 2. Run the Local Server
1. Create a folder on your computer (e.g., `RestoRoot/`).
2. Place `installer.php` inside that folder.
3. Open your terminal or command prompt, navigate to that directory, and run:
   ```bash
   php -S localhost:8000
   ```
4. Open your web browser and go to:
   ```text
   http://localhost:8000/installer.php
   ```
5. Follow the standard installation steps to pull down the repository files and run the setup.
6. Remember to **manually delete `install.php`** once setup is complete.

---

## 🔒 Server Security Hardening
RestoRoot uses SQLite, meaning your database is a local file (`/data/database.sqlite`). 

- **Apache/LiteSpeed**: The bootstrapper automatically creates a `/data/.htaccess` file that blocks all web traffic from directly accessing the SQLite file.
- **Nginx**: You must manually configure your server block to prevent database downloads. Add the following block to your Nginx site configuration file:
  ```nginx
  location ~* \.sqlite$ {
      deny all;
  }
  ```

---

## 📄 License & Attribution

- **App Code Ownership**: All application architecture, logic, and structure are owned by **Po2432**.
- **Attribution**: You must preserve the "Powered by RestoRoot" attribution link in the footer of all pages.
- **Modifications**: You are permitted to modify or build upon the source code strictly for your own internal business use.
- **Distribution**: You are strictly prohibited from distributing, publishing, or sharing modified or unmodified versions of this software anywhere.
- **Commercial Use**: Limited exclusively to running the software to support your commercial establishment. No commercial sales or reselling of the software is permitted.

*For complete details, please read `LICENSE.md` and `EULA.md` inside the repository.*
