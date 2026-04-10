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
    <strong>Before you start:</strong> Run <code>php artisan db:seed --class=TestDataSeeder</code> in the project root to populate the database with all test data described below. Re-running the seeder wipes and re-seeds all student, item, claim, and log data from scratch.
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
    <p style="font-size:13px;color:#64748b;margin-bottom:12px;">All student accounts use password <code>Password123</code>. Login with <strong>email</strong> or <strong>student_number@ub.edu.ph</strong>.</p>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Student No.</th>
          <th>Email</th>
          <th>Name</th>
          <th>Dept</th>
          <th>Key scenario(s)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td><code>2401001</code></td>
          <td><code>lea.robles@ub.edu.ph</code></td>
          <td>Lea Robles</td>
          <td>CICT</td>
          <td>S1 (unmatched), extra report</td>
        </tr>
        <tr>
          <td>2</td>
          <td><code>2401002</code></td>
          <td><code>marco.vega@ub.edu.ph</code></td>
          <td>Marco Vega</td>
          <td>CITE</td>
          <td>S3 (auto-matched, for verification)</td>
        </tr>
        <tr>
          <td>3</td>
          <td><code>2401003</code></td>
          <td><code>dina.cruz@ub.edu.ph</code></td>
          <td>Dina Cruz</td>
          <td>CBA</td>
          <td>S4 (one of two matched reports)</td>
        </tr>
        <tr>
          <td>4</td>
          <td><code>2401004</code></td>
          <td><code>jay.ortiz@ub.edu.ph</code></td>
          <td>Jay Ortiz</td>
          <td>CAS</td>
          <td>S6 (fully claimed)</td>
        </tr>
        <tr>
          <td>5</td>
          <td><code>1920501</code></td>
          <td><code>anna.santos@ub.edu.ph</code></td>
          <td>Anna Santos</td>
          <td>CON</td>
          <td>S4 (second matched report), extra unmatched</td>
        </tr>
        <tr>
          <td>6</td>
          <td><code>2310602</code></td>
          <td><code>miguel.reyes@ub.edu.ph</code></td>
          <td>Miguel Reyes</td>
          <td>CICT</td>
          <td>S5 (claim intent submitted, pending)</td>
        </tr>
        <tr>
          <td>7</td>
          <td><code>2215703</code></td>
          <td><code>grace.lim@ub.edu.ph</code></td>
          <td>Grace Lim</td>
          <td>CBA</td>
          <td>S7 (external ID claimed)</td>
        </tr>
        <tr>
          <td>8</td>
          <td><code>1830804</code></td>
          <td><code>kevin.tan@ub.edu.ph</code></td>
          <td>Kevin Tan</td>
          <td>CITE</td>
          <td>S9 (cancelled report), extra unmatched</td>
        </tr>
        <tr>
          <td>9</td>
          <td><code>2108905</code></td>
          <td><code>sofia.gabriel@ub.edu.ph</code></td>
          <td>Sofia Gabriel</td>
          <td>CAS</td>
          <td>S11 (full claim with photo, unresolved claimants), extra unmatched</td>
        </tr>
        <tr>
          <td>10</td>
          <td><code>2312006</code></td>
          <td><code>ben.aquino@ub.edu.ph</code></td>
          <td>Ben Aquino</td>
          <td>CON</td>
          <td>S12 (rejected claim), extra unmatched</td>
        </tr>
      </tbody>
    </table>
  </div>

  {{-- ─── Seeded Items Reference ──────────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">Seeded Items Quick Reference</div>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Item</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr><td><code>UB10001</code></td><td>Miscellaneous</td><td>6-sided Dice</td><td><span class="tag tag-gray">Unclaimed Items</span></td></tr>
        <tr><td><code>UB10002</code></td><td>Electronics &amp; Gadgets</td><td>iPhone w/ Swirly Case</td><td><span class="tag tag-yellow">For Verification</span></td></tr>
        <tr><td><code>UB10003</code></td><td>Apparel &amp; Accessories</td><td>Small Silver Ring Necklace</td><td><span class="tag tag-yellow">For Verification</span></td></tr>
        <tr><td><code>UB10004</code></td><td>Electronics &amp; Gadgets</td><td>iPhone 13 (Black)</td><td><span class="tag tag-yellow">For Verification</span></td></tr>
        <tr><td><code>UB10005</code></td><td>Personal Belongings</td><td>Star-shaped Keychain</td><td><span class="tag tag-green">Claimed</span></td></tr>
        <tr><td><code>UB10006</code></td><td>ID &amp; Nameplate</td><td>Student ID Card (Grace Lim)</td><td><span class="tag tag-green">Claimed</span></td></tr>
        <tr><td><code>UB10007</code></td><td>Apparel &amp; Accessories</td><td>Small Silver Ring (overdue)</td><td><span class="tag tag-gray">Unclaimed Items</span> <span class="tag tag-red" style="font-size:10px;">3yr old</span></td></tr>
        <tr><td><code>UB10008</code></td><td>Miscellaneous</td><td>Red Dice Set</td><td><span class="tag tag-gray">Unclaimed Items</span></td></tr>
        <tr><td><code>UB10009</code></td><td>Personal Belongings</td><td>Star Keychain (old)</td><td><span class="tag tag-red">Disposed</span></td></tr>
        <tr><td><code>UB10010</code></td><td>Electronics &amp; Gadgets</td><td>iPhone w/ Pink Case</td><td><span class="tag tag-blue">Unresolved Claimants</span></td></tr>
        <tr><td><code>UB10011</code></td><td>Apparel &amp; Accessories</td><td>Silver Pandora Bracelet</td><td><span class="tag tag-gray">Unclaimed Items</span></td></tr>
        <tr><td><code>UB10012</code></td><td>Electronics &amp; Gadgets</td><td>Samsung Galaxy Buds</td><td><span class="tag tag-gray">Unclaimed Items</span></td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <table>
      <thead>
        <tr>
          <th>REF ID (= Ticket)</th>
          <th>Filed by</th>
          <th>Item</th>
          <th>Status</th>
          <th>Matched to</th>
        </tr>
      </thead>
      <tbody>
        <tr><td><code>REF-0000000001</code> / <code>TIC-0000000001</code></td><td>Lea Robles</td><td>Samsung Galaxy Earbuds</td><td><span class="tag tag-gray">Unclaimed Items</span></td><td>—</td></tr>
        <tr><td><code>REF-0000000002</code></td><td>Marco Vega</td><td>iPhone 14 w/ Swirly Case</td><td><span class="tag tag-yellow">For Verification</span></td><td><code>UB10002</code></td></tr>
        <tr><td><code>REF-0000000003</code></td><td>Dina Cruz</td><td>Silver Ring Necklace</td><td><span class="tag tag-yellow">For Verification</span></td><td><code>UB10003</code></td></tr>
        <tr><td><code>REF-0000000004</code></td><td>Anna Santos</td><td>Silver Ring Necklace</td><td><span class="tag tag-yellow">For Verification</span></td><td><code>UB10003</code></td></tr>
        <tr><td><code>REF-0000000005</code></td><td>Miguel Reyes</td><td>iPhone 13 Black</td><td><span class="tag tag-yellow">For Verification</span></td><td><code>UB10004</code></td></tr>
        <tr><td><code>REF-0000000006</code></td><td>Jay Ortiz</td><td>Gold Star Keychain</td><td><span class="tag tag-green">Resolved</span></td><td><code>UB10005</code></td></tr>
        <tr><td><code>REF-0000000007</code></td><td>Grace Lim</td><td>Student ID Card</td><td><span class="tag tag-green">Resolved</span></td><td><code>UB10006</code></td></tr>
        <tr><td><code>REF-0000000008</code></td><td>Kevin Tan</td><td>Red Dice Pouch</td><td><span class="tag tag-red">Cancelled</span></td><td>—</td></tr>
        <tr><td><code>REF-0000000009</code></td><td>Sofia Gabriel</td><td>iPhone w/ Pink Case</td><td><span class="tag tag-yellow">For Verification</span></td><td><code>UB10010</code></td></tr>
        <tr><td><code>REF-0000000010</code></td><td>Ben Aquino</td><td>Silver Pandora Bracelet</td><td><span class="tag tag-yellow">For Verification</span></td><td><code>UB10011</code></td></tr>
        <tr><td><code>REF-0000000011</code></td><td>Lea Robles</td><td>Brown Leather Wallet</td><td><span class="tag tag-gray">Unclaimed Items</span></td><td>—</td></tr>
        <tr><td><code>REF-0000000012</code></td><td>Kevin Tan</td><td>Samsung Earbuds</td><td><span class="tag tag-gray">Unclaimed Items</span></td><td>—</td></tr>
        <tr><td><code>REF-0000000013</code></td><td>Anna Santos</td><td>National ID</td><td><span class="tag tag-gray">Unclaimed Items</span></td><td>—</td></tr>
        <tr><td><code>REF-0000000014</code></td><td>Ben Aquino</td><td>Blue Water Bottle</td><td><span class="tag tag-gray">Unclaimed Items</span></td><td>—</td></tr>
        <tr><td><code>REF-0000000015</code></td><td>Sofia Gabriel</td><td>Black Umbrella</td><td><span class="tag tag-gray">Unclaimed Items</span></td><td>—</td></tr>
      </tbody>
    </table>
  </div>

  {{-- ─── Test Scenarios ──────────────────────────────────────────────────── --}}
  <div class="card">
    <div class="card-title">Seeded Test Scenarios</div>
    <div class="scenario-grid">

      <div class="scenario-item">
        <div class="sc-num">Scenario 1</div>
        <div class="sc-title">Report filed, no match yet</div>
        <dl>
          <dt>Student</dt><dd>Lea Robles</dd>
          <dt>Lost report</dt><dd><code>REF-0000000001</code></dd>
          <dt>Status</dt><dd><span class="tag tag-gray">Unclaimed Items</span></dd>
          <dt>Verify</dt><dd>Report appears in student All Reports tab; no card in Recently Matched</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 2</div>
        <div class="sc-title">Found item encoded, not matched</div>
        <dl>
          <dt>Found item</dt><dd><code>UB10001</code> (Dice)</dd>
          <dt>Status</dt><dd><span class="tag tag-gray">Unclaimed Items</span></dd>
          <dt>Verify</dt><dd>Visible in admin Found Items list; no linked lost report</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 3</div>
        <div class="sc-title">Auto-matched, pending verification</div>
        <dl>
          <dt>Student</dt><dd>Marco Vega</dd>
          <dt>Lost report</dt><dd><code>REF-0000000002</code></dd>
          <dt>Found item</dt><dd><code>UB10002</code> (iPhone)</dd>
          <dt>Both status</dt><dd><span class="tag tag-yellow">For Verification</span></dd>
          <dt>Verify</dt><dd>Matched card visible on student dashboard; appears in admin Matched Items list</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 4</div>
        <div class="sc-title">Two reports matched to one found item</div>
        <dl>
          <dt>Students</dt><dd>Dina Cruz + Anna Santos</dd>
          <dt>Reports</dt><dd><code>REF-0000000003</code> + <code>REF-0000000004</code></dd>
          <dt>Found item</dt><dd><code>UB10003</code> (Necklace)</dd>
          <dt>All status</dt><dd><span class="tag tag-yellow">For Verification</span></dd>
          <dt>Verify</dt><dd>Both students see a match; admin sees two claimants for UB10003</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 5</div>
        <div class="sc-title">Student submitted claim intent (pending admin)</div>
        <dl>
          <dt>Student</dt><dd>Miguel Reyes</dd>
          <dt>Lost report</dt><dd><code>REF-0000000005</code></dd>
          <dt>Found item</dt><dd><code>UB10004</code> (iPhone 13)</dd>
          <dt>Claim</dt><dd><code>CLM-SEED0005</code> — <span class="tag tag-yellow">Pending</span></dd>
          <dt>Verify</dt><dd>Claim history shows Pending; admin can proceed to confirm claim</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 6</div>
        <div class="sc-title">Fully claimed by admin</div>
        <dl>
          <dt>Student</dt><dd>Jay Ortiz</dd>
          <dt>Lost report</dt><dd><code>REF-0000000006</code> — <span class="tag tag-green">Resolved</span></dd>
          <dt>Found item</dt><dd><code>UB10005</code> — <span class="tag tag-green">Claimed</span></dd>
          <dt>Claim</dt><dd><code>CLM-SEED0006</code></dd>
          <dt>Verify</dt><dd>Claim History shows Claimed; item has Claim Record appended to description</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 7</div>
        <div class="sc-title">External ID (ID &amp; Nameplate) — found and claimed</div>
        <dl>
          <dt>Student</dt><dd>Grace Lim</dd>
          <dt>Lost report</dt><dd><code>REF-0000000007</code> — <span class="tag tag-green">Resolved</span></dd>
          <dt>Found item</dt><dd><code>UB10006</code> (Student ID) — <span class="tag tag-green">Claimed</span></dd>
          <dt>Verify</dt><dd>ID &amp; Nameplate type; no student-intent gate was required; claim completed directly</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 8</div>
        <div class="sc-title">Overdue / expired retention</div>
        <dl>
          <dt>Found item</dt><dd><code>UB10007</code> (Silver Ring)</dd>
          <dt>Date encoded</dt><dd>~3 years ago (past 2-year retention)</dd>
          <dt>Status</dt><dd><span class="tag tag-gray">Unclaimed Items</span></dd>
          <dt>Verify</dt><dd>Admin Found Items list shows retention warning / overdue flag</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 9</div>
        <div class="sc-title">Report cancelled by student</div>
        <dl>
          <dt>Student</dt><dd>Kevin Tan</dd>
          <dt>Lost report</dt><dd><code>REF-0000000008</code> — <span class="tag tag-red">Cancelled</span></dd>
          <dt>Found item reverted</dt><dd><code>UB10008</code> — <span class="tag tag-gray">Unclaimed Items</span></dd>
          <dt>Verify</dt><dd>Report does not appear in student active reports; found item is back in pool</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 10</div>
        <div class="sc-title">Disposed found item</div>
        <dl>
          <dt>Found item</dt><dd><code>UB10009</code> (Keychain)</dd>
          <dt>Date encoded</dt><dd>~2 yrs 3 months ago</dd>
          <dt>Status</dt><dd><span class="tag tag-red">Disposed</span></dd>
          <dt>Verify</dt><dd>Item should appear only in admin History / archive views; not in active lists</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 11</div>
        <div class="sc-title">Full claim submitted with photo (Unresolved Claimants)</div>
        <dl>
          <dt>Student</dt><dd>Sofia Gabriel</dd>
          <dt>Lost report</dt><dd><code>REF-0000000009</code></dd>
          <dt>Found item</dt><dd><code>UB10010</code> (iPhone Pink) — <span class="tag tag-blue">Unresolved Claimants</span></dd>
          <dt>Claim</dt><dd><code>CLM-SEED0011</code> — <span class="tag tag-yellow">Pending</span> (proof photo attached)</dd>
          <dt>Verify</dt><dd>Claim History shows Pending; admin sees proof photo on claim detail</dd>
        </dl>
      </div>

      <div class="scenario-item">
        <div class="sc-num">Scenario 12</div>
        <div class="sc-title">Claim rejected by admin</div>
        <dl>
          <dt>Student</dt><dd>Ben Aquino</dd>
          <dt>Lost report</dt><dd><code>REF-0000000010</code></dd>
          <dt>Found item</dt><dd><code>UB10011</code> (Silver Bracelet) — <span class="tag tag-gray">Unclaimed Items</span></dd>
          <dt>Claim</dt><dd><code>CLM-SEED0012</code> — <span class="tag tag-red">Rejected</span></dd>
          <dt>Verify</dt><dd>Claim History shows Rejected; found item reverted to Unclaimed Items after rejection</dd>
        </dl>
      </div>

    </div>
  </div>

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

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:8px;color:#0f172a;">E. Reset test data</p>
    <ol>
      <li>Open a terminal in the project root (<code>c:\xampp\htdocs\campus-backend-laravel</code>).</li>
      <li>Run:</li>
    </ol>
    <div class="code-block">php artisan db:seed --class=TestDataSeeder</div>
    <p style="font-size:12.5px;color:#64748b;margin-top:8px;">This truncates all student, item, claim, activity log, and notification data, then re-seeds everything from scratch. Admin account is preserved.</p>
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
    <div class="code-block"># Full test data reset
php artisan db:seed --class=TestDataSeeder

# Only help page + support contacts (no students/items)
php artisan db:seed --class=HelpPageSeeder

# Full reset (delegates to TestDataSeeder)
php artisan db:seed --class=CleanInstallSeeder

# Run all seeders (local env only — also runs TestDataSeeder)
php artisan db:seed

# Start dev server (if not using XAMPP)
php artisan serve</div>

    <hr class="divider">

    <p style="font-size:13px;font-weight:600;margin-bottom:6px;color:#0f172a;">School database integration (future)</p>
    <p style="font-size:13px;color:#475569;margin-bottom:8px;">See the comment block at the top of <code>database/seeders/TestDataSeeder.php</code> for a step-by-step guide on connecting to the official UB student database, setting up SSO/LDAP authentication, and migrating to hosted MySQL.</p>

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

  {{-- ─── Footer ──────────────────────────────────────────────────────────── --}}
  <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:8px;">
    UB Lost &amp; Found System &mdash; Developer Guide &mdash; Internal Use Only &mdash; Not linked from any page
  </p>

</div>
</body>
</html>
