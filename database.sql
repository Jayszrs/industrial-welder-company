-- =============================================================================
-- Yamato Welding Industries — Company Profile Website
-- Database: industrial_company (utf8mb4, full Japanese character support)
-- Import this file via phpMyAdmin > Import, or:
--   mysql -u root -p < database.sql
-- =============================================================================

CREATE DATABASE IF NOT EXISTS industrial_company
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE industrial_company;

-- -----------------------------------------------------------------------------
-- admin_users
-- -----------------------------------------------------------------------------
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo login: username = admin / password = admin123
-- (hash below is a genuine bcrypt hash, fully compatible with PHP's password_verify())
INSERT INTO admin_users (username, password, full_name) VALUES
('admin', '$2b$10$iu2S0mqU1Vq60lI9Do1t7.pqKFZxfz8Cva62UWhGuIJKhXS2hdZne', 'Site Administrator');

-- -----------------------------------------------------------------------------
-- site_settings — key/value store for global company info
-- -----------------------------------------------------------------------------
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('company_name_ja', '大和溶接工業株式会社'),
('company_name_en', 'Yamato Welding Industries Co., Ltd.'),
('company_tagline_ja', '確かな技術で、ものづくりの未来を支える。'),
('company_tagline_en', 'Engineering the Future of Manufacturing.'),
('representative_ja', '代表取締役社長　山田 一郎'),
('representative_en', 'Ichiro Yamada, President & CEO'),
('established', '2001年4月 (April 2001)'),
('business_activities_ja', '産業用溶接機・溶接設備の製造販売、金属加工、産業機械のメンテナンス、エンジニアリングソリューションの提供'),
('business_activities_en', 'Manufacture and sale of industrial welding machines and equipment, metal fabrication, industrial machinery maintenance, and engineering solutions'),
('address_ja', '〒230-0000 神奈川県横浜市鶴見区工業町1-2-3'),
('address_en', '1-2-3 Kogyo-cho, Tsurumi-ku, Yokohama, Kanagawa 230-0000, Japan'),
('phone', '+81-45-000-1234'),
('email', 'info@yamato-welding.example.com'),
('website', 'https://www.yamato-welding.example.com'),
('linkedin_url', 'https://www.linkedin.com/'),
('instagram_url', 'https://www.instagram.com/'),
('tiktok_url', 'https://www.tiktok.com/'),
('sample_data_notice', 'DEMO DATA — replace via the admin panel before going live'),

-- Homepage "our strength" section intro (used together with the stats table)
('meta_description_ja', '大和溶接工業は、溶接技術と産業機械ソリューションで製造業の未来を支えます。'),
('meta_description_en', 'Yamato Welding Industries supports the future of manufacturing with advanced welding technology and industrial machinery solutions.');

-- -----------------------------------------------------------------------------
-- homepage_content — editable text blocks for homepage sections
-- -----------------------------------------------------------------------------
CREATE TABLE homepage_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) NOT NULL UNIQUE,
    title_ja VARCHAR(255),
    title_en VARCHAR(255),
    subtitle_ja TEXT,
    subtitle_en TEXT,
    content_ja TEXT,
    content_en TEXT,
    image VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO homepage_content (section_key, title_ja, title_en, subtitle_ja, subtitle_en, content_ja, content_en, image) VALUES
('hero',
 '確かな技術で、\nものづくりの未来を支える。',
 'Engineering the Future\nof Manufacturing.',
 NULL, NULL,
 '高度な溶接技術と産業機械ソリューションで、製造現場の品質と生産性向上に貢献します。',
 'Advanced welding technology and industrial machinery solutions for modern manufacturing.',
 'hero-bg.svg'),

