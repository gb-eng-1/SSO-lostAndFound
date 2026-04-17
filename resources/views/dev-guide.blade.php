<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dev / Tester Guide — UB Lost &amp; Found</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      font-size: 14px;
      line-height: 1.65;
      color: #1e293b;
      background: #f1f5f9;
      padding: 32px 16px 64px;
    }

    .page-wrap {
      max-width: 960px;
      margin: 0 auto;
    }

    /* ── Banner ── */
    .banner {
      background: #1e3a5f;
      color: #fff;
      border-radius: 10px;
      padding: 28px 32px;
      margin-bottom: 28px;
    }
    .banner h1 {
      font-size: 22px;
      font-weight: 700;
      letter-spacing: .02em;
    }
    .banner p {
      margin-top: 6px;
      font-size: 13px;
      color: #94a3c0;
    }
    .banner .badge {
      display: inline-block;
      margin-top: 12px;
      background: #ef4444;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 4px;
    }

    /* ── Section card ── */
    .card {
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 24px 28px;
      margin-bottom: 20px;
    }
    .card-title {
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #64748b;
      margin-bottom: 16px;
      padding-bottom: 10px;
      border-bottom: 1px solid #e2e8f0;
    }

    /* ── Tables ── */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    thead th {
      background: #f8fafc;
      text-align: left;
      font-weight: 600;
      color: #475569;
      padding: 8px 12px;
      border: 1px solid #e2e8f0;
    }
    tbody td {
      padding: 7px 12px;
      border: 1px solid #e2e8f0;
      vertical-align: top;
    }
    tbody tr:nth-child(even) td { background: #f8fafc; }

    /* ── Code / mono ── */
    code {
      font-family: 'Cascadia Code', 'Consolas', 'Courier New', monospace;
      font-size: 12.5px;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      border-radius: 4px;
      padding: 1px 6px;
      color: #0f172a;
    }
    .code-block {
      background: #0f172a;
      color: #e2e8f0;
      border-radius: 8px;
      padding: 14px 18px;
      font-family: 'Cascadia Code', 'Consolas', 'Courier New', monospace;
      font-size: 12.5px;
      line-height: 1.7;
      overflow-x: auto;
      margin: 10px 0;
    }

    /* ── URL links ── */
    a { color: #2563eb; text-decoration: none; }
    a:hover { text-decoration: underline; }

    /* ── Badges ── */
    .tag {
      display: inline-block;
      font-size: 11px;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 4px;
      line-height: 1.6;
      white-space: nowrap;
    }
    .tag-blue   { background: #dbeafe; color: #1e40af; }
    .tag-green  { background: #dcfce7; color: #166534; }
    .tag-yellow { background: #fef9c3; color: #854d0e; }
    .tag-red    { background: #fee2e2; color: #991b1b; }
    .tag-gray   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
    .tag-purple { background: #f3e8ff; color: #6b21a8; }

    /* ── Scenario list ── */
    .scenario-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(430px, 1fr));
      gap: 14px;
    }
    .scenario-item {
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 14px 16px;
      background: #fafafa;
    }
    .scenario-item .sc-num {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: #94a3b8;
      margin-bottom: 4px;
    }
    .scenario-item .sc-title {
      font-weight: 600;
      font-size: 13px;
      margin-bottom: 8px;
      color: #0f172a;
    }
    .scenario-item dl {
      display: grid;
      grid-template-columns: auto 1fr;
      column-gap: 10px;
      row-gap: 3px;
      font-size: 12.5px;
    }
    .scenario-item dt { color: #64748b; font-weight: 500; white-space: nowrap; }
    .scenario-item dd { color: #1e293b; }

    /* ── Step lists ── */
    .steps ol {
      padding-left: 20px;
    }
    .steps li {
      margin-bottom: 6px;
      font-size: 13px;
    }
    .steps li::marker { color: #2563eb; font-weight: 700; }

    /* ── Two-col layout for URL section ── */
    .url-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    @media (max-width: 640px) {
      .url-grid { grid-template-columns: 1fr; }
      .scenario-grid { grid-template-columns: 1fr; }
    }

    .url-item {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 12px 16px;
    }
    .url-item .url-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 4px; }
    .url-item a { font-size: 13px; font-weight: 600; }

    /* ── Notice ── */
    .notice {
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 8px;
      padding: 12px 16px;
      font-size: 13px;
      color: #78350f;
      margin-bottom: 16px;
    }
    .notice strong { color: #92400e; }

    /* ── Divider ── */
    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 18px 0; }
  </style>
</head>
<body>
<div class="page-wrap">

  {{-- ─── Banner ─────────────────────────────────────────────────────────── --}}
  <div class="banner">
    <h1>UB Lost &amp; Found System &mdash; Developer / Tester Guide</h1>
    <p>Internal reference for testing, seeding, and exploring system workflows. Not linked from any page — access by URL only.</p>
    <span class="badge">Internal Use Only</span>
  </div>

  {{-- ─── Notice ──────────────────────────────────────────────────────────── --}}
  <div class="notice">
    <strong>Note:</strong> Bulk lost/found test data seeders were removed. Ten dummy student accounts (password <code>Password123</code>) are listed under <strong>Student Credentials</strong> and seeded via <code>StudentSeeder</code>. See Hosting and UBmail OAuth below for deployment and Google sign-in.
  </div>

  {{-- ─── System URLs ─────────────────────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">System Entry Points</div>
    <div class="url-grid">
      <div class="url-item">
        <div class="url-label">Admin Portal</div>
        <a href="/admin/login">/admin/login</a>
      </div>
      <div class="url-item">
        <div class="url-label">Student Portal</div>
        <a href="/student/login">/student/login</a>
      </div>
      <div class="url-item">
        <div class="url-label">Admin Dashboard (after login)</div>
        <a href="/admin">/admin</a>
      </div>
      <div class="url-item">
        <div class="url-label">Student Dashboard (after login)</div>
        <a href="/student">/student</a>
      </div>
    </div>
  </div>

  {{-- ─── Admin Credentials ───────────────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">Admin Credentials</div>
    <table>
      <thead>
        <tr>
          <th>Email</th>
          <th>Password</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><code>admin@ub.edu.ph</code></td>
          <td><code>Admin</code></td>
          <td><span class="tag tag-purple">Admin</span></td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- ─── Student Credentials ─────────────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">Student Credentials</div>
    <p style="font-size:13px;color:#475569;margin-bottom:8px;">Dummy accounts are seeded by <code>StudentSeeder</code> (no lost reports). Email is always <code>STUDENTNUMBER@ub.edu.ph</code>. All use password <code>Password123</code>.</p>
    <p style="font-size:12.5px;color:#64748b;margin-bottom:12px;">Populate or refresh: <code>php artisan db:seed --class=StudentSeeder</code></p>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Student No.</th>
          <th>Email</th>
          <th>Name (display)</th>
          <th>Dept</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>1</td><td><code>2501001</code></td><td><code>2501001@ub.edu.ph</code></td><td>Juan Dela Cruz</td><td>CICT</td></tr>
        <tr><td>2</td><td><code>2501002</code></td><td><code>2501002@ub.edu.ph</code></td><td>Maria Santos</td><td>CBA</td></tr>
        <tr><td>3</td><td><code>2501003</code></td><td><code>2501003@ub.edu.ph</code></td><td>Carlo Reyes</td><td>CITE</td></tr>
        <tr><td>4</td><td><code>2501004</code></td><td><code>2501004@ub.edu.ph</code></td><td>Ana Lim</td><td>CAS</td></tr>
        <tr><td>5</td><td><code>2501005</code></td><td><code>2501005@ub.edu.ph</code></td><td>Marco Ramos</td><td>CON</td></tr>
        <tr><td>6</td><td><code>2501006</code></td><td><code>2501006@ub.edu.ph</code></td><td>Jasmine Torres</td><td>CICT</td></tr>
        <tr><td>7</td><td><code>2501007</code></td><td><code>2501007@ub.edu.ph</code></td><td>Diego Villanueva</td><td>CBA</td></tr>
        <tr><td>8</td><td><code>2501008</code></td><td><code>2501008@ub.edu.ph</code></td><td>Sofia Mendoza</td><td>CITE</td></tr>
        <tr><td>9</td><td><code>2501009</code></td><td><code>2501009@ub.edu.ph</code></td><td>Rafael Garcia</td><td>CAS</td></tr>
        <tr><td>10</td><td><code>2501010</code></td><td><code>2501010@ub.edu.ph</code></td><td>Angela Cruz</td><td>CON</td></tr>
      </tbody>
    </table>
    <p style="font-size:13px;color:#64748b;margin-top:12px;">Production accounts may also be created manually or via &ldquo;Sign in with UBmail&rdquo; once Google OAuth is configured (see Hosting / UBmail sections below).</p>
  </div>

  {{-- Seeded items / scenarios removed — test data has been purged --}}

  {{-- ─── Common Workflows ────────────────────────────────────────────────── --}}
  <div class="card steps">
    <div class="card-title">Common Test Workflows</div>

    <p style="font-size:13px;font-weight:600;margin-bottom:8px;color:#0f172a;">A. Student files a new lost report</p>
    <ol>
      <li>Log in as any student account at <a href="/student/login">/student/login</a>.</li>
      <li>Click <strong>I LOST an Item</strong> on the dashboard.</li>
      <li>Fill in category, description, color/brand, and date lost. Attach a photo if desired.</li>
      <li>Submit. The report appears in <strong>All Reports</strong> tab immediately.</li>
      <li>If the auto-match engine finds a candidate, the report moves to <strong>Matched Reports</strong> and a card appears in <strong>Recently Matched Item</strong>.</li>
    </ol>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:8px;color:#0f172a;">B. Admin encodes a new found item</p>
    <ol>
      <li>Log in as admin at <a href="/admin/login">/admin/login</a>.</li>
      <li>On the dashboard, click <strong>Encode Found Item</strong>.</li>
      <li>Fill in category, description, color, brand, found location, and attach a photo.</li>
      <li>Submit. The system auto-matches against all open lost reports. If a match is found, both items are set to <span class="tag tag-yellow">For Verification</span> and the student is notified.</li>
    </ol>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:8px;color:#0f172a;">C. Student acknowledges a match and submits claim intent</p>
    <ol>
      <li>Log in as the matched student (e.g., <strong>Miguel Reyes</strong> — use Scenario 5 or create a fresh match).</li>
      <li>A <strong>Recently Matched Item</strong> card appears on the dashboard.</li>
      <li>Click <strong>View</strong> to open the compare modal. Review both the lost report and found item.</li>
      <li>Click <strong>Claim</strong>. This creates a <span class="tag tag-yellow">Pending</span> claim row in the system.</li>
      <li>The student receives a confirmation. The admin can now proceed with physical verification.</li>
    </ol>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:8px;color:#0f172a;">D. Admin confirms claim completion</p>
    <ol>
      <li>Log in as admin. Navigate to <strong>Matched Items</strong> (or <strong>Dashboard</strong>).</li>
      <li>Locate the item (e.g., <code>UB10004</code> for Miguel's iPhone). If the student has submitted claim intent, the <strong>Confirm Claim</strong> button is active.</li>
      <li>Fill in claimant name, UB email, contact number, and date accomplished. Attach a proof photo.</li>
      <li>Submit. The found item status becomes <span class="tag tag-green">Claimed</span>; the linked lost report becomes <span class="tag tag-green">Resolved</span>.</li>
      <li>The student's Claim History updates to <span class="tag tag-green">Claimed</span>.</li>
    </ol>

  </div>

  {{-- ─── Developer Notes ─────────────────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">Developer Notes</div>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">Environment setup (XAMPP)</p>
    <table style="margin-bottom:16px;">
      <thead><tr><th>Setting</th><th>Value</th></tr></thead>
      <tbody>
        <tr><td>Database host</td><td><code>127.0.0.1:3306</code></td></tr>
        <tr><td>Database name</td><td><code>lostandfound_db</code></td></tr>
        <tr><td>DB user</td><td><code>root</code> (no password)</td></tr>
        <tr><td>PHP version</td><td>8.x (CLI and web must match)</td></tr>
        <tr><td>Laravel version</td><td>11.x</td></tr>
        <tr><td>MySQL <code>max_allowed_packet</code></td><td>Set to <code>16 MB</code> by the seeder at runtime (required for large image base64)</td></tr>
      </tbody>
    </table>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">Key artisan commands</p>
    <div class="code-block"># Seed help page + support contacts
php artisan db:seed --class=HelpPageSeeder

# Run all seeders
php artisan db:seed

# Run migrations
php artisan migrate

# Start dev server (if not using XAMPP)
php artisan serve</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">Item ID formats</p>
    <table>
      <thead><tr><th>Prefix</th><th>Meaning</th><th>Example</th></tr></thead>
      <tbody>
        <tr><td><code>UB</code></td><td>Found item (barcode)</td><td><code>UB10001</code></td></tr>
        <tr><td><code>REF-</code></td><td>Lost report (internal ID)</td><td><code>REF-0000000001</code></td></tr>
        <tr><td><code>TIC-</code></td><td>Lost report (display ticket ID, same number as REF-)</td><td><code>TIC-0000000001</code></td></tr>
        <tr><td><code>CLM-</code></td><td>Claim reference</td><td><code>CLM-SEED0005</code></td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">Item status flow</p>
    <div class="code-block">Found item:  Unclaimed Items → For Verification → Unresolved Claimants → Claimed
                                                                              ↘ Disposed (retention expired, no claim)

Lost report: Unclaimed Items → For Verification → Resolved (when found item is Claimed)
                             ↘ Cancelled (student cancelled report)</div>
  </div>

  {{-- ─── Google Drive Image Storage ─────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">Google Drive Image Storage</div>
    <p style="font-size:13px;color:#475569;margin-bottom:12px;">
      By default, photos are stored as <strong>base64 data URLs</strong> in <code>image_data</code> (items) and <code>proof_photo</code> (claims).
      Optional <strong>server-side</strong> upload runs after <code>App\Support\ReportImageNormalizer</code>: when Drive is enabled and credentials are valid,
      the app stores an HTTPS view URL (<code>https://drive.google.com/uc?export=view&amp;id=…</code>) in the same columns. UI code treats both data URLs and <code>http(s)</code> links as image sources.
    </p>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">Large photos and MySQL <code>max_allowed_packet</code></p>
    <p style="font-size:13px;color:#475569;margin-bottom:12px;">
      If MySQL returns <code>SQLSTATE[08S01] Got a packet bigger than 'max_allowed_packet' bytes</code>, the server rejected an oversized payload.
      This app resizes photos before persistence (<code>ReportImageNormalizer</code> and <code>public/assets/photo-picker.js</code>).
      You can still raise <code>max_allowed_packet</code> in <code>my.ini</code> as a safety net.
    </p>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">Service account setup</p>
    <ol style="font-size:13px;color:#475569;margin-bottom:12px;padding-left:24px;">
      <li>Create a Google Cloud project, enable the <strong>Google Drive API</strong>, and create a <strong>service account</strong> with a JSON key.</li>
      <li>Create or pick a Drive folder for uploads. <strong>Share that folder</strong> with the service account email (shown in the JSON as <code>client_email</code>) with <strong>Editor</strong> access.</li>
      <li>Copy the folder ID from the URL (<code>folders/&lt;FOLDER_ID&gt;</code>).</li>
      <li>Place the JSON key on the server (e.g. <code>storage/app/google-drive-service-account.json</code>) and point <code>GOOGLE_DRIVE_CREDENTIALS_PATH</code> at it. Do not commit the key.</li>
    </ol>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">Environment variables (<code>.env</code>)</p>
    <div class="code-block">GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_CREDENTIALS_PATH=/full/path/to/service-account.json
GOOGLE_DRIVE_FOLDER_ID=your-folder-id
# When true, each uploaded file gets "anyone with the link can view" so &lt;img&gt; tags work without Google sign-in.
GOOGLE_DRIVE_MAKE_FILES_PUBLIC=true</div>
    <p style="font-size:13px;color:#475569;margin-bottom:12px;">
      Config is read from <code>config/services.php</code> under <code>google_drive</code>. Implementation: <code>App\Services\GoogleDriveImageService</code> and <code>App\Support\ReportImageStorage</code>.
    </p>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">Placeholder shared folder (replace in production)</p>
    <div class="code-block"><a href="https://drive.google.com/drive/folders/1wW573BYmNixp6svYEf845QWVoc_Qfy2b?usp=sharing" target="_blank" rel="noopener" style="color:#3b82f6;">https://drive.google.com/drive/folders/1wW573BYmNixp6svYEf845QWVoc_Qfy2b?usp=sharing</a></div>
  </div>

  {{-- ─── Hosting Guide ────────────────────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">Hosting / Deployment Guide</div>
    <p style="font-size:13px;color:#475569;margin-bottom:12px;">What to change when moving from local XAMPP to a live server (shared hosting, VPS, or cPanel).</p>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">1. <code>.env</code> keys to update</p>
    <table style="margin-bottom:16px;">
      <thead><tr><th>Key</th><th>Local value</th><th>Production value</th></tr></thead>
      <tbody>
        <tr><td><code>APP_ENV</code></td><td><code>local</code></td><td><code>production</code></td></tr>
        <tr><td><code>APP_DEBUG</code></td><td><code>true</code></td><td><code>false</code></td></tr>
        <tr><td><code>APP_URL</code></td><td><code>http://localhost</code></td><td>Your live domain, e.g. <code>https://lostandfound.ub.edu.ph</code></td></tr>
        <tr><td><code>DB_HOST</code></td><td><code>127.0.0.1</code></td><td>Hosting provider&rsquo;s MySQL host</td></tr>
        <tr><td><code>DB_DATABASE</code></td><td><code>lostandfound_db</code></td><td>Database name on host</td></tr>
        <tr><td><code>DB_USERNAME</code></td><td><code>root</code></td><td>Production DB username</td></tr>
        <tr><td><code>DB_PASSWORD</code></td><td>(empty)</td><td>Production DB password</td></tr>
      </tbody>
    </table>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">2. Artisan commands on the server</p>
    <div class="code-block"># Run migrations (--force required in production)
php artisan migrate --force

# Create the storage symlink
php artisan storage:link

# Cache config &amp; routes for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">3. Folder permissions</p>
    <p style="font-size:13px;color:#475569;margin-bottom:8px;">The web server must be able to write to <code>storage/</code> and <code>bootstrap/cache/</code>. On Linux hosts:</p>
    <div class="code-block">chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">4. Web server rewrite rules</p>
    <p style="font-size:13px;color:#475569;margin-bottom:8px;">Apache: <code>public/.htaccess</code> is already included. Point the document root to the <code>public/</code> folder. On cPanel, set the document root or use an <code>.htaccess</code> redirect in the site root.</p>
    <p style="font-size:13px;color:#475569;margin-bottom:8px;">Nginx: add a <code>try_files $uri $uri/ /index.php?$query_string;</code> directive in your server block.</p>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">5. Second database connection (optional)</p>
    <p style="font-size:13px;color:#475569;">If the school&rsquo;s student database lives on a separate MySQL server, add a second connection in <code>config/database.php</code> under <code>'connections'</code> (e.g. <code>'school'</code>) and query it with <code>DB::connection('school')-&gt;table(...)</code>.</p>
  </div>

  {{-- ─── UBmail / Google OAuth Guide ─────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">&ldquo;Sign in with UBmail&rdquo; &mdash; Google OAuth Integration Guide</div>
    <p style="font-size:13px;color:#475569;margin-bottom:12px;">Steps and files to touch when adding Google OAuth so students can sign in with their <code>@ub.edu.ph</code> UBmail account instead of a manual password.</p>

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">1. Install Laravel Socialite</p>
    <div class="code-block">composer require laravel/socialite</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">2. Google Cloud Console setup</p>
    <ol style="font-size:13px;color:#475569;padding-left:20px;margin-bottom:12px;">
      <li>Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank">console.cloud.google.com/apis/credentials</a>.</li>
      <li>Create an <strong>OAuth 2.0 Client ID</strong> (Web application).</li>
      <li>Add your <strong>Authorized redirect URI</strong>: <code>https://YOUR_DOMAIN/auth/google/callback</code>.</li>
      <li>Copy the <strong>Client ID</strong> and <strong>Client Secret</strong>.</li>
    </ol>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">3. <code>.env</code> keys to add</p>
    <div class="code-block">GOOGLE_CLIENT_ID=your-client-id-here
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=https://YOUR_DOMAIN/auth/google/callback</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">4. <code>config/services.php</code></p>
    <p style="font-size:13px;color:#475569;margin-bottom:8px;">Add a <code>google</code> entry inside the returned array:</p>
    <div class="code-block">'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI'),
],</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">5. <code>routes/web.php</code> &mdash; add two routes</p>
    <div class="code-block">use App\Http\Controllers\Auth\StudentGoogleController;

Route::get('/auth/google',          [StudentGoogleController::class, 'redirect']);
Route::get('/auth/google/callback', [StudentGoogleController::class, 'callback']);</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">6. New controller &mdash; <code>app/Http/Controllers/Auth/StudentGoogleController.php</code></p>
    <p style="font-size:13px;color:#475569;margin-bottom:8px;">Key logic in <code>callback()</code>:</p>
    <div class="code-block">$googleUser = Socialite::driver('google')->user();
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

return redirect()->route('student.dashboard');</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">7. <code>resources/views/auth/student-login.blade.php</code></p>
    <p style="font-size:13px;color:#475569;margin-bottom:8px;">Add a &ldquo;Sign in with UBmail&rdquo; button/link that points to <code>/auth/google</code>. Place it below or above the existing email/password form. Example:</p>
    <div class="code-block">&lt;a href="/auth/google" class="ubmail-btn"&gt;Sign in with UBmail&lt;/a&gt;</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">8. Email / account format</p>
    <p style="font-size:13px;color:#475569;">Student emails (the <code>students.email</code> column) should use the format <code>STUDENTNUMBER@ub.edu.ph</code> (e.g. <code>2401001@ub.edu.ph</code>). The <code>students.name</code> column holds the full display name. When the Google callback fires, match the incoming email against this column.</p>
  </div>

  {{-- ─── Footer ──────────────────────────────────────────────────────────── --}}
  <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:8px;">
    UB Lost &amp; Found System &mdash; Developer Guide &mdash; Internal Use Only &mdash; Not linked from any page
  </p>

</div>
</body>
</html>
