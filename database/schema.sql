-- Implement the database schema as discussed in the design.
-- Robert CAT Tool Database Schema
-- This file implements the database schema as discussed in the architecture design
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

-- Projects table to group related documents
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    client VARCHAR(100),
    manager_id INT,
    deadline DATETIME,
    status ENUM('planning', 'in_progress', 'review', 'completed', 'archived') DEFAULT 'planning',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id)
);

-- Project-Document relationship
CREATE TABLE project_documents (
    project_id INT NOT NULL,
    document_id INT NOT NULL,
    PRIMARY KEY (project_id, document_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
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

-- Translation versions table for version control
CREATE TABLE translation_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_id INT NOT NULL,
    version_number INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'reviewed', 'approved', 'rejected'),
    modified_by INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_id) REFERENCES translations(id) ON DELETE CASCADE,
    FOREIGN KEY (modified_by) REFERENCES users(id),
    UNIQUE KEY (translation_id, version_number)
);

-- Translation memories table
CREATE TABLE translation_memories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    source_language_id INT NOT NULL,
    target_language_id INT NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_language_id) REFERENCES languages(id),
    FOREIGN KEY (target_language_id) REFERENCES languages(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Translation memory entries
CREATE TABLE translation_memory_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    memory_id INT NOT NULL,
    source_content TEXT NOT NULL,
    target_content TEXT NOT NULL,
    context TEXT,
    quality_score DECIMAL(3,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (memory_id) REFERENCES translation_memories(id) ON DELETE CASCADE,
    FULLTEXT KEY (source_content, target_content)
);

-- Terminology table (glossary)
CREATE TABLE terminology (
    id INT AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(255) NOT NULL,
    definition TEXT NOT NULL,
    context TEXT,
    source_language_id INT NOT NULL,
    domain VARCHAR(100),
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_language_id) REFERENCES languages(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY (term, source_language_id, domain)
);

-- Terminology translations
CREATE TABLE terminology_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    terminology_id INT NOT NULL,
    language_id INT NOT NULL,
    translation VARCHAR(255) NOT NULL,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (terminology_id) REFERENCES terminology(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY (terminology_id, language_id)
);

-- User projects (for permissions)
CREATE TABLE user_projects (
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    role ENUM('manager', 'translator', 'reviewer', 'observer') NOT NULL DEFAULT 'translator',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, project_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- User language proficiency
CREATE TABLE user_languages (
    user_id INT NOT NULL,
    language_id INT NOT NULL,
    proficiency ENUM('native', 'fluent', 'working', 'limited') NOT NULL,
    PRIMARY KEY (user_id, language_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id)
);

-- Comments on translation units
CREATE TABLE translation_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_id) REFERENCES translations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tags for organizing content
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#CCCCCC',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Document tags
CREATE TABLE document_tags (
    document_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (document_id, tag_id),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Translation unit tags
CREATE TABLE translation_unit_tags (
    translation_unit_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (translation_unit_id, tag_id),
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Initial data insertion for testing
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

-- Add translation version history examples
INSERT INTO translation_versions (translation_id, version_number, content, status, modified_by, comment) VALUES
(1, 1, 'Bienvenido al Manual del Usuario', 'draft', 1, 'Initial translation'),
(1, 2, 'Bienvenido al Manual de Usuario del Producto', 'approved', 1, 'Updated to match terminology guidelines'),
(2, 1, 'Este manual te guiará por todas las características de nuestro producto.', 'draft', 1, 'Initial translation'),
(2, 2, 'Este manual le guiará a través de todas las características de nuestro producto.', 'approved', 1, 'Switched to formal tone');

-- Add a project and associate documents with it
INSERT INTO projects (id, name, description, client, manager_id, deadline, status) VALUES
(1, 'Product Launch 2025', 'Documentation for the new product line launch', 'Internal', 1, '2025-07-15 00:00:00', 'in_progress');

INSERT INTO project_documents (project_id, document_id) VALUES
(1, 1),
(1, 3);

-- Add translation memories
INSERT INTO translation_memories (id, name, description, source_language_id, target_language_id, is_public, created_by) VALUES
(1, 'Technical Documentation EN-ES', 'Translation memory for technical documentation English to Spanish', 1, 2, TRUE, 1),
(2, 'Marketing Content EN-FR', 'Translation memory for marketing materials English to French', 1, 5, FALSE, 1);

-- Add translation memory entries
INSERT INTO translation_memory_entries (memory_id, source_content, target_content, context, quality_score) VALUES
(1, 'Safety Instructions', 'Instrucciones de Seguridad', 'Section header', 1.00),
(1, 'User Manual', 'Manual de Usuario', 'Document title', 1.00),
(1, 'Always read safety instructions before operating the device.', 'Lea siempre las instrucciones de seguridad antes de operar el dispositivo.', 'Safety warning', 0.95),
(2, 'Innovative Solutions', 'Solutions Innovantes', 'Marketing term', 0.90),
(2, 'We provide cutting-edge technology', 'Nous fournissons une technologie de pointe', 'Company description', 0.85);

-- Add terminology entries
INSERT INTO terminology (id, term, definition, context, source_language_id, domain, created_by) VALUES
(1, 'device', 'The physical product being described', 'technical', 1, 'hardware', 1),
(2, 'power adapter', 'The component that connects the device to electrical power', 'technical', 1, 'hardware', 1),
(3, 'configuration', 'The process of setting up the device for use', 'technical', 1, 'software', 1);

-- Add terminology translations
INSERT INTO terminology_translations (terminology_id, language_id, translation, notes, created_by) VALUES
(1, 2, 'dispositivo', 'Preferred term over "aparato"', 1),
(1, 5, 'appareil', '', 1),
(2, 2, 'adaptador de corriente', '', 1),
(2, 5, 'adaptateur secteur', '', 1),
(3, 2, 'configuración', '', 1),
(3, 5, 'configuration', '', 1);