('about',
 '私たちについて',
 'About Us',
 NULL, NULL,
 '大和溶接工業は、2001年の創業以来、産業用溶接機と金属加工ソリューションを通じて日本のものづくりを支えてきました。TIG・MIG・レーザー溶接など幅広い技術に対応し、自動車、建設、エネルギーなど多様な業界のお客様に高精度な製品と信頼性の高いサービスを提供しています。少数精鋭の技術者が、設計から製造、アフターサービスまで一貫して対応することで、お客様一社ごとの課題に寄り添ったソリューションを実現します。',
 'Since our founding in 2001, Yamato Welding Industries has supported Japanese manufacturing through industrial welding machines and metal fabrication solutions. We work across TIG, MIG, and laser welding technologies, serving clients in automotive, construction, and energy with precision products and dependable service. Our focused team of engineers manages every stage — from design to production and after-sales support — to deliver solutions tailored to each client\'s challenges.',
 'about.svg'),

('strength',
 '品質と信頼への取り組み',
 'Commitment to Quality & Reliability',
 NULL, NULL,
 NULL, NULL, NULL),

('cta',
 'お気軽にお問い合わせください',
 'Get in Touch With Us',
 '製品・技術・導入に関するご相談は、専門スタッフが丁寧に対応いたします。',
 'Our specialists are ready to help with any questions about our products, technology, or implementation.',
 NULL, NULL, NULL);

