-- Fix "????" for Sinhala when tables were created under latin1 (common on older XAMPP defaults).
-- Change USE to your database name on hosting (e.g. if0_41737287_villagetraveler).
-- After running, re-import attraction text from database.sql if values were already saved as question marks.

USE village_traveler;

ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE attractions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE trip_plans CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE trip_plan_items CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
