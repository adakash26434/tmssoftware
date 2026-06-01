-- ============================================================
--  Ankur Infotech Pvt. Ltd. — COMPLETE FRESH DATABASE (All-in-One)
--  MySQL 8.0+ / MariaDB 10.5+  |  utf8mb4
--  Version: v4 (merged — fresh install only, no migration needed)
--
--  cPanel / phpMyAdmin Steps:
--    1. Database select garnus: bandanas_it (ya tpako DB name)
--    2. Operations → Drop the database (ya naya empty DB banaus)
--    3. Import tab → yo file select garnus → Go
--
--  Command line:
--    mysql -u USER -p DBNAME < ankur_fresh_database.sql
--
--  Admin login: ankurinfotech8@gmail.com / Admin@12345
--  ⚠  Login pachi TURANT password change garnus!
-- ============================================================

SET NAMES utf8mb4;

-- ══════════════════════════════════════════════════════════════
-- DROP existing tables (fresh install — removes old schema)
-- ══════════════════════════════════════════════════════════════
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `status_incident_updates`;
DROP TABLE IF EXISTS `status_incidents`;
DROP TABLE IF EXISTS `status_components`;
DROP TABLE IF EXISTS `sla_policies`;
DROP TABLE IF EXISTS `site_pages`;
DROP TABLE IF EXISTS `renewal_reminders`;
DROP TABLE IF EXISTS `onboarding_progress`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `livechat_messages`;
DROP TABLE IF EXISTS `kb_feedback`;
DROP TABLE IF EXISTS `kb_articles`;
DROP TABLE IF EXISTS `kb_categories`;
DROP TABLE IF EXISTS `email_intake_log`;
DROP TABLE IF EXISTS `cron_runs`;
DROP TABLE IF EXISTS `client_licenses`;
DROP TABLE IF EXISTS `branches`;
DROP TABLE IF EXISTS `api_request_log`;
DROP TABLE IF EXISTS `api_tokens`;
DROP TABLE IF EXISTS `activity_log`;
DROP TABLE IF EXISTS `support_contacts`;
DROP TABLE IF EXISTS `support_messages`;
DROP TABLE IF EXISTS `support_conversations`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `audit_log`;
DROP TABLE IF EXISTS `banners`;
DROP TABLE IF EXISTS `ticket_internal_notes`;
DROP TABLE IF EXISTS `ticket_replies`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `subscribers`;
DROP TABLE IF EXISTS `demo_requests`;
DROP TABLE IF EXISTS `contact_submissions`;
DROP TABLE IF EXISTS `job_applications`;
DROP TABLE IF EXISTS `job_listings`;
DROP TABLE IF EXISTS `partners`;
DROP TABLE IF EXISTS `gallery`;
DROP TABLE IF EXISTS `testimonials`;
DROP TABLE IF EXISTS `faqs`;
DROP TABLE IF EXISTS `news`;
DROP TABLE IF EXISTS `portfolio`;
DROP TABLE IF EXISTS `pricing_plans`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `team_members`;
DROP TABLE IF EXISTS `site_settings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `crm_proposals`;
DROP TABLE IF EXISTS `crm_followups`;
DROP TABLE IF EXISTS `crm_leads`;
DROP TABLE IF EXISTS `client_subscriptions`;
DROP TABLE IF EXISTS `user_services`;
DROP TABLE IF EXISTS `api_rate_limits`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `email_verifications`;
SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_ENGINE_SUBSTITUTION';

-- ══════════════════════════════════════════════════════════════
-- TABLE DEFINITIONS
-- ══════════════════════════════════════════════════════════════

-- ──────────────────────────────────────────────────────────────
-- 1. USERS & AUTH
-- ──────────────────────────────────────────────────────────────
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  display_name  VARCHAR(120)  NOT NULL DEFAULT '',
  email         VARCHAR(180)  NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,
  role          ENUM('client','editor','support','admin','superadmin') NOT NULL DEFAULT 'client',
  avatar_url    VARCHAR(500)  DEFAULT NULL,
  phone         VARCHAR(30)   DEFAULT NULL,
  org_name      VARCHAR(200)  DEFAULT NULL,
  district      VARCHAR(100)  DEFAULT NULL,
  bio           TEXT          DEFAULT NULL,
  email_verified TINYINT(1)  NOT NULL DEFAULT 0,
  active        TINYINT(1)    NOT NULL DEFAULT 1,
  last_login_at DATETIME      DEFAULT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email  (email),
  INDEX idx_role   (role),
  INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 2. SITE SETTINGS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE site_settings (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key  VARCHAR(100) NOT NULL UNIQUE,
  setting_val  LONGTEXT     DEFAULT NULL,
  updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 3. TEAM MEMBERS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE team_members (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120)  NOT NULL,
  role          VARCHAR(120)  NOT NULL DEFAULT '',
  bio           TEXT          DEFAULT NULL,
  photo_url     VARCHAR(500)  DEFAULT NULL,
  email         VARCHAR(180)  DEFAULT NULL,
  linkedin_url  VARCHAR(500)  DEFAULT NULL,
  twitter_url   VARCHAR(500)  DEFAULT NULL,
  github_url    VARCHAR(500)  DEFAULT NULL,
  is_leadership TINYINT(1)    NOT NULL DEFAULT 0,
  active        TINYINT(1)    NOT NULL DEFAULT 1,
  position      SMALLINT      NOT NULL DEFAULT 0,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active_pos (active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 4. SERVICES
-- ──────────────────────────────────────────────────────────────
CREATE TABLE services (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200)  NOT NULL,
  slug        VARCHAR(200)  NOT NULL UNIQUE,
  summary     TEXT          DEFAULT NULL,
  description LONGTEXT      DEFAULT NULL,
  icon        VARCHAR(20)   DEFAULT '⚙️',
  icon_color  VARCHAR(50)   DEFAULT 'blue',
  features    JSON          DEFAULT NULL,
  active      TINYINT(1)    NOT NULL DEFAULT 1,
  position    SMALLINT      NOT NULL DEFAULT 0,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug   (slug),
  INDEX idx_active (active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 5. PRODUCTS (v2 columns already included)
-- ──────────────────────────────────────────────────────────────
CREATE TABLE products (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name                VARCHAR(200)  NOT NULL,
  slug                VARCHAR(200)  NOT NULL UNIQUE,
  tagline             VARCHAR(300)  DEFAULT NULL,
  summary             TEXT          DEFAULT NULL,
  description         LONGTEXT      DEFAULT NULL,
  icon                VARCHAR(20)   DEFAULT '📦',
  lucide_icon         VARCHAR(60)   DEFAULT 'package',
  icon_color          VARCHAR(30)   DEFAULT 'blue',
  badge               VARCHAR(60)   DEFAULT NULL,
  category            VARCHAR(100)  DEFAULT NULL,
  accent              VARCHAR(100)  DEFAULT 'from-blue-500 to-indigo-600',
  highlights          JSON          DEFAULT NULL,
  features            JSON          DEFAULT NULL,
  modules             JSON          DEFAULT NULL,
  tech_stack          JSON          DEFAULT NULL,
  screenshots         JSON          DEFAULT NULL,
  compliance          JSON          DEFAULT NULL,
  faqs                JSON          DEFAULT NULL,
  price_from          DECIMAL(12,2) DEFAULT NULL,
  show_on_home        TINYINT(1)    NOT NULL DEFAULT 1,
  home_position       SMALLINT      NOT NULL DEFAULT 0,
  home_card_wide      TINYINT(1)    NOT NULL DEFAULT 0,
  home_card_dark      TINYINT(1)    NOT NULL DEFAULT 0,
  home_bg_css         TEXT          DEFAULT NULL,
  demo_screenshot_url VARCHAR(500)  DEFAULT NULL,
  tab_label           VARCHAR(100)  DEFAULT NULL,
  sort_order          SMALLINT      NOT NULL DEFAULT 0,
  active              TINYINT(1)    NOT NULL DEFAULT 1,
  position            SMALLINT      NOT NULL DEFAULT 0,
  created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug   (slug),
  INDEX idx_active (active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 6. PRICING PLANS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE pricing_plans (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100)  NOT NULL,
  tag         VARCHAR(200)  DEFAULT NULL,
  price_label VARCHAR(100)  NOT NULL DEFAULT 'Contact us',
  period      VARCHAR(60)   DEFAULT '/ month',
  cta_label   VARCHAR(80)   DEFAULT 'Get started',
  cta_url     VARCHAR(300)  DEFAULT NULL,
  is_popular  TINYINT(1)    NOT NULL DEFAULT 0,
  features    JSON          DEFAULT NULL,
  active      TINYINT(1)    NOT NULL DEFAULT 1,
  position    SMALLINT      NOT NULL DEFAULT 0,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 7. PORTFOLIO / CASE STUDIES
-- ──────────────────────────────────────────────────────────────
CREATE TABLE portfolio (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title          VARCHAR(300)  NOT NULL,
  slug           VARCHAR(300)  NOT NULL UNIQUE,
  client_name    VARCHAR(200)  DEFAULT NULL,
  category       VARCHAR(100)  DEFAULT NULL,
  excerpt        TEXT          DEFAULT NULL,
  summary        TEXT          DEFAULT NULL,
  description    LONGTEXT      DEFAULT NULL,
  image_url      VARCHAR(500)  DEFAULT NULL,
  cover_url      VARCHAR(500)  DEFAULT NULL,
  gallery        JSON          DEFAULT NULL,
  tags           JSON          DEFAULT NULL,
  tech_used      JSON          DEFAULT NULL,
  results        JSON          DEFAULT NULL,
  result_metric  VARCHAR(300)  DEFAULT NULL,
  project_url    VARCHAR(500)  DEFAULT NULL,
  url            VARCHAR(500)  DEFAULT NULL,
  featured       TINYINT(1)    NOT NULL DEFAULT 0,
  active         TINYINT(1)    NOT NULL DEFAULT 1,
  position       SMALLINT      NOT NULL DEFAULT 0,
  published_at   DATE          DEFAULT NULL,
  created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_slug     (slug),
  INDEX idx_featured (featured, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 8. NEWS / BLOG
-- ──────────────────────────────────────────────────────────────
CREATE TABLE news (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(400)  NOT NULL,
  slug         VARCHAR(400)  NOT NULL UNIQUE,
  excerpt      TEXT          DEFAULT NULL,
  content      LONGTEXT      DEFAULT NULL,
  image_url    VARCHAR(500)  DEFAULT NULL,
  cover_url    VARCHAR(500)  DEFAULT NULL,
  author_id    INT UNSIGNED  DEFAULT NULL,
  author_name  VARCHAR(150)  DEFAULT 'Ankur Infotech Pvt. Ltd.',
  author_title VARCHAR(150)  DEFAULT 'Team',
  read_time    TINYINT UNSIGNED DEFAULT NULL,
  category     VARCHAR(100)  DEFAULT 'News',
  tags         JSON          DEFAULT NULL,
  featured     TINYINT(1)    NOT NULL DEFAULT 0,
  active       TINYINT(1)    NOT NULL DEFAULT 1,
  published    TINYINT(1)    NOT NULL DEFAULT 0,
  published_at DATETIME      DEFAULT NULL,
  meta_title   VARCHAR(300)  DEFAULT NULL,
  meta_desc    VARCHAR(500)  DEFAULT NULL,
  views        INT UNSIGNED  NOT NULL DEFAULT 0,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_slug      (slug),
  INDEX idx_active    (active, published_at DESC),
  INDEX idx_published (published, published_at DESC),
  INDEX idx_category  (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 9. FAQs
-- ──────────────────────────────────────────────────────────────
CREATE TABLE faqs (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category   VARCHAR(100)  NOT NULL DEFAULT 'General',
  question   TEXT          NOT NULL,
  answer     TEXT          NOT NULL,
  active     TINYINT(1)    NOT NULL DEFAULT 1,
  position   SMALLINT      NOT NULL DEFAULT 0,
  created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active_cat (active, category, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 10. TESTIMONIALS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE testimonials (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  author_name VARCHAR(150)  NOT NULL,
  author_role VARCHAR(200)  DEFAULT NULL,
  author_org  VARCHAR(200)  DEFAULT NULL,
  photo_url   VARCHAR(500)  DEFAULT NULL,
  quote       TEXT          NOT NULL,
  rating      TINYINT       NOT NULL DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
  product_ref VARCHAR(100)  DEFAULT NULL,
  active      TINYINT(1)    NOT NULL DEFAULT 1,
  position    SMALLINT      NOT NULL DEFAULT 0,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active (active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 11. GALLERY
-- ──────────────────────────────────────────────────────────────
CREATE TABLE gallery (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200)  DEFAULT NULL,
  description TEXT          DEFAULT NULL,
  image_url   VARCHAR(500)  NOT NULL,
  category    VARCHAR(100)  DEFAULT 'General',
  active      TINYINT(1)    NOT NULL DEFAULT 1,
  position    SMALLINT      NOT NULL DEFAULT 0,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 12. PARTNERS / CLIENTS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE partners (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(200)  NOT NULL,
  logo_url   VARCHAR(500)  DEFAULT NULL,
  url        VARCHAR(500)  DEFAULT NULL,
  type       ENUM('client','partner','investor') NOT NULL DEFAULT 'client',
  district   VARCHAR(100)  DEFAULT NULL,
  active     TINYINT(1)    NOT NULL DEFAULT 1,
  position   SMALLINT      NOT NULL DEFAULT 0,
  created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active (active, type, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 13. CAREERS / JOBS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE job_listings (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(200)  NOT NULL,
  slug         VARCHAR(200)  NOT NULL UNIQUE,
  department   VARCHAR(100)  DEFAULT NULL,
  location     VARCHAR(150)  DEFAULT 'Birgunj, Nepal',
  type         ENUM('full-time','part-time','contract','internship') NOT NULL DEFAULT 'full-time',
  experience   VARCHAR(100)  DEFAULT NULL,
  salary_range VARCHAR(100)  DEFAULT NULL,
  description  LONGTEXT      DEFAULT NULL,
  requirements TEXT          DEFAULT NULL,
  perks        JSON          DEFAULT NULL,
  deadline     DATE          DEFAULT NULL,
  active       TINYINT(1)    NOT NULL DEFAULT 1,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_applications (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_listing_id  INT UNSIGNED  DEFAULT NULL,
  full_name       VARCHAR(150)  NOT NULL,
  email           VARCHAR(180)  NOT NULL,
  phone           VARCHAR(30)   DEFAULT NULL,
  cover_letter    TEXT          DEFAULT NULL,
  resume_url      VARCHAR(500)  DEFAULT NULL,
  status          ENUM('new','reviewing','shortlisted','interview','hired','rejected') NOT NULL DEFAULT 'new',
  notes           TEXT          DEFAULT NULL,
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (job_listing_id) REFERENCES job_listings(id) ON DELETE SET NULL,
  INDEX idx_job    (job_listing_id),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 14. CONTACT SUBMISSIONS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE contact_submissions (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(150)  NOT NULL,
  email      VARCHAR(180)  NOT NULL,
  phone      VARCHAR(30)   DEFAULT NULL,
  org_name   VARCHAR(200)  DEFAULT NULL,
  subject    VARCHAR(300)  DEFAULT 'General Enquiry',
  message    TEXT          NOT NULL,
  status     ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
  notes      TEXT          DEFAULT NULL,
  created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status  (status),
  INDEX idx_created (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 15. DEMO REQUESTS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE demo_requests (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product      VARCHAR(100)  NOT NULL,
  org_name     VARCHAR(200)  NOT NULL,
  contact_name VARCHAR(120)  NOT NULL,
  email        VARCHAR(180)  NOT NULL,
  phone        VARCHAR(30)   DEFAULT NULL,
  members      INT           DEFAULT NULL,
  message      TEXT          DEFAULT NULL,
  preferred_at DATETIME      DEFAULT NULL,
  status       ENUM('new','contacted','scheduled','won','lost') NOT NULL DEFAULT 'new',
  assigned_to  INT UNSIGNED  DEFAULT NULL,
  notes        TEXT          DEFAULT NULL,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status  (status, created_at DESC),
  INDEX idx_product (product)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 16. NEWSLETTER SUBSCRIBERS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE subscribers (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email        VARCHAR(180)  NOT NULL UNIQUE,
  name         VARCHAR(120)  DEFAULT NULL,
  status       ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
  source       VARCHAR(60)   DEFAULT 'website',
  confirmed_at DATETIME      DEFAULT NULL,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 17. ORDERS / LEAD PIPELINE
-- ──────────────────────────────────────────────────────────────
CREATE TABLE orders (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED  DEFAULT NULL,
  product_id   INT UNSIGNED  DEFAULT NULL,
  product_name VARCHAR(200)  NOT NULL,
  plan_name    VARCHAR(100)  DEFAULT NULL,
  order_ref    VARCHAR(60)   DEFAULT NULL,
  amount       DECIMAL(12,2) DEFAULT NULL,
  currency     CHAR(3)       NOT NULL DEFAULT 'NPR',
  status       ENUM('pending','confirmed','active','cancelled','completed') NOT NULL DEFAULT 'pending',
  notes        TEXT          DEFAULT NULL,
  starts_at    DATE          DEFAULT NULL,
  expires_at   DATE          DEFAULT NULL,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_user   (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 18. SUPPORT TICKETS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE tickets (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED  NOT NULL,
  assigned_to     INT UNSIGNED  DEFAULT NULL,
  number          INT UNSIGNED  NOT NULL DEFAULT 0,
  subject         VARCHAR(400)  NOT NULL,
  body            LONGTEXT      NOT NULL DEFAULT '',
  category        VARCHAR(100)  DEFAULT 'General',
  priority        ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  status          ENUM('open','pending','in_progress','replied','resolved','closed') NOT NULL DEFAULT 'open',
  product         VARCHAR(100)  DEFAULT NULL,
  product_ref     VARCHAR(100)  DEFAULT NULL,
  attachment_url  VARCHAR(500)  DEFAULT NULL,
  last_message_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_at     DATETIME      DEFAULT NULL,
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user     (user_id),
  INDEX idx_assigned (assigned_to),
  INDEX idx_status   (status, last_message_at DESC),
  INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_replies (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id      INT UNSIGNED  NOT NULL,
  author_id      INT UNSIGNED  NOT NULL,
  author_role    ENUM('client','staff','admin') NOT NULL DEFAULT 'client',
  body           LONGTEXT      NOT NULL,
  attachment_url VARCHAR(500)  DEFAULT NULL,
  is_internal    TINYINT(1)    NOT NULL DEFAULT 0,
  created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id)  ON DELETE CASCADE,
  INDEX idx_ticket (ticket_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_internal_notes (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id    INT UNSIGNED  NOT NULL,
  author_id    INT UNSIGNED  NOT NULL,
  note         LONGTEXT      NOT NULL,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
  FOREIGN KEY (author_id) REFERENCES users(id)  ON DELETE CASCADE,
  INDEX idx_ticket (ticket_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 19. PAGE BANNERS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE banners (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(300)  NOT NULL,
  subtitle     VARCHAR(400)  DEFAULT NULL,
  banner_style VARCHAR(20)   NOT NULL DEFAULT 'info',
  btn_text     VARCHAR(100)  DEFAULT NULL,
  link_url     VARCHAR(500)  DEFAULT NULL,
  cta_label    VARCHAR(100)  DEFAULT NULL,
  cta_url      VARCHAR(500)  DEFAULT NULL,
  image_url    VARCHAR(500)  DEFAULT NULL,
  page_target  VARCHAR(100)  DEFAULT NULL,
  page         VARCHAR(100)  DEFAULT NULL,
  active       TINYINT(1)    NOT NULL DEFAULT 1,
  starts_at    DATETIME      DEFAULT NULL,
  ends_at      DATETIME      DEFAULT NULL,
  position     SMALLINT      NOT NULL DEFAULT 0,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active_pos (active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 20. AUDIT LOG
-- ──────────────────────────────────────────────────────────────
CREATE TABLE audit_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED    DEFAULT NULL,
  action      VARCHAR(100)    NOT NULL,
  target_type VARCHAR(60)     DEFAULT NULL,
  target_id   INT UNSIGNED    DEFAULT NULL,
  old_value   JSON            DEFAULT NULL,
  new_value   JSON            DEFAULT NULL,
  ip_address  VARCHAR(45)     DEFAULT NULL,
  user_agent  VARCHAR(300)    DEFAULT NULL,
  created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user   (user_id),
  INDEX idx_action (action),
  INDEX idx_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 21. ANNOUNCEMENTS / POPUPS
-- ──────────────────────────────────────────────────────────────
CREATE TABLE announcements (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(300)  NOT NULL,
  body        TEXT          DEFAULT NULL,
  type        ENUM('info','success','warning','danger','promo') NOT NULL DEFAULT 'info',
  scope       ENUM('banner','popup','toast') NOT NULL DEFAULT 'banner',
  page_target VARCHAR(100)  DEFAULT NULL,
  btn_text    VARCHAR(100)  DEFAULT NULL,
  btn_url     VARCHAR(500)  DEFAULT NULL,
  active      TINYINT(1)    NOT NULL DEFAULT 1,
  dismissible TINYINT(1)    NOT NULL DEFAULT 1,
  starts_at   DATETIME      DEFAULT NULL,
  ends_at     DATETIME      DEFAULT NULL,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active (active, starts_at, ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 22. LIVE CHAT
-- ──────────────────────────────────────────────────────────────
CREATE TABLE support_conversations (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  visitor_name    VARCHAR(200)  DEFAULT NULL,
  visitor_email   VARCHAR(320)  DEFAULT NULL,
  user_id         INT UNSIGNED  DEFAULT NULL,
  status          ENUM('open','closed') NOT NULL DEFAULT 'open',
  unread_admin    SMALLINT      NOT NULL DEFAULT 0,
  unread_visitor  SMALLINT      NOT NULL DEFAULT 0,
  last_message_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status       (status, last_message_at DESC),
  INDEX idx_unread_admin (unread_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_messages (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT UNSIGNED  NOT NULL,
  sender          ENUM('visitor','admin') NOT NULL,
  message         TEXT          NOT NULL,
  created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (conversation_id) REFERENCES support_conversations(id) ON DELETE CASCADE,
  INDEX idx_conv (conversation_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- 23. SUPPORT CONTACTS (v3: department column included)
-- ──────────────────────────────────────────────────────────────
CREATE TABLE support_contacts (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label       VARCHAR(120)  NOT NULL,
  type        VARCHAR(40)   NOT NULL DEFAULT 'phone',
  department  VARCHAR(100)  DEFAULT NULL
    COMMENT 'complaint,account,legal,administration,audit,marketing,support',
  value       VARCHAR(300)  NOT NULL,
  description VARCHAR(300)  DEFAULT NULL,
  is_primary  TINYINT(1)    NOT NULL DEFAULT 0,
  active      TINYINT(1)    NOT NULL DEFAULT 1,
  position    SMALLINT      NOT NULL DEFAULT 0,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active (active, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ──────────────────────────────────────────────────────────────
-- v4 TABLES (20 extra tables)
-- ──────────────────────────────────────────────────────────────

CREATE TABLE activity_log (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type  VARCHAR(64)  NOT NULL,
  entity_id    BIGINT UNSIGNED NULL,
  action       VARCHAR(64)  NOT NULL,
  title        VARCHAR(255) NULL,
  meta         JSON         NULL,
  user_id      INT UNSIGNED NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_entity (entity_type, entity_id),
  KEY idx_user   (user_id),
  KEY idx_created(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_tokens (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  token_hash    CHAR(64)     NOT NULL UNIQUE,
  token_prefix  VARCHAR(16)  NOT NULL,
  client_id     INT UNSIGNED NULL,
  user_id       INT UNSIGNED NULL,
  scopes        VARCHAR(255) NULL,
  rate_limit    INT UNSIGNED DEFAULT 60,
  expires_at    DATETIME NULL,
  last_used_at  DATETIME NULL,
  last_ip       VARCHAR(64) NULL,
  revoked_at    DATETIME NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_prefix (token_prefix),
  KEY idx_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_request_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  token_id    INT UNSIGNED NULL,
  endpoint    VARCHAR(255) NOT NULL,
  method      VARCHAR(10)  NOT NULL,
  status_code SMALLINT     NOT NULL,
  ip          VARCHAR(64)  NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_token   (token_id),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE branches (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id  INT UNSIGNED NULL,
  code       VARCHAR(32)  NULL,
  name       VARCHAR(160) NOT NULL,
  address    VARCHAR(255) NULL,
  district   VARCHAR(80)  NULL,
  province   VARCHAR(80)  NULL,
  phone      VARCHAR(40)  NULL,
  manager    VARCHAR(120) NULL,
  is_head    TINYINT(1) DEFAULT 0,
  active     TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_client (client_id),
  KEY idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE client_licenses (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id           INT UNSIGNED NULL,
  license_key         VARCHAR(64) NOT NULL UNIQUE,
  max_users           INT UNSIGNED DEFAULT 0,
  activation_status   ENUM('pending','active','suspended','expired') DEFAULT 'pending',
  hardware_id         VARCHAR(128) NULL,
  issued_at           DATETIME NULL,
  expires_at          DATETIME NULL,
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_client (client_id),
  KEY idx_status (activation_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cron_runs (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job          VARCHAR(80)  NOT NULL,
  status       ENUM('ok','error','partial') DEFAULT 'ok',
  sent         INT UNSIGNED DEFAULT 0,
  failed       INT UNSIGNED DEFAULT 0,
  message      TEXT NULL,
  started_at   DATETIME NULL,
  finished_at  DATETIME NULL,
  KEY idx_job     (job),
  KEY idx_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE email_intake_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  message_id  VARCHAR(255) NULL,
  from_email  VARCHAR(190) NULL,
  subject     VARCHAR(255) NULL,
  ticket_id   INT UNSIGNED NULL,
  status      ENUM('received','linked','created','error','spam') DEFAULT 'received',
  error       TEXT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_msg    (message_id),
  KEY idx_ticket (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kb_categories (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  slug        VARCHAR(140) NOT NULL UNIQUE,
  description TEXT NULL,
  icon        VARCHAR(40) NULL,
  position    INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kb_articles (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id   INT UNSIGNED NULL,
  title         VARCHAR(200) NOT NULL,
  slug          VARCHAR(220) NOT NULL UNIQUE,
  excerpt       VARCHAR(500) NULL,
  body          MEDIUMTEXT NULL,
  tags          VARCHAR(255) NULL,
  author_id     INT UNSIGNED NULL,
  status        ENUM('draft','published','archived') DEFAULT 'draft',
  language      VARCHAR(8) DEFAULT 'en',
  published_at  DATETIME NULL,
  helpful_yes   INT UNSIGNED DEFAULT 0,
  helpful_no    INT UNSIGNED DEFAULT 0,
  views         INT UNSIGNED DEFAULT 0,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_cat    (category_id),
  KEY idx_status (status),
  KEY idx_lang   (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kb_feedback (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  article_id  INT UNSIGNED NOT NULL,
  helpful     TINYINT(1) NOT NULL,
  ip          VARCHAR(64) NULL,
  user_id     INT UNSIGNED NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_article (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE livechat_messages (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT UNSIGNED NOT NULL,
  sender          ENUM('user','agent','system') DEFAULT 'user',
  message         TEXT NOT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_conv (conversation_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  type       VARCHAR(48) NOT NULL,
  title      VARCHAR(200) NOT NULL,
  body       TEXT NULL,
  link_url   VARCHAR(500) NULL,
  icon       VARCHAR(40) NULL,
  seen_at    DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_seen (user_id, seen_at),
  KEY idx_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE onboarding_progress (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL UNIQUE,
  current_step  INT UNSIGNED DEFAULT 0,
  total_steps   INT UNSIGNED DEFAULT 5,
  data          JSON NULL,
  completed     TINYINT(1) DEFAULT 0,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE renewal_reminders (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  subscription_id INT UNSIGNED NOT NULL,
  user_id         INT UNSIGNED NULL,
  days_before     INT NOT NULL,
  channel         ENUM('email','sms','push','inapp') DEFAULT 'email',
  status          ENUM('queued','sent','failed','skipped') DEFAULT 'queued',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_sub    (subscription_id),
  KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE site_pages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(140) NOT NULL UNIQUE,
  title       VARCHAR(200) NOT NULL,
  body        MEDIUMTEXT NULL,
  meta_desc   VARCHAR(300) NULL,
  status      ENUM('draft','published','archived') DEFAULT 'draft',
  language    VARCHAR(8) DEFAULT 'en',
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sla_policies (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name               VARCHAR(120) NOT NULL,
  description        VARCHAR(255) NULL,
  priority           ENUM('urgent','high','normal','low') NOT NULL DEFAULT 'normal',
  response_minutes   INT UNSIGNED DEFAULT 60,
  resolution_minutes INT UNSIGNED DEFAULT 1440,
  active             TINYINT(1) DEFAULT 1,
  created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_components (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  status      ENUM('operational','degraded','partial_outage','major_outage','maintenance') DEFAULT 'operational',
  sort_order  INT DEFAULT 0,
  active      TINYINT(1) DEFAULT 1,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_incidents (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(200) NOT NULL,
  body         TEXT NULL,
  severity     ENUM('investigating','identified','monitoring','resolved') DEFAULT 'investigating',
  impact       ENUM('none','minor','major','critical') DEFAULT 'minor',
  component_id INT UNSIGNED NULL,
  started_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  resolved_at  DATETIME NULL,
  KEY idx_comp    (component_id),
  KEY idx_started (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_incident_updates (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  incident_id INT UNSIGNED NOT NULL,
  status      ENUM('investigating','identified','monitoring','resolved') DEFAULT 'investigating',
  message     TEXT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_incident (incident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_sessions (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  event       ENUM('login','logout','refresh','timeout','locked') DEFAULT 'login',
  ip          VARCHAR(64) NULL,
  user_agent  VARCHAR(255) NULL,
  device      VARCHAR(120) NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ══════════════════════════════════════════════════════════════
-- SEED DATA
-- ══════════════════════════════════════════════════════════════

-- ── ADMIN USER ────────────────────────────────────────────────
-- Default password: Admin@12345  (change immediately after login!)
INSERT INTO users (display_name, email, password_hash, role, email_verified, active) VALUES
('Ankur Admin', 'ankurinfotech8@gmail.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uQdTTrCoC',
 'admin', 1, 1)
ON DUPLICATE KEY UPDATE display_name = VALUES(display_name);

-- ── SITE SETTINGS ─────────────────────────────────────────────
INSERT INTO site_settings (setting_key, setting_val) VALUES
  ('site_name',             'Ankur Infotech Pvt. Ltd.'),
  ('site_tagline',          'Leading the Financial Digital Era in Nepal'),
  ('contact_email',         'ankurinfotech8@gmail.com'),
  ('contact_phone',         '+977-071-438585, 071-437612'),
  ('address',               'Butwal, Rupandehi, Nepal'),
  ('whatsapp_number',       '97771438585'),
  ('whatsapp_enabled',      '1'),
  ('whatsapp_message',      'Namaste! Ankur Infotech Pvt. Ltd. bare kura garnu cha.'),
  ('logo_url',              ''),
  ('favicon_url',           ''),
  ('social_links',          '{"facebook":"","twitter":"","linkedin":"","youtube":"","instagram":""}'),
  ('maintenance_mode',      '0'),
  ('google_analytics',      ''),
  ('meta_title',            'Ankur Infotech Pvt. Ltd. Pvt. Ltd. | Leading Digital Solutions for Finance'),
  ('meta_description',      'Ankur Infotech Pvt. Ltd. — CBS, KYM, Loan Automation, DMS ra Task Management software Nepal ko सहकारी ra financial institutions ko lagi. ISO 9001:2015 certified. Pokhara-based, nationwide support.'),
  ('brand_primary',         '#3a6636'),
  ('brand_secondary',       '#d9483b'),
  ('stat_1_value',          '10+'),
  ('stat_1_label',          'Years of Experience'),
  ('stat_2_value',          '650+'),
  ('stat_2_label',          'Happy Clients'),
  ('stat_3_value',          '7+'),
  ('stat_3_label',          'Major Products'),
  ('stat_4_value',          '100%'),
  ('stat_4_label',          'Client Retention Rate'),
  ('home_bento_eyebrow',    'What we build'),
  ('home_bento_title',      'Everything your cooperative needs. <span class="tg">One platform.</span>'),
  ('home_bento_subtitle',   'Purpose-built for Nepal''s सहकारी sector — not adapted from generic banking software. Every feature aligns with NRB guidelines and the Bikram Sambat calendar.'),
  ('home_in_action_title',    'See it in action'),
  ('home_in_action_subtitle', 'Explore the actual screens your team and members will use every day.'),
  ('chairman_name',    'Sujan Babu Adhikari'),
  ('chairman_title',   'Chairperson, Ankur Infotech Pvt. Ltd. Pvt. Ltd.'),
  ('chairman_photo',   'uploads/team/sujan1.jpg'),
  ('chairman_message', 'Ankur Infotech Pvt. Ltd. is committed to delivering modern, reliable and locally-supported IT solutions for businesses across Nepal. Based in Butwal, we bring quality software and on-the-ground support that our clients can depend on every day.'),
  ('ceo_name',         'Tanka Prasad Adhikari'),
  ('ceo_title',        'CEO & Director, Ankur Infotech Pvt. Ltd. Pvt. Ltd.'),
  ('ceo_photo',        'uploads/team/tanka1.jpg'),
  ('ceo_message',      'Since our founding, we have been focused on providing practical and efficient software solutions for our clients. Based in Butwal, Rupandehi, we listen first and deploy technology that fits the real needs of Nepali businesses.')
ON DUPLICATE KEY UPDATE setting_val = VALUES(setting_val);

-- ── PRODUCTS ──────────────────────────────────────────────────
-- ── PRODUCTS ──────────────────────────────────────────────────
INSERT IGNORE INTO products
  (name, slug, tagline, summary, icon, lucide_icon, icon_color, badge, category,
   features, highlights, show_on_home, home_position, home_card_wide, home_card_dark,
   position, active)
VALUES
('Core Banking System (CBS)', 'cbs',
 'NRB-aligned core banking for Nepal cooperatives',
 'Member KYC, savings & FD, loan lifecycle, share capital, general ledger and 100+ NRB-ready statutory reports — in Bikram Sambat, always.',
 '🏦','monitor','blue','Most Popular','Banking Software',
 '["Member & KYC","Savings & FD","Loan Lifecycle","Share Capital","General Ledger","NRB Reports","Multi-branch","BS Calendar","Audit Trail","100+ Reports"]',
 '["650+ cooperatives","NRB compliant","BS Calendar native"]',
 1,1,1,0, 1,1),

('KYM — Know Your Member', 'kym',
 'Comprehensive member profiles with risk & wealth details',
 'Maintains comprehensive member profiles with wealth, risk, and demographic details. Supports Excel and CBS data import. Tracks member status, transactions, and history efficiently.',
 '👤','users','indigo',NULL,'Member Management',
 '["Comprehensive member profiles","Wealth & risk details","Excel & CBS import","Member status tracking","Transaction history","Demographic analytics","KYC document storage","Audit-ready records"]',
 '["Full member lifecycle","Import from Excel/CBS"]',
 1,2,0,0, 2,1),

('Loan Management', 'loans',
 'Full loan lifecycle — application to closure',
 'Complete loan lifecycle automation: Application → Review → Approval → Disbursement. Online loan application by members with real-time status tracking. Automated document preparation and notifications.',
 '💰','credit-card','green',NULL,'Loan Software',
 '["Online loan application","Real-time status tracking","Automated approvals","Document auto-preparation","CRM-based follow-up","EMI scheduling","NRB-compliant reports","Member self-service"]',
 '["End-to-end automation","Member self-service"]',
 1,3,0,1, 3,1),

('Document Management System', 'dms',
 'Paperless office — every file searchable in seconds',
 'Aakash DMS le sahakari ko kagaji kamlaai purnrupama digital banaucha. Loan documents, KYC files ra reports ekai thauma — seconds ma khojnus.',
 '📄','folder-open','purple',NULL,'Document Management',
 '["Instant document search","KYC & loan file storage","Role-based access control","Version history","Audit trail","Report generation","Secure cloud backup","Mobile access"]',
 '["Seconds to find any file","Full audit trail"]',
 1,4,1,0, 4,1),

('Task & Office Management', 'tasks',
 'Prioritise, track and auto-generate official documents',
 'Task management to prioritize and track tasks for office staff. Auto-generation of committee letters and official documents. Integration with dashboard notifications for pending actions.',
 '✅','check-square','amber',NULL,'Office Management',
 '["Task prioritization","Staff task tracking","Auto-generate committee letters","Official document templates","Dashboard notifications","Pending action alerts","Deadline tracking","Approval workflows"]',
 '["Auto-generate letters","Dashboard alerts"]',
 1,5,0,0, 5,1),

('Mobile Banking App', 'mobile-banking',
 'Branded Android & iOS app in 3 weeks',
 'Branded Android & iOS app live in 3 weeks — member balance, transfers, QR payments, loan EMI, push alerts and biometric login. Available on Play Store & App Store.',
 '📱','smartphone','teal','New','Mobile App',
 '["Android & iOS","QR Payments","Fund Transfer","Loan EMI Payment","Push Alerts","Biometric Login","Balance Check","Mini Statement"]',
 '["3-week delivery","Play Store + App Store"]',
 1,6,0,1, 6,1);


-- ── SLA POLICIES ──────────────────────────────────────────────
INSERT IGNORE INTO sla_policies (name, description, priority, response_minutes, resolution_minutes)
VALUES
  ('Urgent', 'Critical / production-down issues', 'urgent', 15,  240),
  ('High',   'High-impact issues',                'high',   60,  480),
  ('Normal', 'Standard requests',                 'normal', 240, 1440),
  ('Low',    'Low priority / cosmetic',           'low',    480, 4320);

-- ── TESTIMONIALS ──────────────────────────────────────────────
-- Real client testimonials from Ankur Infotech Pvt. Ltd.'s old website
INSERT IGNORE INTO testimonials (author_name, author_role, author_org, quote, photo_url, rating, active, position) VALUES
('रनबहादुर थापा', 'व्यबस्थापक', 'जनउत्थान साकोस, रूपन्देही',
 'हामीले Aakash DMS प्रयोग गर्न थालेपछि सहकारीको दैनिक काम धेरै सहज भएको छ। पहिले दिन–अन्त्यको काम, फाइल खोज्ने, र कागजी प्रक्रिया निकै झन्झटिलो थियो। अब सबै काम एकै ठाउँमा मिल्छ र छिटो हुन्छ। कुनै समस्या परेमा सपोर्ट टिम तुरुन्त सहयोग गर्छ। Aakash DMS ले हाम्रो सम्पूर्ण प्रणालीलाई व्यवस्थित र सरल बनाइदिएको छ।',
 'uploads/clients/ran.jpeg', 5, 1, 1),
('नवराज दाहाल', 'प्रमुख कार्यकारी अधिकृत', 'जनसचेतन साकोस, दोलखा',
 'Aakash DMS ले हाम्रो सहकारीको कागजी कामलाई पूर्णरूपमा डिजिटल बनाइदियो। पहिले फाइल खोज्न धेरै समय लाग्थ्यो, अब केही सेकेन्डमै भेटिन्छ। रिपोर्ट, फाइल र loan विवरण सबै एकै प्लेटफर्ममा व्यवस्थापन गर्न सकिन्छ, जसले कामको गति उल्लेखनीय रूपमा बढाएको छ।',
 'uploads/clients/nabaraj.jpeg', 5, 1, 2),
('केबी लामा', 'प्रमुख कार्यकारी अधिकृत', 'विन्दवासिनी साकोस, काभ्रे',
 'CBS डेटा मिलाउन पहिले धेरै समस्या पर्थ्यो, तर Aakash DMS ले त्यो झन्झट पूर्ण रूपमा हटाइदियो। अब रिपोर्ट तयार गर्न, फाइल चेक गर्न र विवरण खोज्न केही मिनेट मात्र लाग्छ। सिस्टमको स्थिरता र सहज प्रयोगका कारण टिमको उत्पादनशीलता नै फरक भएको छ।',
 'uploads/clients/kblama.jpeg', 5, 1, 3),
('बिनोद पराजुली', 'प्रमुख कार्यकारी अधिकृत', 'कोलियो देवदह साकोस, बर्दघाट',
 'Aakash DMS बिना अहिले सहकारी चलाउन गाह्रो लाग्छ। सबै फाइल, loan documents र KYM एकै ठाउँमा सुरक्षित छन्। प्रणाली छिटो, भरपर्दो र प्रयोगमैत्री भएकोले टिमलाई निर्णय लिन, फाइल हेर्न र रिपोर्ट तयार गर्न निकै सजिलो भएको छ।',
 'uploads/clients/binod.jpeg', 5, 1, 4),
('गोविन्दराज आचार्य', 'प्रमुख कार्यकारी अधिकृत', 'सार्दीखोला साकोस, भुजरुङखोला',
 'Loan follow-up, reminder र document expiry alert ले हाम्रो सहकारीमा ठूलो सुधार ल्यायो। सदस्य व्यवस्थापन सजिलो भयो र कामको गति पनि बढ्यो। सपोर्ट टिम निरन्तर उपलब्ध हुन्छ, जसले कुनै पनि समस्या तुरुन्त समाधान गरिदिन्छ।',
 'uploads/clients/govinda.jpeg', 5, 1, 5),
('मेघनाथ लामीछाने', 'प्रमुख कार्यकारी', 'यूनिक साकोस, रत्ननगर',
 'Loan follow-up, reminder र document expiry alert ले हाम्रो सहकारीमा ठूलो सुधार ल्यायो। सदस्य व्यवस्थापन सजिलो भयो र कामको गति पनि बढ्यो। सपोर्ट टिम निरन्तर उपलब्ध हुन्छ, जसले कुनै पनि समस्या तुरुन्त समाधान गरिदिन्छ।',
 'uploads/clients/meghnath.jpeg', 5, 1, 6);

-- ── PARTNERS / CLIENTS (SACCOS logos) ────────────────────────
INSERT IGNORE INTO partners (name, logo_url, type, active, position) VALUES
  ('SACCOS Partner 1', 'uploads/clients/saccoslogo1.png', 'client', 1, 1),
  ('SACCOS Partner 2', 'uploads/clients/saccoslogo2.png', 'client', 1, 2),
  ('SACCOS Partner 3', 'uploads/clients/saccoslogo3.png', 'client', 1, 3),
  ('SACCOS Partner 4', 'uploads/clients/saccoslogo4.png', 'client', 1, 4),
  ('SACCOS Partner 5', 'uploads/clients/saccoslogo5.png', 'client', 1, 5),
  ('SACCOS Partner 6', 'uploads/clients/saccoslogo6.png', 'client', 1, 6),
  ('SACCOS Partner 7', 'uploads/clients/saccoslogo7.png', 'client', 1, 7);

-- ── FAQs ──────────────────────────────────────────────────────
-- ── FAQs ──────────────────────────────────────────────────────
INSERT IGNORE INTO faqs (category, question, answer, active, position) VALUES
('Products','Is your CBS NRB-compliant?',
 'Yes. Our CBS includes 100+ pre-built NRB report templates and is updated with every regulatory change at no extra cost.',1,1),
('Products','Does it support Nepali calendar (Bikram Sambat)?',
 'Yes. Every module — savings, loans, reports, payslips, receipts — is fully Bikram Sambat native. All dates print in BS format.',1,2),
('Products','What is KYM and how is it different from KYC?',
 'KYM (Know Your Member) goes beyond KYC — it maintains comprehensive member profiles including wealth details, risk assessment, demographic data, transaction history and document storage. Supports import from Excel and existing CBS.',1,3),
('Products','Can members apply for loans online?',
 'Yes. The Loan Management module includes a member portal where members can apply for loans, track approval status in real time, and receive automated notifications at each stage.',1,4),
('Products','How does DMS help with cooperative paperwork?',
 'Aakash DMS digitises all cooperative documents — KYC files, loan papers, meeting minutes, committee letters. Every document is indexed and searchable in seconds. Role-based access ensures right people see right files.',1,5),
('Implementation','How long does it take to go live?',
 'From signed contract to live system — data migration included — we target 14 days. Our project manager handles full migration and staff training, and stays on-call for 30 days post-launch.',1,6),
('Implementation','Can you migrate data from our existing system?',
 'Yes. We have migrated data from Excel, FoxPro, Tally, and various legacy CBS systems. Our migration team cleans, validates and imports your data with zero manual re-entry required.',1,7),
('Pricing','Are there any hidden fees?',
 'No hidden fees. Setup, data migration, staff training and the first 30-day support period are all included in the quoted price.',1,8),
('Pricing','What happens if NRB changes reporting requirements?',
 'Regulatory updates are included at no extra charge for all active plan holders. Our team monitors NRB circulars and pushes updates within 7 business days.',1,9),
('Support','What kind of support is available after go-live?',
 'Every plan includes email and ticket support. Growth and Enterprise clients get priority < 2 hr response, direct phone support, and on-site visits across all 7 provinces of Nepal.',1,10);


-- ── PRICING PLANS ─────────────────────────────────────────────
-- ── PRICING PLANS ─────────────────────────────────────────────
INSERT IGNORE INTO pricing_plans (name, tag, price_label, period, cta_label, is_popular, features, active, position) VALUES
('Starter', 'Single-branch cooperatives', 'NPR 4,999', '/ month', 'Get started', 0,
 '["Up to 500 members","Core Banking (CBS)","KYM — basic member profiles","Web portal + notice board","Email & ticket support","NRB reports included"]',
 1,1),
('Growth', 'Multi-branch — most popular', 'NPR 12,999', '/ month', 'Book a demo', 1,
 '["Up to 5,000 members","CBS + KYM + Loan Management","Mobile Banking App","DMS + role-based access","Task & office management","Priority < 2 hr support","On-site training included"]',
 1,2),
('Enterprise', 'Large & regulated cooperatives', 'Custom', '', 'Talk to us', 0,
 '["Unlimited members & branches","All modules included","Dedicated success manager","Custom NRB report builder","24×7 critical SLA","On-site visits included","API access"]',
 1,3);


-- ── SERVICES ──────────────────────────────────────────────────
-- ── SERVICES ──────────────────────────────────────────────────
INSERT IGNORE INTO services (title, slug, summary, features, icon, icon_color, active, position) VALUES
('Core Banking System (CBS)', 'core-banking',
 'NRB-aligned core banking with member management, savings, loans, share capital, general ledger and over 100 statutory reports. Single-branch to multi-province ready.',
 'Members & KYC,Savings & Deposits,Loan Lifecycle,General Ledger,NRB Reports,Audit Trail',
 'monitor', 'blue', 1, 1),
('Mobile Banking App', 'mobile-banking',
 'Branded Android & iOS apps for cooperative members. Balance inquiry, mini-statement, fund transfer, QR payments, loan EMI, utility bills and push notifications.',
 'Android + iOS,QR Payments,Biometric Login,Push Alerts,Utility Bills,Transaction SMS',
 'smartphone', 'teal', 1, 2),
('Document Management (DMS)', 'dms',
 'Go fully paperless. Upload, index, version and retrieve KYC files, loan documents, board minutes and policies with granular role-based access and a full audit trail.',
 'OCR Indexing,KYC Management,Version History,Role-based Access,E-signature Ready,Audit Logs',
 'file-text', 'purple', 1, 3),
('HR & Payroll Software', 'hr-payroll',
 'End-to-end HR from onboarding to exit — biometric attendance, leave, payroll, TDS, SSF, CIT and a self-service ESS portal for staff.',
 'Attendance Tracking,Leave Management,Payroll & TDS,SSF & CIT,ESS Portal,Payslip Generation',
 'users', 'amber', 1, 4),
('Website Development', 'coop-website',
 'Professional cooperative websites with notice board, downloads, loan calculator, online forms, multilingual content and a self-service CMS — delivered in 2 weeks.',
 'CMS-powered,Multilingual,Notice Board,Online Forms,SEO Optimised,Fast Hosting',
 'globe', 'green', 1, 5),
('Support & Ticket Desk', 'support',
 '24×7 multi-channel support via your client portal. Every issue is logged, tracked and resolved with < 2 hr response SLA. On-site visits available across Nepal.',
 'Client Portal,< 2 hr SLA,On-site Visits,Priority Escalation,24×7 Coverage,Root Cause Reports',
 'headphones', 'rose', 1, 6);


-- ── TEAM MEMBERS ──────────────────────────────────────────────
INSERT IGNORE INTO team_members (name, role, bio, photo_url, is_leadership, active, position) VALUES
-- Board of Directors
('Sujan Babu Adhikari',  'Chairperson',
 'Founder and Chairperson of Ankur Infotech Pvt. Ltd. Pvt. Ltd. Visionary leader driving digital transformation in Nepal''s cooperative and financial sector.',
 'uploads/team/sujan1.jpg', 1, 1, 1),
('Tanka Prasad Adhikari','Director & CEO',
 'Co-founder and CEO of Ankur Infotech Pvt. Ltd.. 10+ years experience in cooperative software and IT solutions. Leads product development and overall company strategy.',
 'uploads/team/tanka1.jpg', 1, 1, 2),
('Subash Acharya',       'Director',
 'Board Director of Ankur Infotech Pvt. Ltd. Pvt. Ltd. Oversees strategic partnerships and institutional growth.',
 'uploads/team/subash.jpg', 1, 1, 3),
-- Management Team
('Roshni Bhandari',      'Manager',
 'Operations Manager responsible for day-to-day client coordination and project delivery.',
 'uploads/team/roshni1.jpg', 1, 1, 4),
('Indira Acharya',       'Head of Support',
 'Leads the client support team, ensuring fast resolution of every ticket and on-site visits as needed.',
 'uploads/team/indira1.jpg', 0, 1, 5),
('Sushila Poudel',       'Head of Operation',
 'Oversees operational workflows, staff coordination and process improvement across all departments.',
 'uploads/team/sushila1.jpeg', 0, 1, 6);

-- ── SUPPORT CONTACTS ──────────────────────────────────────────
INSERT IGNORE INTO support_contacts (label, type, value, description, is_primary, active, position) VALUES
('Main Office',      'phone',   '+977-071-438585',          'Butwal Office — Sales',           1, 1, 1),
('Support Line',     'phone',   '071-437612',               'Butwal Office — Support',         0, 1, 2),
('Email',            'email',   'ankurinfotech8@gmail.com', 'General enquiries',               1, 1, 3),
('Office Address',   'address', 'Butwal, Rupandehi, Nepal', 'Main office',                     0, 1, 4);

-- ══════════════════════════════════════════════════════════════
SET FOREIGN_KEY_CHECKS = 1;
-- ══════════════════════════════════════════════════════════════
--  Import complete!
--  Admin panel : /admin/
--  Login       : ankurinfotech8@gmail.com / Admin@12345
--  ⚠ Password turai change garnus!
-- ══════════════════════════════════════════════════════════════

-- ══════════════════════════════════════════════════════════════
-- MISSING TABLES — added to complete the schema
-- ══════════════════════════════════════════════════════════════

-- ── Email verification tokens ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED    NOT NULL,
  `token`      VARCHAR(128)    NOT NULL,
  `expires_at` DATETIME        NOT NULL,
  `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user` (`user_id`),
  UNIQUE KEY `uq_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Password reset tokens ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED    NOT NULL,
  `token`      VARCHAR(128)    NOT NULL,
  `expires_at` DATETIME        NOT NULL,
  `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user` (`user_id`),
  UNIQUE KEY `uq_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Login / brute-force attempt tracking ─────────────────────
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier`     VARCHAR(255) NOT NULL,
  `attempts`       SMALLINT     NOT NULL DEFAULT 1,
  `last_attempt_at`DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `locked_until`   DATETIME     NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_identifier` (`identifier`),
  KEY `idx_locked` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── API rate limiting ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `api_rate_limits` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_hash`      CHAR(64)     NOT NULL,
  `hits`         INT UNSIGNED NOT NULL DEFAULT 1,
  `window_start` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ip` (`ip_hash`),
  KEY `idx_window` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Client subscriptions ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `client_subscriptions` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED  NOT NULL,
  `product_id`      INT UNSIGNED  NULL DEFAULT NULL,
  `product_name`    VARCHAR(200)  NOT NULL,
  `plan_name`       VARCHAR(100)  NOT NULL,
  `license_key`     VARCHAR(100)  NULL DEFAULT NULL,
  `deployment_type` VARCHAR(50)   NULL DEFAULT NULL,
  `branches`        SMALLINT      NOT NULL DEFAULT 1,
  `members_limit`   INT           NULL DEFAULT NULL,
  `amount`          DECIMAL(12,2) NULL DEFAULT NULL,
  `billing_cycle`   VARCHAR(20)   NULL DEFAULT 'monthly',
  `status`          VARCHAR(20)   NOT NULL DEFAULT 'active',
  `starts_at`       DATE          NULL DEFAULT NULL,
  `expires_at`      DATE          NULL DEFAULT NULL,
  `next_renewal`    DATE          NULL DEFAULT NULL,
  `notes`           TEXT          NULL DEFAULT NULL,
  `created_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_renewal` (`next_renewal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── User services (portal sidebar count) ─────────────────────