-- -----------------------------------------------------------------------------
-- stats — homepage strength numbers (editable, demo values)
-- -----------------------------------------------------------------------------
-- Unlimited homepage slideshow images managed from admin.
CREATE TABLE hero_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_hero_slides_display (status, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number_value VARCHAR(20) NOT NULL,
    label_ja VARCHAR(100) NOT NULL,
    label_en VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO stats (number_value, label_ja, label_en, sort_order) VALUES
('25+', '創業からの年数', 'Years of Experience', 1),
('100+', '取引企業数', 'Industrial Clients', 2),
('50+', '導入実績', 'Machine Solutions Delivered', 3);

-- -----------------------------------------------------------------------------
-- industries — industries served
-- -----------------------------------------------------------------------------
CREATE TABLE industries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ja VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    icon_label VARCHAR(10) DEFAULT NULL,
    sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO industries (name_ja, name_en, icon_label, sort_order) VALUES
('自動車', 'Automotive', '01', 1),
('製造業', 'Manufacturing', '02', 2),
('建設', 'Construction', '03', 3),
('インフラ', 'Infrastructure', '04', 4),
('エネルギー', 'Energy', '05', 5),
('金属加工', 'Metal Fabrication', '06', 6),
('産業保全', 'Industrial Maintenance', '07', 7),
('造船', 'Shipbuilding', '08', 8);

-- -----------------------------------------------------------------------------
-- services — core services / technology overview cards
-- -----------------------------------------------------------------------------
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sort_order INT DEFAULT 0,
    title_ja VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    description_ja TEXT,
    description_en TEXT,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO services (sort_order, title_ja, title_en, description_ja, description_en, image) VALUES
(1, '溶接機・溶接設備', 'Welding Machines', 'TIG・MIG・MAGなど多様な方式に対応した産業用溶接機を設計・製造しています。', 'We design and manufacture industrial welding machines supporting TIG, MIG, MAG, and other processes.', 'svc-welding-machines.svg'),
(2, '産業機械', 'Industrial Machinery', 'プレス機・切断機など、製造現場に不可欠な産業機械を幅広く取り扱います。', 'We supply a wide range of industrial machinery essential to modern production floors, including presses and cutting machines.', 'svc-industrial-machinery.svg'),
(3, '溶接技術', 'Welding Technology', '自動車から重工業まで、あらゆる素材・厚みに対応する高精度な溶接技術を提供します。', 'From automotive to heavy industry, we provide high-precision welding technology for every material and thickness.', 'svc-welding-technology.svg'),
(4, '金属加工', 'Metal Fabrication', '切断・曲げ・溶接を一貫して行う金属加工サービスで、複雑な形状にも対応します。', 'Our integrated cutting, bending, and welding services handle even complex fabrication requirements.', 'svc-metal-fabrication.svg'),
(5, '保守・メンテナンス', 'Maintenance', '定期点検から緊急修理まで、設備の稼働率を最大化する保守サービスを提供します。', 'From scheduled inspections to emergency repairs, our maintenance service keeps your equipment running at peak uptime.', 'svc-maintenance.svg'),
(6, '部品・設備', 'Parts & Equipment', '純正部品の供給から周辺機器の選定まで、設備導入をトータルでサポートします。', 'We support your equipment needs end-to-end, from genuine replacement parts to peripheral equipment selection.', 'svc-parts-equipment.svg');

-- -----------------------------------------------------------------------------
-- technologies — welding technology detail list
-- -----------------------------------------------------------------------------
CREATE TABLE technologies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sort_order INT DEFAULT 0,
    name_ja VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    description_ja TEXT,
    description_en TEXT,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO technologies (sort_order, name_ja, name_en, description_ja, description_en, image) VALUES
(1, 'TIG溶接', 'TIG Welding', 'タングステン電極を用いた高精度溶接。ステンレスやアルミなど薄板の美しい仕上がりに最適です。', 'High-precision welding using a tungsten electrode. Ideal for stainless steel and aluminum thin sheets with a clean finish.', 'tech-tig.svg'),
(2, 'MIG溶接', 'MIG Welding', '高い溶着速度で厚板から薄板まで幅広く対応し、生産性の高い溶接を実現します。', 'Delivers high deposition rates across thick and thin plates for productive, efficient welding.', 'tech-mig.svg'),
(3, 'MAG溶接', 'MAG Welding', '炭酸ガスを用いた溶接方式で、鉄鋼構造物の溶接に高いコストパフォーマンスを発揮します。', 'A CO2 shielding gas process offering excellent cost performance for steel structural welding.', 'tech-mag.svg'),
(4, 'レーザー溶接', 'Laser Welding', '高エネルギー密度のレーザーによる非接触溶接で、狭小部や精密部品にも対応します。', 'Non-contact welding with high energy density, suited to precision components and hard-to-reach joints.', 'tech-laser.svg'),
(5, 'スポット溶接', 'Spot Welding', '自動車ボディなど、重ね合わせた金属板を短時間で接合する量産向け溶接方式です。', 'A high-speed joining process for overlapping sheet metal, widely used in automotive body assembly.', 'tech-spot.svg'),
(6, 'ロボット溶接', 'Robotic Welding', '産業用ロボットによる自動溶接で、安定した品質と高い生産性を両立します。', 'Automated welding using industrial robots, combining consistent quality with high throughput.', 'tech-robotic.svg'),
(7, 'アーク溶接', 'Arc Welding', '汎用性の高い溶接方式で、建設現場から工場内加工まで幅広く活用されています。', 'A versatile welding method used everywhere from construction sites to in-house fabrication.', 'tech-arc.svg');

-- -----------------------------------------------------------------------------
-- product_categories
-- -----------------------------------------------------------------------------
CREATE TABLE product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ja VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO product_categories (id, name_ja, name_en, slug) VALUES
(1, '溶接機', 'Welding Machine', 'welding-machine'),
(2, 'TIG溶接機', 'TIG Welding', 'tig-welding'),
(3, 'MIG/MAG溶接機', 'MIG/MAG Welding', 'mig-mag-welding'),
(4, 'レーザー溶接機', 'Laser Welding', 'laser-welding'),
(5, '溶接ロボット', 'Welding Robot', 'welding-robot'),
(6, '切断機', 'Cutting Machine', 'cutting-machine'),
(7, '産業機械', 'Industrial Machinery', 'industrial-machinery');

-- -----------------------------------------------------------------------------
-- products
-- -----------------------------------------------------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    name_ja VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    model VARCHAR(100),
    manufacturer VARCHAR(100),
    short_description_ja TEXT,
    short_description_en TEXT,
    description_ja TEXT,
    description_en TEXT,
    features_ja TEXT,
    features_en TEXT,
    application_ja TEXT,
    application_en TEXT,
    spec_power VARCHAR(100),
    spec_output VARCHAR(100),
    spec_current_range VARCHAR(100),
    spec_dimensions VARCHAR(100),
    spec_weight VARCHAR(100),
    image VARCHAR(255),
    gallery TEXT,
    slug VARCHAR(255) UNIQUE,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (category_id, name_ja, name_en, model, manufacturer, short_description_ja, short_description_en, description_ja, description_en, features_ja, features_en, application_ja, application_en, spec_power, spec_output, spec_current_range, spec_dimensions, spec_weight, image, slug) VALUES
(2, 'TIG-200 プロ', 'TIG-200 Pro', 'TIG-200', 'Yamato', 'ステンレス・アルミの薄板溶接に対応する高精度TIG溶接機。', 'A high-precision TIG welder for stainless steel and aluminum thin-sheet welding.',
 'TIG-200 Proは、安定したアーク特性とパルス制御機能を備えた高精度TIG溶接機です。薄板の美しい仕上がりを求める精密加工現場に最適です。',
 'The TIG-200 Pro is a high-precision TIG welding machine with stable arc characteristics and pulse control. It is ideal for precision fabrication where a clean finish on thin sheet metal is required.',
 '軽量ボディで持ち運びが容易\nパルス制御で入熱を最小化\nデジタル電流表示パネル',
 'Lightweight body for easy portability\nPulse control minimizes heat input\nDigital current display panel',
 'ステンレス製厨房機器、アルミフレーム、精密板金加工', 'Stainless kitchen equipment, aluminum frames, precision sheet-metal work',
 '単相200V', '5-200A', '5A - 200A', '420 x 200 x 320 mm', '18 kg', 'prod-tig-200.svg', 'tig-200-pro'),

(3, 'MIG-350 インダストリアル', 'MIG-350 Industrial', 'MIG-350', 'Yamato', '厚板構造物向けの高出力MIG/MAG溶接機。', 'A high-output MIG/MAG welder built for heavy structural steel work.',
 'MIG-350 Industrialは、建設・造船分野の厚板溶接に対応する高出力インバーター式溶接機です。安定したワイヤ送給機構により、長時間の連続作業でも高品質な溶接を実現します。',
 'The MIG-350 Industrial is a high-output inverter welder built for heavy-plate welding in construction and shipbuilding. Its stable wire feed mechanism maintains weld quality through long production runs.',
 '350A出力で厚板にも対応\n高耐久ワイヤ送給ユニット\n遠隔操作パネル対応',
 'Rated up to 350A for heavy plate\nHeavy-duty wire feed unit\nCompatible with remote control panel',
 '建設用鉄骨、造船構造物、産業プラント配管', 'Structural steel for construction, shipbuilding structures, industrial plant piping',
 '三相200V', '350A', '30A - 350A', '650 x 320 x 520 mm', '62 kg', 'prod-mig-350.svg', 'mig-350-industrial'),

(4, 'レーザーウェルド-500', 'LaserWeld-500', 'LW-500', 'Yamato', '狭小部・精密部品向けファイバーレーザー溶接システム。', 'A fiber laser welding system for tight spaces and precision components.',
 'LaserWeld-500は、ファイバーレーザーによる非接触溶接を実現するシステムです。熱影響部が小さく、精密部品や薄板の高速溶接に適しています。',
 'The LaserWeld-500 delivers non-contact fiber laser welding with a minimal heat-affected zone, ideal for high-speed welding of precision components and thin materials.',
 '狭小スペースでも高精度施工\n熱変形を大幅に抑制\n自動焦点調整機能',
 'High precision even in tight spaces\nSignificantly reduces thermal distortion\nAutomatic focus adjustment',
 '電子部品筐体、精密医療機器、薄板金属加工', 'Electronics enclosures, precision medical devices, thin sheet-metal fabrication',
 '三相200V', '500W', 'N/A', '900 x 700 x 1400 mm', '210 kg', 'prod-laser-500.svg', 'laserweld-500'),

(5, 'ウェルドボット ARC-6', 'WeldBot ARC-6', 'ARC-6', 'Yamato', '6軸産業用ロボットによる自動アーク溶接システム。', 'A 6-axis industrial robot arm for automated arc welding.',
 'WeldBot ARC-6は、6軸自由度を持つ産業用ロボットアームに溶接トーチを搭載した自動溶接システムです。量産ラインでの品質安定化と生産性向上に貢献します。',
 'WeldBot ARC-6 pairs a 6-axis industrial robot arm with an integrated welding torch for fully automated arc welding, stabilizing weld quality and boosting throughput on production lines.',
 '繰り返し精度±0.05mm\nオフラインティーチング対応\n24時間連続稼働に対応',
 'Repeatability of ±0.05mm\nSupports offline teaching\nDesigned for 24-hour continuous operation',
 '自動車部品量産ライン、家電製品フレーム、産業機械筐体', 'Automotive parts production lines, appliance frames, industrial machine housings',
 '三相200V', '6軸 / 6-Axis', 'N/A', '1200 x 900 x 2100 mm', '650 kg', 'prod-robot-arm.svg', 'weldbot-arc-6'),

(6, 'プラズマカット-120', 'PlasmaCut-120', 'PC-120', 'Yamato', '厚板鋼板を高速切断するプラズマ切断機。', 'A plasma cutting machine for fast, clean cuts through heavy steel plate.',
 'PlasmaCut-120は、最大32mm厚の鋼板を高速かつ高精度に切断できる産業用プラズマ切断機です。安定したアーク維持機能で、切断面の品質を高く保ちます。',
 'PlasmaCut-120 is an industrial plasma cutter capable of fast, precise cuts through steel plate up to 32mm thick, with stable arc maintenance for a clean cut edge.',
 '最大切断厚32mm\n高速切断ヘッド\n消耗品交換が容易な設計',
 'Cuts up to 32mm thickness\nHigh-speed cutting head\nDesigned for easy consumable replacement',
 '構造用鋼板切断、船舶部材加工、重機部品製造', 'Structural steel plate cutting, marine component fabrication, heavy equipment parts',
 '三相200V', '120A', '20A - 120A', '520 x 300 x 400 mm', '34 kg', 'prod-cutting-plasma.svg', 'plasmacut-120'),

(7, '油圧プレス HP-80', 'Hydraulic Press HP-80', 'HP-80', 'Yamato', '金属成形・打抜き加工向け80トン油圧プレス機。', 'An 80-ton hydraulic press for metal forming and blanking operations.',
 '油圧プレス HP-80は、板金の成形・打抜き・曲げ加工に対応する80トン級の産業用プレス機です。安全装置を標準搭載し、幅広い金属加工現場で活用されています。',
 'The HP-80 is an 80-ton industrial hydraulic press for sheet metal forming, blanking, and bending. Standard safety interlocks make it suitable for a wide range of metalworking environments.',
 '最大加圧力80トン\n安全ライトカーテン標準装備\nストローク長調整機能',
 'Maximum pressing force of 80 tons\nSafety light curtain included as standard\nAdjustable stroke length',
 '自動車部品成形、金属ブラケット製造、電子機器筐体加工', 'Automotive part forming, metal bracket manufacturing, electronics enclosure fabrication',
 '三相200V', '80トン / 80 tons', 'N/A', '1600 x 1100 x 2800 mm', '3200 kg', 'prod-industrial-press.svg', 'hydraulic-press-hp-80');

-- -----------------------------------------------------------------------------
-- facilities
-- -----------------------------------------------------------------------------
CREATE TABLE facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sort_order INT DEFAULT 0,
    machine_name_ja VARCHAR(255) NOT NULL,
    machine_name_en VARCHAR(255) NOT NULL,
    manufacturer VARCHAR(100),
    model VARCHAR(100),
    capacity VARCHAR(100),
    quantity INT DEFAULT 1,
    description_ja TEXT,
    description_en TEXT,
    image VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO facilities (sort_order, machine_name_ja, machine_name_en, manufacturer, model, capacity, quantity, description_ja, description_en, image) VALUES
(1, 'TIG溶接ステーション', 'TIG Welding Station', 'Yamato', 'TIG-200', '200A', 6, '精密板金加工向けの専用溶接ブースです。集塵・換気設備を完備しています。', 'Dedicated welding booths for precision sheet-metal work, fully equipped with dust extraction and ventilation.', 'fac-tig-station.svg'),
(2, 'ファイバーレーザー切断機', 'Fiber Laser Cutter', 'Yamato', 'LW-500', '500W', 2, '複雑形状の高速切断に対応するファイバーレーザー加工機です。', 'A fiber laser cutting system capable of fast, precise cuts for complex part geometries.', 'fac-laser-cutter.svg'),
(3, 'CNCマシニングセンタ', 'CNC Machining Center', 'Yamato', 'MC-4000', '4軸 / 4-axis', 3, '高精度な機械加工を実現する4軸CNCマシニングセンタです。', 'A 4-axis CNC machining center delivering high-precision mechanical machining.', 'fac-cnc-machine.svg'),
(4, 'ロボット溶接セル', 'Robotic Welding Cell', 'Yamato', 'ARC-6', '6軸 / 6-axis', 4, '量産ライン向けの自動溶接セルで、安定した品質を維持します。', 'Automated welding cells for high-volume production lines, maintaining consistent weld quality.', 'fac-robot-cell.svg'),
(5, 'CNCプレスブレーキ', 'CNC Press Brake', 'Yamato', 'HP-80', '80トン / 80 tons', 2, '複雑な曲げ加工にも対応するCNC制御のプレスブレーキです。', 'A CNC-controlled press brake capable of handling complex bending operations.', 'fac-press-brake.svg');

-- -----------------------------------------------------------------------------
-- projects
-- -----------------------------------------------------------------------------
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ja VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    industry_ja VARCHAR(100),
    industry_en VARCHAR(100),
    year VARCHAR(10),
    location_ja VARCHAR(100),
    location_en VARCHAR(100),
    description_ja TEXT,
    description_en TEXT,
    challenge_ja TEXT,
    challenge_en TEXT,
    solution_ja TEXT,
    solution_en TEXT,
    technology_ja TEXT,
    technology_en TEXT,
    result_ja TEXT,
    result_en TEXT,
    image VARCHAR(255),
    gallery TEXT,
    slug VARCHAR(255) UNIQUE,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO projects (name_ja, name_en, industry_ja, industry_en, year, location_ja, location_en, description_ja, description_en, challenge_ja, challenge_en, solution_ja, solution_en, technology_ja, technology_en, result_ja, result_en, image, slug) VALUES
('自動車部品溶接ライン自動化', 'Automotive Parts Welding Line Automation', '自動車', 'Automotive', '2024', '愛知県', 'Aichi, Japan',
 '自動車部品メーカー様の溶接工程をロボット化し、品質のばらつきを解消したプロジェクトです。', 'A project that automated the welding process for an automotive parts manufacturer, eliminating quality variance.',
 '手作業による溶接品質のばらつきと、熟練工不足による生産能力の限界が課題でした。', 'Manual welding produced inconsistent quality, and a shortage of skilled welders was limiting production capacity.',
 'WeldBot ARC-6を4台導入し、オフラインティーチングで複数車種への対応を可能にしました。', 'We installed four WeldBot ARC-6 units with offline teaching, enabling flexible support for multiple vehicle models.',
 'ロボット溶接、オフラインティーチング', 'Robotic Welding, Offline Teaching',
 '不良率を60%削減し、生産能力を1.4倍に向上させました。', 'Defect rates dropped 60% and production capacity increased 1.4x.',
 'proj-automotive.svg', 'automotive-welding-line-automation'),

('鉄骨構造物製作プロジェクト', 'Steel Structure Fabrication Project', '建設', 'Construction', '2023', '神奈川県', 'Kanagawa, Japan',
 '大型商業施設の鉄骨構造物を、厚板MIG溶接によって製作したプロジェクトです。', 'A project fabricating structural steel for a large commercial facility using heavy-plate MIG welding.',
 '短工期の中で大量の厚板溶接を、強度基準を満たしながら完了させる必要がありました。', 'A large volume of heavy-plate welding had to be completed within a tight schedule while meeting strength requirements.',
 'MIG-350 Industrialを複数台稼働させ、班体制による並行作業で工程を短縮しました。', 'We ran multiple MIG-350 Industrial units in parallel, using team-based shifts to compress the schedule.',
 'MIG溶接、品質検査', 'MIG Welding, Quality Inspection',
 '工期を予定より2週間短縮し、全溶接部が強度基準をクリアしました。', 'The schedule was shortened by two weeks, and all welds passed the required strength standards.',
 'proj-construction.svg', 'steel-structure-fabrication'),

('プラント配管メンテナンス', 'Energy Plant Piping Maintenance', 'エネルギー', 'Energy', '2022', '千葉県', 'Chiba, Japan',
 'エネルギープラントの配管設備に対し、定期保守とTIG溶接による補修を行ったプロジェクトです。', 'A project providing scheduled maintenance and TIG-weld repairs for piping at an energy plant.',
 '稼働を止められない設備において、高精度な補修と厳格な安全管理が求められました。', 'Repairs had to be precise and safety procedures strict, all without stopping plant operations.',
 'TIG-200 Proによる精密補修と、事前の非破壊検査を組み合わせた保守計画を実施しました。', 'We combined precision repairs using the TIG-200 Pro with a maintenance plan built around prior non-destructive testing.',
 'TIG溶接、非破壊検査', 'TIG Welding, Non-Destructive Testing',
 '計画通りに保守を完了し、設備の安全性と稼働率を維持しました。', 'Maintenance was completed on schedule, preserving both equipment safety and uptime.',
 'proj-energy.svg', 'energy-plant-piping-maintenance');

-- -----------------------------------------------------------------------------
-- news
-- -----------------------------------------------------------------------------
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_ja VARCHAR(100),
    category_en VARCHAR(100),
    title_ja VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    content_ja TEXT,
    content_en TEXT,
    image VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    publish_date DATE,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO news (category_ja, category_en, title_ja, title_en, content_ja, content_en, image, slug, publish_date) VALUES
('COMPANY', 'COMPANY', '新しい溶接設備を導入しました', 'We Have Introduced New Welding Equipment',
 'この度、当社ではファイバーレーザー溶接システム「LaserWeld-500」を新たに導入いたしました。本設備の導入により、精密部品分野における対応力がさらに向上いたします。今後もお客様のニーズに応える設備投資を継続してまいります。',
 'We are pleased to announce the introduction of our new fiber laser welding system, the LaserWeld-500. This addition further strengthens our capabilities in precision component welding. We will continue to invest in equipment that meets our clients\' evolving needs.',
 'news-1.svg', 'new-welding-equipment-introduced', '2026-08-21'),

('COMPANY', 'COMPANY', '第二工場の増設工事が完了しました', 'Expansion of Our Second Factory Completed',
 '生産能力向上を目的とした第二工場の増設工事が完了し、本格稼働を開始いたしました。新工場にはロボット溶接セルを追加導入し、量産対応力を強化しています。',
 'Construction to expand our second factory, aimed at increasing production capacity, has been completed and full operations have begun. The new facility includes additional robotic welding cells to strengthen our high-volume production capabilities.',
 'news-2.svg', 'second-factory-expansion-completed', '2026-06-10'),

('NOTICE', 'NOTICE', '安全衛生教育を実施いたしました', 'Safety Training Program Conducted',
 '全従業員を対象とした安全衛生教育を実施いたしました。今後も安全な職場環境の維持・向上に努めてまいります。',
 'We conducted a safety and health training program for all employees. We remain committed to maintaining and improving a safe working environment.',
 'news-3.svg', 'safety-training-conducted', '2026-04-02');

-- -----------------------------------------------------------------------------
-- inquiries — contact form submissions
-- -----------------------------------------------------------------------------
CREATE TABLE inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inquiry_type VARCHAR(100),
    company_name VARCHAR(150),
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50),
    subject VARCHAR(255),
    product_interest VARCHAR(255),
    quantity VARCHAR(50),
    budget_range VARCHAR(100),
    desired_timeline VARCHAR(100),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- End of database.sql
-- =============================================================================
