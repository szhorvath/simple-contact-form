CREATE TABLE
IF NOT EXISTS `leads`
(
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar
(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar
(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar
(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribed` tinyint
(1) NOT NULL DEFAULT '0',
  `ip` varchar
(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar
(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY
(`id`),
  UNIQUE KEY `leads_email_unique`
(`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE
IF NOT EXISTS `messages`
(
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar
(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar
(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY
(`id`),
  KEY `messages_lead_id_foreign`
(`lead_id`),
  CONSTRAINT `messages_lead_id_foreign` FOREIGN KEY
(`lead_id`) REFERENCES `leads`
(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
