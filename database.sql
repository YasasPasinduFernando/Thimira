CREATE DATABASE IF NOT EXISTS village_traveler CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE village_traveler;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    reset_token_hash CHAR(64) DEFAULT NULL,
    reset_token_expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attractions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(80) NOT NULL,
    name_en VARCHAR(150) NOT NULL,
    name_si VARCHAR(200) NOT NULL,
    short_en VARCHAR(255) NOT NULL,
    short_si VARCHAR(255) NOT NULL,
    description_en TEXT NOT NULL,
    description_si TEXT NOT NULL,
    open_hours VARCHAR(80) DEFAULT '08:00 - 18:00',
    entry_fee_lkr DECIMAL(10,2) DEFAULT 0,
    latitude DECIMAL(10,7) NOT NULL,
    longitude DECIMAL(10,7) NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS trip_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    status ENUM('planned','completed') NOT NULL DEFAULT 'planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS trip_plan_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_plan_id INT NOT NULL,
    attraction_id INT NOT NULL,
    visit_order INT NOT NULL,
    is_visited TINYINT(1) NOT NULL DEFAULT 0,
    visited_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_plan_id) REFERENCES trip_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, email, password_hash, role)
VALUES ('admin', 'info.itzone.sl@gmail.com', '$2y$10$QNeckYAE8k1RVmKfDq2ra.kXbKp0aZ0rGdv7k0PtY/3flYoV.J8Ku', 'admin')
ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    email = VALUES(email),
    role = VALUES(role);

INSERT INTO attractions (category, name_en, name_si, short_en, short_si, description_en, description_si, open_hours, entry_fee_lkr, latitude, longitude, image_url)
VALUES
('Nature / Ecotourism', 'Madu Ganga Boat Safari', 'මාදු ගඟ බෝට්ටු සෆාරි',
 'Mangrove islands, bird life, and calm lagoon scenery.',
 'මැන්ග්‍රෝව් දූපත්, පක්ෂීජීවිතය සහ සන්සුන් වැව් දර්ශන.',
 'A famous wetland ecosystem with mangrove forests, small islands, and traditional livelihoods. Ideal for morning and evening boat rides.',
 'මැන්ග්‍රෝව් වන සහ කුඩා දූපත් ඇති ප්‍රසිද්ධ තෙත් බිම් පද්ධතියකි. උදේ සහ සවස බෝට්ටු සංචාරයට සුදුසුය.',
 '07:00 - 18:00', 3500, 6.2745000, 80.0375000, 'https://images.unsplash.com/photo-1501785888041-af3ef285b470'),

('Religious / Cultural', 'Kothduwa Raja Maha Viharaya', 'කොත්දූව රාජමහා විහාරය',
 'Historic island temple with serene surroundings.',
 'ඉතිහාසගත දූපත් විහාරයක් සහ සන්සුන් වටපිටාව.',
 'A Buddhist temple reached by short boat ride, known for peaceful ambiance and cultural value.',
 'බෝට්ටුවකින් පැමිණිය හැකි, සන්සුන් පරිසරය සහ සංස්කෘතික වටිනාකමක් ඇති බෞද්ධ විහාරයකි.',
 '06:00 - 18:00', 0, 6.2679000, 80.0286000, 'https://images.unsplash.com/photo-1548013146-72479768bada'),

('Architecture / Garden', 'Brief Garden by Bevis Bawa', 'බීවිස් බාවාගේ බ්‍රීෆ් ගාර්ඩන්',
 'Artistic tropical garden and house museum.',
 'කලාත්මක උෂ්ණ කලාපීය උයනක් සහ නිවසේ සංග්‍රහාලයක්.',
 'A landscape masterpiece featuring sculpture, architecture, and tropical planting by Bevis Bawa.',
 'බීවිස් බාවා විසින් නිර්මාණය කරන ලද මූර්ති, වාස්තු හා උද්භිද සැලසුම් සම්මිශ්‍රිත උද්‍යාන කලාකෘතියකි.',
 '08:00 - 17:00', 3000, 6.2519000, 80.0619000, 'https://images.unsplash.com/photo-1469474968028-56623f02e42e'),

('Architecture / Garden', 'Lunuganga Estate', 'ලුනුගඟ එස්ටේට්',
 'Geoffrey Bawa estate with iconic landscape design.',
 'ජෙෆ්රි බාවාගේ ප්‍රසිද්ධ භූදර්ශන නිර්මාණය සහිත එස්ටේට්.',
 'World-renowned country estate of architect Geoffrey Bawa, blending architecture and nature.',
 'ලෝකප්‍රසිද්ධ වාස්තු ශිල්පී ජෙෆ්රි බාවාගේ නිවස සහ උද්‍යානය; වාස්තු හා ස්වභාවය සමගාමීව සැලසුම් කර ඇත.',
 '09:00 - 17:00', 4500, 6.3311000, 80.0437000, 'https://images.unsplash.com/photo-1472396961693-142e6e269027'),

