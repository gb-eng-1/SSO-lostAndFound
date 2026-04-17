# UB Lost & Found System — Developer / Tester Guide
To view php version of this readme: /dev-guide

Internal reference for testing, seeding, and exploring system workflows. In a local install this content is also available at `/dev-guide` (not linked from the UI).

**Internal use only**

> **Note:** Bulk lost/found test data seeders were removed.
> Ten dummy student accounts (password `Password123`) are listed under **Student credentials** and seeded via `StudentSeeder`.
> See **Hosting** and **UBmail OAuth** below for deployment and Google sign-in.

---

## System entry points

| Portal | Path |
|--------|------|
| Admin login | `/admin/login` |
| Student login | `/student/login` |
| Admin dashboard (after login) | `/admin` |
| Student dashboard (after login) | `/student` |

---

## Admin credentials

| Email | Password | Role |
|-------|----------|------|
| `admin@ub.edu.ph` | `Admin` | Admin |

---

## Student credentials

Dummy accounts are seeded by `StudentSeeder` (no lost reports).
Email is always `STUDENTNUMBER@ub.edu.ph`. CURRENTLY, all use password `Password123`.

Populate or refresh:

```bash
php artisan db:seed --class=StudentSeeder
```

| # | Student No. | Email | Name (display) | Dept |
|---|-------------|-------|----------------|------|
| 1 | `2501001` | `2501001@ub.edu.ph` | Juan Dela Cruz | CICT |
| 2 | `2501002` | `2501002@ub.edu.ph` | Maria Santos | CBA |
| 3 | `2501003` | `2501003@ub.edu.ph` | Carlo Reyes | CITE |
| 4 | `2501004` | `2501004@ub.edu.ph` | Ana Lim | CAS |
| 5 | `2501005` | `2501005@ub.edu.ph` | Marco Ramos | CON |
| 6 | `2501006` | `2501006@ub.edu.ph` | Jasmine Torres | CICT |
| 7 | `2501007` | `2501007@ub.edu.ph` | Diego Villanueva | CBA |
| 8 | `2501008` | `2501008@ub.edu.ph` | Sofia Mendoza | CITE |
| 9 | `2501009` | `2501009@ub.edu.ph` | Rafael Garcia | CAS |
| 10 | `2501010` | `2501010@ub.edu.ph` | Angela Cruz | CON |

Production accounts may also be created manually or via “Sign in with UBmail” once Google OAuth is configured (see below).

---

## Common test workflows

### A. Student files a new lost report

1. Log in as any student account at `/student/login`.
2. Click **I LOST an Item** on the dashboard.
3. Fill in category, description, color/brand, and date lost. Attach a photo if desired.
4. Submit. The report appears in **All Reports** immediately.
5. If the auto-match engine finds a candidate, the report moves to **Matched Reports** and a card appears in **Recently Matched Item**.

### B. Admin encodes a new found item

1. Log in as admin at `/admin/login`.
2. On the dashboard, click **Encode Found Item**.
3. Fill in category, description, color, brand, found location, and attach a photo.
4. Submit. The system auto-matches against all open lost reports. If a match is found, both items are set to **For Verification** and the student is notified.

### C. Student acknowledges a match and submits claim intent

1. Log in as the student whose lost report was matched (use a seeded account after you have created a matching pair in workflows A and B).
2. A **Recently Matched Item** card appears on the dashboard.
3. Click **View** to open the compare modal. Review both the lost report and found item.
4. Click **Claim**. This creates a **Pending** claim row in the system.
5. The student receives a confirmation. The admin can now proceed with physical verification.

### D. Admin confirms claim completion

1. Log in as admin. Navigate to **Matched Items** (or **Dashboard**).
2. Locate the matched item by its UB barcode or ticket reference. If the student has submitted claim intent, the **Confirm Claim** button is active.
3. Fill in claimant name, UB email, contact number, and date accomplished. Attach a proof photo.
4. Submit. The found item status becomes **Claimed**; the linked lost report becomes **Resolved**.
5. The student's Claim History updates to **Claimed**.

---

## Developer notes

### Environment setup (XAMPP)

| Setting | Value |
|---------|--------|
| Database host | `127.0.0.1:3306` |
| Database name | `lostandfound_db` |
| DB user | `root` (no password) |
| PHP version | 8.x (CLI and web must match) |
| Laravel version | 11.x |
| MySQL `max_allowed_packet` | Set to `16 MB` by the seeder at runtime (required for large image base64) |

### Key Artisan commands

```bash
# Seed help page + support contacts
php artisan db:seed --class=HelpPageSeeder

# Run all seeders
php artisan db:seed

# Run migrations
php artisan migrate

# Start dev server (if not using XAMPP)
php artisan serve
```

### Item ID formats

| Prefix | Meaning | Example |
|--------|---------|---------|
| `UB` | Found item (barcode) | `UB10001` |
| `REF-` | Lost report (internal ID) | `REF-0000000001` |
| `TIC-` | Lost report (display ticket ID, same number as REF-) | `TIC-0000000001` |
| `CLM-` | Claim reference | `CLM-SEED0005` |

### Item status flow

```
Found item:  Unclaimed Items → For Verification → Unresolved Claimants → Claimed
                                                                              ↘ Disposed (retention expired, no claim)

Lost report: Unclaimed Items → For Verification → Resolved (when found item is Claimed)
                             ↘ Cancelled (student cancelled report)
```

---

## Google Drive image storage

By default, photos are stored as **base64 data URLs** in `image_data` (items) and `proof_photo` (claims). Optional **server-side** upload runs after `App\Support\ReportImageNormalizer`: when Drive is enabled and credentials are valid, the app stores an HTTPS view URL (`https://drive.google.com/uc?export=view&id=…`) in the same columns. UI code treats both data URLs and `http(s)` links as image sources.

