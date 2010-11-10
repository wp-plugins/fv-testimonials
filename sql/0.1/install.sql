CREATE TABLE IF NOT EXISTS `%prefix%fpt_testimonials` (
   `id` INT AUTO_INCREMENT PRIMARY KEY,
   `title` VARCHAR(255) NOT NULL,
   `slug` VARCHAR(100) NOT NULL,
   `excerpt` TEXT,
   `text` TEXT,
   `category` INT NOT NULL,
   `status` ENUM('wait','approved','deleted') NOT NULL DEFAULT 'wait',
   `featured` ENUM('yes','no','old') NOT NULL DEFAULT 'no',
   `date` DATE,
   `last_modified` VARCHAR(100),
   `last_modified_date` DATETIME,
   `order` INT NOT NULL
) ENGINE=InnoDB CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `%prefix%fpt_images` (
   `id` INT AUTO_INCREMENT PRIMARY KEY,
   `testimonial` INT NOT NULL,
   `path` VARCHAR(255) NOT NULL,
   `width` INT NOT NULL,
   `height` INT NOT NULL,
   `type` ENUM('original','large','medium','small','thumbs') NOT NULL
) ENGINE=InnoDB CHARACTER SET=utf8