('Culture / Crafts', 'Ambalangoda Mask Museum', 'අම්බලන්ගොඩ මැස්ක් කලාගාරය',
 'Traditional mask carving and folklore exhibitions.',
 'සාම්ප්‍රදායික වෙස්මුහුණු කැටයම් සහ ජනකථා ප්‍රදර්ශන.',
 'Showcases Sri Lankan mask traditions used in ritual dance and storytelling.',
 'ශ්‍රී ලාංකික වෙස්මුහුණු නර්තන හා ජනකථා සම්ප්‍රදායන් ප්‍රදර්ශනය කරයි.',
 '09:00 - 18:00', 1200, 6.2358000, 80.0531000, 'https://images.unsplash.com/photo-1518998053901-5348d3961a04'),

('Geology / Heritage', 'Meetiyagoda Moonstone Mine', 'මීටියාගොඩ සඳකඩපහණ ගල් පතල',
 'Historic moonstone mining area and museum.',
 'ඉතිහාසගත සඳකඩපහණ ගල් කැණීමේ ප්‍රදේශය සහ කුඩා කෞතුකාගාරය.',
 'Learn the traditional methods of moonstone mining and gem processing unique to this area.',
 'මෙම ප්‍රදේශයට විශේෂ වූ සාම්ප්‍රදායික සඳකඩපහණ කැණීම සහ රත්න සැකසුම් ක්‍රම දැනගත හැක.',
 '08:30 - 17:30', 1000, 6.2102000, 80.0898000, 'https://images.unsplash.com/photo-1465101162946-4377e57745c3'),

('Religious / Cultural', 'Karandeniya Shailatharama Viharaya', 'කරන්දෙණිය ශෛලතාරාම විහාරය',
 'Ancient temple with cave paintings and statues.',
 'ගුහා චිත්‍ර සහ ප්‍රතිමා ඇති පුරාණ විහාරස්ථානයක්.',
 'A significant inland temple with archaeological and religious importance.',
 'ඉතිහාසය සහ ආගමික වටිනාකම ඇති අභ්‍යන්තර භූමියේ වැදගත් විහාරස්ථානයකි.',
 '06:00 - 18:00', 0, 6.2714000, 80.1910000, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee'),

('Wildlife / Conservation', 'Kosgoda Turtle Hatchery', 'කොස්ගොඩ කැස්බෑවා සංරක්ෂණ මධ්‍යස්ථානය',
 'Sea turtle conservation center and education visits.',
 'මුහුදු කැස්බෑවා සංරක්ෂණය සහ අධ්‍යාපනික සංචාර.',
 'Conservation program protecting turtle eggs and rehabilitating hatchlings.',
 'කැස්බෑවා බිත්තර ආරක්ෂා කිරීම සහ කුඩා කැස්බෑවන් මුදාහැරීමේ සංරක්ෂණ වැඩසටහනක්.',
 '08:00 - 18:00', 1500, 6.3327000, 80.0281000, 'https://images.unsplash.com/photo-1501594907352-04cda38ebc29'),

('Nature / River', 'Bentota River Estuary', 'බෙන්තොට ගඟ මුහුදු මෝය',
 'Scenic river mouth with boat tours and bird watching.',
 'දර්ශනීය ගඟ මෝය, බෝට්ටු සංචාර සහ පක්ෂී නැරඹීම.',
 'Popular for estuary views, mangroves, and mixed saltwater-freshwater habitats.',
 'මැන්ග්‍රෝව් සහ මිශ්‍ර ජල පද්ධතියක් සමග දර්ශනීය මෝය පරිසරයක්.',
 '06:30 - 18:30', 2000, 6.4218000, 79.9981000, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e'),

('History / Culture', 'Ariyapala Folk Museum', 'අරියපාල ජනකලා කෞතුකාගාරය',
 'Folk arts, devil masks, and traditional performance heritage.',
 'ජනකලා, යක්ෂ වෙස්මුහුණු සහ සාම්ප්‍රදායික නර්තන උරුමය.',
 'A folk museum documenting southern Sri Lankan ritual arts and crafts traditions.',
 'දකුණු ශ්‍රී ලංකාවේ ජනකලා හා ශිල්ප සම්ප්‍රදායන් ලේඛනගත කරන ජනකලා කෞතුකාගාරයකි.',
 '09:00 - 17:00', 800, 6.2440000, 80.0550000, 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8')
ON DUPLICATE KEY UPDATE
    name_en = VALUES(name_en),
    name_si = VALUES(name_si),
    short_en = VALUES(short_en),
    short_si = VALUES(short_si),
    description_en = VALUES(description_en),
    description_si = VALUES(description_si),
    open_hours = VALUES(open_hours),
    entry_fee_lkr = VALUES(entry_fee_lkr),
    latitude = VALUES(latitude),
    longitude = VALUES(longitude),
    image_url = VALUES(image_url),
    is_active = 1;
