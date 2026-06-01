<?php
/**
 * SQLite database initializer for Replit dev environment.
 * Called automatically from db.php when using SQLite driver.
 */

function sqliteIsInitialized(PDO $pdo): bool {
    $r = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
    return (bool)$r;
}

function sqliteInit(PDO $pdo): void {
    $pdo->exec("PRAGMA journal_mode=WAL");
    $pdo->exec("PRAGMA foreign_keys=ON");

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        display_name TEXT NOT NULL DEFAULT '',
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'client',
        avatar_url TEXT,
        phone TEXT,
        org_name TEXT,
        district TEXT,
        bio TEXT,
        email_verified INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1,
        last_login_at TEXT,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        setting_key TEXT NOT NULL UNIQUE,
        setting_val TEXT,
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS team_members (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT '',
        bio TEXT,
        photo_url TEXT,
        email TEXT,
        linkedin_url TEXT,
        is_leadership INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        summary TEXT,
        description TEXT,
        icon TEXT DEFAULT 'settings',
        icon_color TEXT DEFAULT 'blue',
        features TEXT,
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        tagline TEXT,
        summary TEXT,
        description TEXT,
        icon TEXT DEFAULT 'box',
        badge TEXT,
        category TEXT,
        highlights TEXT,
        features TEXT,
        price_from REAL,
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS pricing_plans (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        tag TEXT,
        price_label TEXT NOT NULL DEFAULT 'Contact us',
        period TEXT DEFAULT '/ month',
        cta_label TEXT DEFAULT 'Get started',
        cta_url TEXT,
        is_popular INTEGER NOT NULL DEFAULT 0,
        features TEXT,
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS testimonials (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        author_name TEXT NOT NULL,
        author_role TEXT,
        author_org TEXT,
        photo_url TEXT,
        quote TEXT NOT NULL,
        rating INTEGER NOT NULL DEFAULT 5,
        product_ref TEXT,
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS gallery (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT,
        description TEXT,
        image_url TEXT NOT NULL,
        category TEXT DEFAULT 'General',
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS partners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        logo_url TEXT,
        url TEXT,
        type TEXT NOT NULL DEFAULT 'client',
        district TEXT,
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS news (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        excerpt TEXT,
        content TEXT,
        image_url TEXT,
        cover_url TEXT,
        author_name TEXT DEFAULT 'Ankur Infotech Pvt. Ltd.',
        author_title TEXT DEFAULT 'Team',
        read_time INTEGER,
        category TEXT DEFAULT 'News',
        tags TEXT,
        featured INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1,
        published INTEGER NOT NULL DEFAULT 0,
        published_at TEXT,
        views INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS faqs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category TEXT NOT NULL DEFAULT 'General',
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS job_listings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        department TEXT,
        location TEXT DEFAULT 'Kathmandu, Nepal',
        type TEXT NOT NULL DEFAULT 'full-time',
        experience TEXT,
        salary_range TEXT,
        short_desc TEXT,
        description TEXT,
        requirements TEXT,
        perks TEXT,
        deadline TEXT,
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS contact_submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        org_name TEXT,
        subject TEXT DEFAULT 'General Enquiry',
        message TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'new',
        notes TEXT,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS demo_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product TEXT NOT NULL,
        org_name TEXT NOT NULL,
        contact_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        members INTEGER,
        message TEXT,
        status TEXT NOT NULL DEFAULT 'new',
        notes TEXT,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS announcements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        body TEXT,
        type TEXT NOT NULL DEFAULT 'info',
        scope TEXT NOT NULL DEFAULT 'banner',
        page_target TEXT,
        btn_text TEXT,
        btn_url TEXT,
        active INTEGER NOT NULL DEFAULT 1,
        dismissible INTEGER NOT NULL DEFAULT 1,
        starts_at TEXT,
        ends_at TEXT,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS clients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        org_name TEXT NOT NULL,
        logo_url TEXT,
        contact_email TEXT,
        contact_phone TEXT,
        status TEXT NOT NULL DEFAULT 'active',
        district TEXT,
        province TEXT,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    CREATE TABLE IF NOT EXISTS portfolio (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        client_name TEXT,
        category TEXT,
        excerpt TEXT,
        description TEXT,
        image_url TEXT,
        result_metric TEXT,
        featured INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1,
        position INTEGER NOT NULL DEFAULT 0,
        published_at TEXT,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

    -- Missing tables added for full feature support
    CREATE TABLE IF NOT EXISTS email_verifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expires_at TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS password_resets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL UNIQUE,
        token TEXT NOT NULL UNIQUE,
        expires_at TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        identifier TEXT NOT NULL UNIQUE,
        attempts INTEGER NOT NULL DEFAULT 1,
        last_attempt_at TEXT NOT NULL DEFAULT (datetime('now')),
        locked_until TEXT DEFAULT NULL
    );
    CREATE TABLE IF NOT EXISTS api_rate_limits (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_hash TEXT NOT NULL UNIQUE,
        hits INTEGER NOT NULL DEFAULT 1,
        window_start TEXT NOT NULL DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS user_services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'active',
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS client_subscriptions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        product_id INTEGER DEFAULT NULL,
        product_name TEXT NOT NULL,
        plan_name TEXT NOT NULL,
        license_key TEXT DEFAULT NULL,
        deployment_type TEXT DEFAULT NULL,
        branches INTEGER NOT NULL DEFAULT 1,
        members_limit INTEGER DEFAULT NULL,
        amount REAL DEFAULT NULL,
        billing_cycle TEXT DEFAULT 'monthly',
        status TEXT NOT NULL DEFAULT 'active',
        starts_at TEXT DEFAULT NULL,
        expires_at TEXT DEFAULT NULL,
        next_renewal TEXT DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS crm_leads (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        org_name TEXT DEFAULT NULL,
        email TEXT DEFAULT NULL,
        phone TEXT DEFAULT NULL,
        district TEXT DEFAULT NULL,
        source TEXT DEFAULT NULL,
        source_ref_id INTEGER DEFAULT NULL,
        products_interest TEXT DEFAULT NULL,
        stage TEXT NOT NULL DEFAULT 'prospect',
        stage_notes TEXT DEFAULT NULL,
        deal_value REAL DEFAULT NULL,
        next_followup TEXT DEFAULT NULL,
        last_contact_at TEXT DEFAULT NULL,
        assigned_to INTEGER DEFAULT NULL,
        won_at TEXT DEFAULT NULL,
        lost_reason TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now')),
        updated_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS crm_followups (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lead_id INTEGER NOT NULL,
        user_id INTEGER DEFAULT NULL,
        type TEXT NOT NULL DEFAULT 'call',
        notes TEXT DEFAULT NULL,
        outcome TEXT DEFAULT NULL,
        next_followup TEXT DEFAULT NULL,
        followup_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
    CREATE TABLE IF NOT EXISTS crm_proposals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        lead_id INTEGER NOT NULL,
        user_id INTEGER DEFAULT NULL,
        title TEXT NOT NULL,
        products TEXT DEFAULT NULL,
        amount REAL DEFAULT NULL,
        valid_until TEXT DEFAULT NULL,
        status TEXT NOT NULL DEFAULT 'draft',
        notes TEXT DEFAULT NULL,
        file_url TEXT DEFAULT NULL,
        sent_at TEXT DEFAULT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );
    ");

    // Seed admin user (password: Admin@12345)
    $hash = password_hash('Admin@12345', PASSWORD_BCRYPT, ['cost' => 10]);
    $pdo->prepare("INSERT OR IGNORE INTO users (display_name, email, password_hash, role, email_verified, active)
        VALUES ('Ankur Admin', 'ankurinfotech8@gmail.com', ?, 'admin', 1, 1)")->execute([$hash]);

    // Seed site settings
    $settings = [
        ['site_name',        'Ankur Infotech Pvt. Ltd.'],
        ['site_tagline',     'Cooperative Software for Nepal'],
        ['contact_email',    'ankurinfotech8@gmail.com'],
        ['contact_phone',    '+977-071-438585, 071-437612'],
        ['address',          'Butwal, Rupandehi, Nepal'],
        ['logo_url',         ''],
        ['social_links',     '{"facebook":"","twitter":"","linkedin":"","youtube":""}'],
        ['whatsapp_number',  '97771438585'],
        ['whatsapp_enabled', '1'],
        ['stat_1_value',     '120+'],
        ['stat_1_label',     'Cooperatives Served'],
        ['stat_2_value',     '8 yrs'],
        ['stat_2_label',     'Nepal Experience'],
        ['stat_3_value',     '<2 hr'],
        ['stat_3_label',     'Avg Support Response'],
        ['stat_4_value',     '99.9%'],
        ['stat_4_label',     'Platform Uptime'],
        ['hero_title',       'Cooperative Software Built for Nepal'],
        ['hero_subtitle',    'IT Solutions & Software Services — purpose-built for Nepal. Reliable, locally supported, and tailored to your business.'],
        ['hero_cta_primary',  'Book a Free Demo'],
        ['hero_cta_secondary','See Pricing'],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO site_settings (setting_key, setting_val) VALUES (?, ?)");
    foreach ($settings as [$k, $v]) $stmt->execute([$k, $v]);

    // Seed testimonials
    $testimonials = [
        ['Aarav Shrestha',  'Manager',    'Himalayan Saving Co-op',  'हाम्रो सहकारीको दैनिक काम एकदम सजिलो भयो। Support team ले समयमै reply दिन्छन्।',          5],
        ['Maya Tamang',     'IT Lead',    'Yatra Cooperative',       'CBS system ले हाम्रो NRB reporting काम एकदम सजिलो भयो। Highly recommended!',               5],
        ['Rohan Karki',     'Founder',    'Sajilo Sahakari',         'Website redesign पछि नयाँ सदस्य join गर्ने rate बढ्यो। Excellent work!',                    5],
        ['Sunita Gurung',   'President',  'Butwal Business Co-op', 'Ankur Infotech को सेवा एकदम राम्रो छ — सधैं समयमा support पाउँछौं।',     5],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO testimonials (author_name, author_role, author_org, quote, rating, active, position) VALUES (?,?,?,?,?,1,?)");
    foreach ($testimonials as $i => $t) $stmt->execute(array_merge($t, [$i+1]));

    // Seed partners (client logos for marquee)
    $partners = [
        ['Himalayan Saving Co-op',           'client',  'Kathmandu', null],
        ['Yatra Cooperative',                'client',  'Lalitpur',  null],
        ['Sajilo Sahakari',                  'client',  'Bhaktapur', null],
        ['Butwal Business Co-op',          'client',  'Kaski',     null],
        ['Janahit Bachat Sahakari',          'client',  'Chitwan',   null],
        ['Annapurna Multipurpose Co-op',     'client',  'Kaski',     null],
        ['Seti Gandaki Saving Co-op',        'client',  'Surkhet',   null],
        ['Lumbini Cooperative Society',      'client',  'Rupandehi', null],
        ['Everest Community Finance',        'client',  'Solukhumbu',null],
        ['Bagmati Bachat Tatha Rin',         'client',  'Kathmandu', null],
        ['Janasewa Sahakari Sanstha',        'client',  'Chitwan',   null],
        ['Mechi Cooperative Bank',           'client',  'Jhapa',     null],
        ['Koshi Development Co-op',          'client',  'Morang',    null],
        ['Rapti Saving Society',             'client',  'Dang',      null],
        ['Bishal Multipurpose Co-op',        'client',  'Banke',     null],
        ['Nepal Rastra Bank',                'partner', 'Kathmandu', 'https://www.nrb.org.np'],
        ['Connectips',                       'partner', 'Kathmandu', null],
        ['eSewa',                            'partner', 'Kathmandu', 'https://esewa.com.np'],
        ['Khalti',                           'partner', 'Kathmandu', 'https://khalti.com'],
        ['Nepal Clearing House',             'partner', 'Kathmandu', null],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO partners (name, type, district, url, active, position) VALUES (?,?,?,?,1,?)");
    foreach ($partners as $i => $p) $stmt->execute(array_merge($p, [$i+1]));

    // Seed team members
    $team = [
        ['Rajesh Shrestha', 'Founder & CEO',    'Building cooperative software for Nepal since 2017. 15 years in fintech.',       1, 1],
        ['Sita Tamang',     'CTO',              'Leads product architecture and engineering. Ex-NIC Asia digital team.',           1, 2],
        ['Bikash Karki',    'Head of Sales',    'Former cooperative manager turned tech advocate. 10+ yrs in सहकारी sector.',     1, 3],
        ['Anita Rai',       'Lead Developer',   'PHP & MySQL specialist. Core of the CBS engine team.',                           0, 4],
        ['Prakash Thapa',   'Mobile Dev Lead',  'React Native / Flutter apps. Built 20+ banking apps in Nepal.',                  0, 5],
        ['Manisha Gurung',  'Support Lead',     'Ensures every ticket gets a response. Speaks Nepali, Hindi, English.',           0, 6],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO team_members (name, role, bio, is_leadership, active, position) VALUES (?,?,?,?,1,?)");
    foreach ($team as $t) $stmt->execute($t);

    // Seed services
    $services = [
        ['Core Banking System', 'cbs',          'Full-featured CBS with member KYC, savings, loans, share capital and 100+ NRB reports.',  'monitor',    'blue',   '["Member & KYC","Savings & FD","Loan Lifecycle","Share Capital","NRB Reports","Multi-branch","BS Calendar"]', 1],
        ['Mobile Banking App',  'mobile',       'Branded Android & iOS app — balance, transfers, QR payments, EMI, biometric login.',        'smartphone', 'teal',   '["Android & iOS","QR Payments","Push Alerts","Biometric Login"]',                                        2],
        ['Document Management', 'dms',          'Paperless KYC & loan files with OCR indexing, version history and role-based access.',       'file-text',  'purple', '["OCR Indexing","Version History","Role-based Access","Audit Trail"]',                                  3],
        ['HR & Payroll',        'hr',           'Full HR, payroll, TDS, SSF and CIT ready with employee self-service portal.',                'users',      'green',  '["Payroll & TDS","SSF & CIT","ESS Portal","Leave Management"]',                                         4],
        ['Cooperative Website', 'website',      'SEO-optimised cooperative website with notices, loan calculator and multilingual CMS.',       'globe',      'orange', '["SEO Optimised","Multilingual","Notice Board","Loan Calculator","Self-service CMS"]',                  5],
        ['24×7 Support',        'support',      'Real people, not bots. On-site visits across all 7 provinces. <2 hr SLA for critical issues.','headphones', 'rose',   '["<2 hr SLA","On-site Visits","24×7 Portal","All Provinces"]',                                         6],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO services (title, slug, summary, icon, icon_color, features, active, position) VALUES (?,?,?,?,?,?,1,?)");
    foreach ($services as $s) $stmt->execute([$s[0],$s[1],$s[2],$s[3],$s[4],$s[5],$s[6]]);

    // Seed pricing plans
    $plans = [
        ['Starter', 'Single-branch cooperatives',   'NPR 4,999', '/ month', 'Get started', 0, '["Up to 500 members","Core Banking (CBS)","Web portal + notices","Email & ticket support","Monthly backups"]',             1],
        ['Growth',  'Multi-branch — most popular',  'NPR 12,999','/ month', 'Book a demo', 1, '["Up to 5,000 members","CBS + Mobile Banking App","DMS + role-based access","Priority <2 hr support","Quarterly training"]', 2],
        ['Enterprise','Large & regulated cooperatives','Custom',  '',        'Contact us',  0, '["Unlimited members & branches","All modules included","Dedicated success manager","24×7 critical SLA","On-premise option"]', 3],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO pricing_plans (name, tag, price_label, period, cta_label, is_popular, features, active, position) VALUES (?,?,?,?,?,?,?,1,?)");
    foreach ($plans as $p) $stmt->execute($p);

    // Seed FAQs
    $faqs = [
        ['General',   'What is Ankur Infotech Pvt. Ltd.?',                   'Ankur Infotech Pvt. Ltd. is a software company based in Butwal, Rupandehi, Nepal, providing IT solutions and software services.', 1],
        ['General',   'How many cooperatives use your software?', 'Over 120 cooperatives across Nepal — from small single-branch savings cooperatives to large multi-province financial cooperatives.',            2],
        ['Products',  'Is your CBS NRB-compliant?',               'Yes. Our CBS includes 100+ pre-built NRB report templates and is updated with every regulatory change at no extra cost.',                       3],
        ['Products',  'Does it support Nepali calendar (BS)?',    'Yes. Every module — savings, loans, reports, payslips — is fully Bikram Sambat native.',                                                       4],
        ['Pricing',   'Are there hidden fees?',                   'No. We provide a full itemized quote before any commitment — including setup, data migration and training costs.',                              5],
        ['Support',   'What is your support response time?',      'Growth and Enterprise clients: < 2 hr SLA for P1 issues. Starter: next-business-day. All clients have the 24×7 ticket portal.',               6],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO faqs (category, question, answer, active, position) VALUES (?,?,?,1,?)");
    foreach ($faqs as $f) $stmt->execute($f);

    // Seed news articles
    $news = [
        ['CBS v4.0 Released with NRB 2081 Compliance Updates', 'cbs-v4-nrb-2081',
         'Our latest CBS release includes all NRB 2081 circular updates, improved loan lifecycle management and faster report generation.',
         'News', 1, 1, date('Y-m-d H:i:s', strtotime('-7 days'))],
        ['New Mobile Banking App Live for 15 Cooperatives', 'mobile-banking-live-15',
         'We are thrilled to announce that our new branded mobile banking app is now live for 15 cooperative clients across 6 districts of Nepal.',
         'Updates', 1, 0, date('Y-m-d H:i:s', strtotime('-21 days'))],
        ['Ankur Infotech Pvt. Ltd. Expands to Madhesh Province', 'madhesh-expansion',
         'We opened our regional office in Birgunj, making it easier to serve cooperatives across Madhesh Province with on-site support.',
         'Company', 0, 0, date('Y-m-d H:i:s', strtotime('-45 days'))],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO news (title, slug, excerpt, category, featured, published, published_at, active) VALUES (?,?,?,?,?,?,?,1)");
    foreach ($news as $n) $stmt->execute($n);

    // Seed job listings
    $jobs = [
        ['Senior PHP Engineer', 'senior-php-engineer', 'Engineering', 'full-time', '3+ years', 'NPR 80,000–130,000', 'Build and scale our Core Banking platform used by 120+ cooperatives.', 1],
        ['QA Engineer',         'qa-engineer',         'Quality',     'full-time', '1+ years', 'NPR 45,000–70,000',  'Own quality for products trusted with members\' money.',                1],
        ['UI/UX Designer',      'ui-ux-designer',      'Design',      'full-time', '2+ years', 'NPR 50,000–90,000',  'Design simple, fast interfaces for cooperative staff and members.',    1],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO job_listings (title, slug, department, type, experience, salary_range, short_desc, active) VALUES (?,?,?,?,?,?,?,1)");
    foreach ($jobs as $j) $stmt->execute($j);
}