CREATE TABLE IF NOT EXISTS `user_services` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `name`       VARCHAR(200) NOT NULL,
  `status`     VARCHAR(30)  NOT NULL DEFAULT 'active',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── CRM Leads ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `crm_leads` (
  `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(200)  NOT NULL,
  `org_name`          VARCHAR(200)  NULL DEFAULT NULL,
  `email`             VARCHAR(200)  NULL DEFAULT NULL,
  `phone`             VARCHAR(30)   NULL DEFAULT NULL,
  `district`          VARCHAR(100)  NULL DEFAULT NULL,
  `source`            VARCHAR(100)  NULL DEFAULT NULL,
  `source_ref_id`     INT UNSIGNED  NULL DEFAULT NULL,
  `products_interest` VARCHAR(500)  NULL DEFAULT NULL,
  `stage`             VARCHAR(50)   NOT NULL DEFAULT 'prospect',
  `stage_notes`       TEXT          NULL DEFAULT NULL,
  `deal_value`        DECIMAL(14,2) NULL DEFAULT NULL,
  `next_followup`     DATE          NULL DEFAULT NULL,
  `last_contact_at`   DATETIME      NULL DEFAULT NULL,
  `assigned_to`       INT UNSIGNED  NULL DEFAULT NULL,
  `won_at`            DATETIME      NULL DEFAULT NULL,
  `lost_reason`       TEXT          NULL DEFAULT NULL,
  `created_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stage` (`stage`),
  KEY `idx_followup` (`next_followup`),
  KEY `idx_assigned` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── CRM Follow-ups ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `crm_followups` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id`       INT UNSIGNED NOT NULL,
  `user_id`       INT UNSIGNED NULL DEFAULT NULL,
  `type`          VARCHAR(50)  NOT NULL DEFAULT 'call',
  `notes`         TEXT         NULL DEFAULT NULL,
  `outcome`       VARCHAR(100) NULL DEFAULT NULL,
  `next_followup` DATE         NULL DEFAULT NULL,
  `followup_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lead` (`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── CRM Proposals ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `crm_proposals` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `lead_id`     INT UNSIGNED  NOT NULL,
  `user_id`     INT UNSIGNED  NULL DEFAULT NULL,
  `title`       VARCHAR(300)  NOT NULL,
  `products`    VARCHAR(500)  NULL DEFAULT NULL,
  `amount`      DECIMAL(14,2) NULL DEFAULT NULL,
  `valid_until` DATE          NULL DEFAULT NULL,
  `status`      VARCHAR(30)   NOT NULL DEFAULT 'draft',
  `notes`       TEXT          NULL DEFAULT NULL,
  `file_url`    VARCHAR(500)  NULL DEFAULT NULL,
  `sent_at`     DATETIME      NULL DEFAULT NULL,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lead` (`lead_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
