-- Simplified Robert CAT Tool Database Schema
DROP DATABASE IF EXISTS robert_cat;
CREATE DATABASE robert_cat;
USE robert_cat;

-- Users table for authentication and authorization
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    role ENUM('admin', 'translator', 'reviewer', 'reader') NOT NULL DEFAULT 'translator',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Languages table
CREATE TABLE languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,  -- ISO code (e.g., en-US, fr-FR)
    name VARCHAR(100) NOT NULL,
    direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Documents table
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    source_language_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_language_id) REFERENCES languages(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Translation units table
CREATE TABLE translation_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    sequence_number INT NOT NULL,
    source_content TEXT NOT NULL,
    context TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    UNIQUE KEY (document_id, sequence_number)
);

-- Translations table
CREATE TABLE translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id INT NOT NULL,
    language_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'reviewed', 'approved', 'rejected') DEFAULT 'draft',
    translated_by INT,
    reviewed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id),
    FOREIGN KEY (translated_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    UNIQUE KEY (translation_unit_id, language_id)
);

-- Initial data insertion
INSERT INTO languages (code, name, direction) VALUES 
('en-US', 'English (US)', 'ltr'),
('es-ES', 'Spanish (Spain)', 'ltr'),
('ar-SA', 'Arabic (Saudi Arabia)', 'rtl'),
('zh-CN', 'Chinese (Simplified)', 'ltr'),
('fr-FR', 'French (France)', 'ltr');

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
VALUES ('admin', 'admin@robert-cat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin');

-- Add sample documents
INSERT INTO documents (id, title, description, source_language_id, file_path, file_type, created_by) VALUES 
(1, 'Product User Manual', 'User manual for our flagship product', 1, '/uploads/documents/manual.docx', 'docx', 1),
(2, 'Website Homepage', 'Content for company website homepage', 1, '/uploads/documents/homepage.html', 'html', 1),
(3, 'Marketing Brochure', 'Spring 2025 marketing campaign materials', 1, '/uploads/documents/brochure.pdf', 'pdf', 1),
(4, 'Mobile App Strings', 'UI strings for the mobile application', 1, '/uploads/documents/strings.json', 'json', 1);

-- Add translation units for Document 1: Product User Manual
INSERT INTO translation_units (id, document_id, sequence_number, source_content, context) VALUES
(1, 1, 1, 'Welcome to the Product User Manual', 'Title of the manual'),
(2, 1, 2, 'This manual will guide you through all the features of our product.', 'Introduction paragraph'),
(3, 1, 3, 'Safety Instructions', 'Section header'),
(4, 1, 4, 'Always read safety instructions before operating the device.', 'Safety warning'),
(5, 1, 5, 'Keep away from water and heat sources.', 'Safety instruction'),
(6, 1, 6, 'Getting Started', 'Section header'),
(7, 1, 7, 'Unbox your device and check that all components are included.', 'Setup instruction'),
(8, 1, 8, 'Connect the power adapter to a wall outlet.', 'Setup instruction'),
(9, 1, 9, 'Press and hold the power button for 3 seconds to turn on the device.', 'Power on instruction'),
(10, 1, 10, 'Configuration', 'Section header');

-- Add translation units for Document 2: Website Homepage
INSERT INTO translation_units (id, document_id, sequence_number, source_content, context) VALUES
(11, 2, 1, 'Innovative Solutions for Your Business', 'Main headline'),
(12, 2, 2, 'We provide cutting-edge technology to help your business grow.', 'Subheading'),
(13, 2, 3, 'Our Products', 'Section header'),
(14, 2, 4, 'Explore our range of innovative products designed to meet your needs.', 'Product section intro'),
(15, 2, 5, 'About Us', 'Section header'),
(16, 2, 6, 'Founded in 2010, our company has been at the forefront of innovation.', 'Company description'),
(17, 2, 7, 'Contact Us', 'Section header'),
(18, 2, 8, 'Get in touch with our team to learn more about our offerings.', 'Contact section intro'),
(19, 2, 9, 'Privacy Policy', 'Footer link'),
(20, 2, 10, 'Terms of Service', 'Footer link');

-- Add Spanish translations for some units in Document 1
INSERT INTO translations (translation_unit_id, language_id, content, status, translated_by, reviewed_by) VALUES
(1, 2, 'Bienvenido al Manual de Usuario del Producto', 'approved', 1, 1),
(2, 2, 'Este manual le guiará a través de todas las características de nuestro producto.', 'approved', 1, 1),
(3, 2, 'Instrucciones de Seguridad', 'approved', 1, 1),
(4, 2, 'Lea siempre las instrucciones de seguridad antes de operar el dispositivo.', 'approved', 1, 1),
(5, 2, 'Mantenga alejado del agua y fuentes de calor.', 'approved', 1, 1),
(6, 2, 'Primeros Pasos', 'draft', 1, NULL);

-- Add French translations for some units in Document 1
INSERT INTO translations (translation_unit_id, language_id, content, status, translated_by) VALUES
(1, 5, 'Bienvenue au Manuel d\'Utilisateur du Produit', 'draft', 1),
(2, 5, 'Ce manuel vous guidera à travers toutes les fonctionnalités de notre produit.', 'draft', 1),
(3, 5, 'Instructions de Sécurité', 'draft', 1);

-- Add Arabic translations for some units in Document 1
INSERT INTO translations (translation_unit_id, language_id, content, status, translated_by, reviewed_by) VALUES
(1, 3, 'مرحبًا بك في دليل مستخدم المنتج', 'reviewed', 1, 1),
(2, 3, 'سيرشدك هذا الدليل خلال جميع ميزات منتجنا.', 'reviewed', 1, 1);

-- Add Spanish translations for some units in Document 2
INSERT INTO translations (translation_unit_id, language_id, content, status, translated_by) VALUES
(11, 2, 'Soluciones Innovadoras para su Negocio', 'draft', 1),
(12, 2, 'Proporcionamos tecnología de vanguardia para ayudar a su negocio a crecer.', 'draft', 1),
(13, 2, 'Nuestros Productos', 'draft', 1);