### Large photos and MySQL `max_allowed_packet`

If MySQL returns `SQLSTATE[08S01] Got a packet bigger than 'max_allowed_packet' bytes`, the server rejected an oversized payload. This app resizes photos before persistence (`ReportImageNormalizer` and `public/assets/photo-picker.js`). You can still raise `max_allowed_packet` in `my.ini` as a safety net.

### Service account setup

1. Create a Google Cloud project, enable the **Google Drive API**, and create a **service account** with a JSON key.
2. Create or pick a Drive folder for uploads. **Share that folder** with the service account email (shown in the JSON as `client_email`) with **Editor** access.
3. Copy the folder ID from the URL (`folders/<FOLDER_ID>`).
4. Place the JSON key on the server (e.g. `storage/app/google-drive-service-account.json`) and point `GOOGLE_DRIVE_CREDENTIALS_PATH` at it. **Do not commit the key.**

### Environment variables (`.env`)

```env
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_CREDENTIALS_PATH=/full/path/to/service-account.json
GOOGLE_DRIVE_FOLDER_ID=your-folder-id
# When true, each uploaded file gets "anyone with the link can view" so <img> tags work without Google sign-in.
GOOGLE_DRIVE_MAKE_FILES_PUBLIC=true
```

Config is read from `config/services.php` under `google_drive`. Implementation: `App\Services\GoogleDriveImageService` and `App\Support\ReportImageStorage`.

**Placeholder shared folder (replace in production):**  
https://drive.google.com/drive/folders/1wW573BYmNixp6svYEf845QWVoc_Qfy2b?usp=sharing

---

## Hosting / deployment

What to change when moving from local XAMPP to a live server (shared hosting, VPS, or cPanel).

### 1. `.env` keys to update

| Key | Local value | Production value |
|-----|-------------|------------------|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost` | Your live domain, e.g. `https://lostandfound.ub.edu.ph` |
| `DB_HOST` | `127.0.0.1` | Hosting provider's MySQL host |
| `DB_DATABASE` | `lostandfound_db` | Database name on host |
| `DB_USERNAME` | `root` | Production DB username |
| `DB_PASSWORD` | (empty) | Production DB password |

### 2. Artisan commands on the server

```bash
# Run migrations (--force required in production)
php artisan migrate --force

# Create the storage symlink
php artisan storage:link

# Cache config & routes for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Folder permissions

The web server must be able to write to `storage/` and `bootstrap/cache/`. On Linux hosts:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 4. Web server rewrite rules

- **Apache:** `public/.htaccess` is already included. Point the document root to the `public/` folder. On cPanel, set the document root or use an `.htaccess` redirect in the site root.
- **Nginx:** add `try_files $uri $uri/ /index.php?$query_string;` in your server block.

### 5. Second database connection (optional)

If the school's student database lives on a separate MySQL server, add a second connection in `config/database.php` under `'connections'` (e.g. `'school'`) and query it with `DB::connection('school')->table(...)`.

---

## “Sign in with UBmail” — Google OAuth

Steps and files to touch when adding Google OAuth so students can sign in with their `@ub.edu.ph` UBmail account instead of a manual password.

### 1. Install Laravel Socialite

```bash
composer require laravel/socialite
```

### 2. Google Cloud Console setup

1. Go to [Google Cloud Console — Credentials](https://console.cloud.google.com/apis/credentials).
2. Create an **OAuth 2.0 Client ID** (Web application).
3. Add your **Authorized redirect URI**: `https://YOUR_DOMAIN/auth/google/callback`.
4. Copy the **Client ID** and **Client Secret**.

### 3. `.env` keys to add

```env
GOOGLE_CLIENT_ID=your-client-id-here
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=https://YOUR_DOMAIN/auth/google/callback
```

### 4. `config/services.php`

Add a `google` entry inside the returned array:

```php
'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI'),
],
```

### 5. `routes/web.php` — add two routes

```php
use App\Http\Controllers\Auth\StudentGoogleController;

Route::get('/auth/google',          [StudentGoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [StudentGoogleController::class, 'callback']);
```

### 6. New controller — `app/Http/Controllers/Auth/StudentGoogleController.php`

Key logic in `callback()`:

```php
$googleUser = Socialite::driver('google')->user();
$email      = strtolower($googleUser->getEmail());

// Only allow @ub.edu.ph emails
if (! str_ends_with($email, '@ub.edu.ph')) {
    return redirect()->route('student.login')
        ->withErrors(['email' => 'Only @ub.edu.ph accounts are allowed.']);
}

// Look up existing student by email
$student = Student::whereRaw('LOWER(email) = ?', [$email])->first();

if (! $student) {
    // Option A: auto-register from the Google profile
    // Option B: reject with "account not found" error
}

session([
    'student_id'    => $student->id,
    'student_email' => $student->email,
    'student_name'  => $student->name ?? $googleUser->getName(),
]);

return redirect()->route('student.dashboard');
```

### 7. `resources/views/auth/student-login.blade.php`

Add a “Sign in with UBmail” button/link that points to `/auth/google`. Place it below or above the existing email/password form. Example:

```html
<a href="/auth/google" class="ubmail-btn">Sign in with UBmail</a>
```

### 8. Email / account format

Student emails (the `students.email` column) should use the format `STUDENTNUMBER@ub.edu.ph` (e.g. `2401001@ub.edu.ph`). The `students.name` column holds the full display name. When the Google callback fires, match the incoming email against this column.

---

*UB Lost & Found System — Developer guide — internal use only*
