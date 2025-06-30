-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql:3306
-- Generation Time: Jun 29, 2025 at 08:55 PM
-- Server version: 9.3.0
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE DATABASE IF NOT EXISTS `interview` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `interview`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `interview`
--

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso_code` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` char(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `system` tinyint(1) NOT NULL DEFAULT '1',
  `protected` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `uuid`, `iso_code`, `title`, `active`, `system`, `protected`) VALUES
(1, '75f00832-5fef-4b8d-b942-302935e56eda', 'eng', 'English', 1, 1, 1),
(2, '12e1f5a4-4d0e-49c1-a64b-6ad94d0f9b7d', 'tur', 'Türkçe', 1, 1, 1),
(3, '09274f2d-e8e0-4621-b29d-09716e337172', 'rou', 'Română', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` char(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` json NOT NULL,
  `date_record` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `id` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `system` tinyint(1) NOT NULL DEFAULT '0',
  `iso_code` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `translation_key` char(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `translation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `translations`
--

INSERT INTO `translations` (`id`, `uuid`, `system`, `iso_code`, `translation_key`, `translation`) VALUES
(1, 'e3e45c89-4b74-47ff-804a-aaaaf4a46f63', 1, 'eng', 'PAGES_TRANSLATION_NOT_FOUND_TITLE', 'Translation Not Found Page!'),
(2, 'a1613de7-7d58-4fbf-842d-edf6de3af16e', 1, 'tur', 'PAGES_TRANSLATION_NOT_FOUND_TITLE', 'Çeviri Bulunamadı Sayfası!'),
(3, '96b926fd-59a7-425c-946d-e0b1caa5da1f', 1, 'rou', 'PAGES_TRANSLATION_NOT_FOUND_TITLE', 'Pagina nu a fost găsită pentru traducere!'),
(4, '39ab7b4f-8ba0-42d9-812e-7b265362e986', 1, 'eng', 'PAGES_TRANSLATION_NOT_FOUND_DESCRIPTION', 'The translation not found page is a sample page. When the requested text doesn’t have a translation available, system returns \"translation-not-found\". This text is collected across all the platform to help you create missing translations.'),
(5, '73f104eb-d1a5-48a1-be4b-3b750cc1e0cf', 1, 'tur', 'PAGES_TRANSLATION_NOT_FOUND_DESCRIPTION', 'Çeviri bulunamadı sayfası bir örnek sayfadır. İstenen metinde çeviri bulunmadığında sistem \"translation-not-found\" döndürür. Bu metin, eksik çevirileri oluşturmanıza yardımcı olmak için tüm platformda toplanır.'),
(6, '7cc957c0-a672-4d48-b1e9-65331a78fa8a', 1, 'rou', 'PAGES_TRANSLATION_NOT_FOUND_DESCRIPTION', 'Pagina „traducere negăsită” este o pagină exemplu. Când textul solicitat nu are o traducere disponibilă, sistemul returnează \"translation-not-found\". Acest text este colectat pe întreaga platformă pentru a vă ajuta să creați traducerile lipsă.'),
(7, 'd0dbb0fb-5f9c-4405-a7b3-1b60cdcccfc4', 1, 'eng', 'PAGES_ABOUT_TITLE', 'About Translation Management App'),
(8, '4dcde18d-3b0a-4ca0-9a03-2b1d14e2ad4b', 1, 'tur', 'PAGES_ABOUT_TITLE', 'About Translation Management App'),
(9, '10c5bf0e-52d3-47a0-b67d-0f4ac14b0a0e', 1, 'rou', 'PAGES_ABOUT_TITLE', 'Despre Aplicația de Gestionare a Traducerilor'),
(10, '48485c92-089f-4109-9045-8ef05389c4bb', 1, 'eng', 'PAGES_ABOUT_DESCRIPTION', 'Translation Management App is a tool designed to streamline the process of translating content across multiple languages. It helps teams collaborate efficiently by organizing translation tasks, managing language assets like glossaries and translation memories, and integrating with content platforms. Ideal for businesses and localization teams, the app improves consistency, reduces manual effort, and speeds up global content delivery.'),
(11, '13332241-6116-483b-9fef-737251669949', 1, 'tur', 'PAGES_ABOUT_DESCRIPTION', 'Aplicația de gestionare a traducerilor este un instrument conceput pentru a eficientiza procesul de traducere a conținutului în mai multe limbi. Ajută echipele să colaboreze eficient prin organizarea sarcinilor de traducere, gestionarea resurselor lingvistice precum glosarele și memoriile de traducere și integrarea cu platformele de conținut. Ideală pentru companii și echipe de localizare, aplicația îmbunătățește consecvența, reduce efortul manual și accelerează livrarea globală de conținut.'),
(12, '0f210e34-4f8a-4cb0-9214-ceb24e3d56f0', 1, 'rou', 'PAGES_ABOUT_DESCRIPTION', 'Çeviri Yönetim Uygulaması, birden fazla dilde içerik çevirme sürecini kolaylaştırmak için tasarlanmış bir araçtır. Çeviri görevlerini düzenleyerek, sözlükler ve çeviri bellekleri gibi dil varlıklarını yöneterek ve içerik platformlarıyla entegre olarak ekiplerin verimli bir şekilde işbirliği yapmasına yardımcı olur. İşletmeler ve yerelleştirme ekipleri için ideal olan uygulama, tutarlılığı artırır, manuel çabayı azaltır ve küresel içerik dağıtımını hızlandırır.'),
(13, '4aeb4fd4-86f8-4d57-b30c-283a1e05131f', 1, 'eng', 'PAGES_TRANSLATION_MENU_ITEM_GROUPED', 'Grouped'),
(14, '8d91c9e1-56ff-43d3-8610-c3287fd407e1', 1, 'tur', 'PAGES_TRANSLATION_MENU_ITEM_GROUPED', 'Gruplanmış'),
(15, '9f4c048d-f003-43ea-91be-c8d3f7507e0d', 1, 'rou', 'PAGES_TRANSLATION_MENU_ITEM_GROUPED', 'Grupate'),
(16, '64fadbab-5fe3-4e33-95b4-78a4abf72b12', 1, 'eng', 'PAGES_TRANSLATION_MENU_ITEM_ALL', 'All'),
(17, '637974e-d21a-4f8c-9548-5733860e662c', 1, 'tur', 'PAGES_TRANSLATION_MENU_ITEM_ALL', 'Tümü'),
(18, 'dfee4206-7758-43a2-b90c-e16b64448121', 1, 'rou', 'PAGES_TRANSLATION_MENU_ITEM_ALL', 'Toate'),
(19, 'd1ebec08-75c7-4aa4-ab22-80c57e813c81', 1, 'eng', 'PAGES_HOME_TITLE', 'Welcome to Translation Management App'),
(20, 'd1ebec08-75c7-4aa4-ab22-80c57e813c81', 1, 'tur', 'PAGES_HOME_TITLE', 'Çeviri Yönetimi Uygulamasına Hoş Geldiniz'),
(21, '86a4cdea-00de-4013-897a-dbc85eb26902', 1, 'rou', 'PAGES_HOME_TITLE', 'Bun Venit la Aplicația de Gestionare a Traducerilor'),
(22, 'c3285746-8fd6-4256-b76c-6310464c19a0', 1, 'eng', 'PAGES_HOME_DESCRIPTION', 'Your hub for managing translations, streamlining localization, and reaching global audiences with ease. Start by adding your content, inviting your team, and letting us handle the rest. Let’s make your message multilingual!'),
(23, '18651d31-f412-4f79-ad02-47243859a365', 1, 'tur', 'PAGES_HOME_DESCRIPTION', 'Çevirileri yönetme, yerelleştirmeyi kolaylaştırma ve küresel kitlelere kolayca ulaşma merkeziniz. İçeriğinizi ekleyerek, ekibinizi davet ederek ve gerisini bize bırakarak başlayın. Mesajınızı çok dilli hale getirelim!'),
(24, 'f7169709-9a55-4e3e-a1e4-6bde36fdbac1', 1, 'rou', 'PAGES_HOME_DESCRIPTION', 'Centrul tău pentru gestionarea traducerilor, eficientizarea localizării și atingerea cu ușurință a publicului global. Începe prin a adăuga conținutul tău, invită-ți echipa și lasă-ne pe noi să ne ocupăm de restul. Hai să facem mesajul tău multilingv!'),
(25, '3d3703ae-a4ae-4937-b5ad-1b92e3fd4723', 1, 'eng', 'WEBSITE_PAGES_HOME', 'Home'),
(26, 'd9db512a-b442-48a2-a9da-af1320074f3b', 1, 'tur', 'WEBSITE_PAGES_HOME', 'Ana Sayfa'),
(27, '59f44196-e57d-4740-925d-2f13336b94d1', 1, 'rou', 'WEBSITE_PAGES_HOME', 'Pagina Principală'),
(28, '04030d78-ca0c-456d-9817-62188a79c910', 1, 'eng', 'PAGES_TRANSLATIONS_TITLE', 'Translation Management'),
(29, '3d3322ea-585d-4570-8136-d90531446c6d', 1, 'tur', 'PAGES_TRANSLATIONS_TITLE', 'Çeviri Yönetimi'),
(30, '8255d01e-6f2c-4d9b-a893-8c63727c370d', 1, 'rou', 'PAGES_TRANSLATIONS_TITLE', 'Managementul Traducerilor'),
(31, 'f286b54b-6148-4036-80ef-8d61f73e3ca5', 1, 'eng', 'PAGES_TRANSLATIONS_DESCRIPTION', 'Your central place to manage translations, collaborate with teams, and localize faster.'),
(32, '284f764b-87c2-45b2-bbda-da5ffaacebaf', 1, 'tur', 'PAGES_TRANSLATIONS_DESCRIPTION', 'Çevirilerinizi yönetmek, ekiplerle işbirliği yapmak ve daha hızlı yerelleştirmek için merkezi yeriniz.'),
(33, '1b9f481d-edbb-4dd8-8775-741d6f80a08e', 1, 'rou', 'PAGES_TRANSLATIONS_DESCRIPTION', 'Locul tău central pentru a gestiona traducerile, a colabora cu echipele și a localiza mai rapid.'),
(34, '37a75885-dc61-47f8-bf51-6f585b4910b6', 1, 'eng', 'MENU_HEADER_HOME', 'Home'),
(35, '337224e9-d146-4fc2-9db2-fd3b6dc17a5e', 1, 'tur', 'MENU_HEADER_HOME', 'Ana Sayfa'),
(36, '7cac713a-ba9b-441f-b85b-e8801d225202', 1, 'rou', 'MENU_HEADER_HOME', 'Pagina Principală'),
(37, 'ae172408-a148-46c0-962c-b21e76ba5d85', 1, 'eng', 'PAGES_LANGUAGES_TITLE', 'Translation Languages'),
(38, '25ce5048-6fce-4f42-b12a-971f583cf7e0', 1, 'tur', 'PAGES_LANGUAGES_TITLE', 'Çeviri Dilleri'),
(39, 'a6af05ce-1e27-4286-917b-9335847b66e2', 1, 'rou', 'PAGES_LANGUAGES_TITLE', 'Limbi de Traducere'),
(40, 'f2e5afbe-9d77-4048-a363-59e988e4b11e', 1, 'eng', 'PAGES_LANGUAGES_DESCRIPTION', 'Manage all your project languages in one place. Add new languages, assign translators, and track progress to ensure your content reaches a global audience—accurately and consistently.'),
(41, '614380f1-ef85-4d0d-892d-da8b7a0a90f9', 1, 'tur', 'PAGES_LANGUAGES_DESCRIPTION', 'Tüm proje dillerinizi tek bir yerden yönetin. Yeni diller ekleyin, çevirmenler atayın ve içeriğinizin küresel bir kitleye doğru ve tutarlı bir şekilde ulaşmasını sağlamak için ilerlemeyi takip edin.'),
(42, '9f0eac08-ecb2-40b1-b177-89bbdc5b16f4', 1, 'rou', 'PAGES_LANGUAGES_DESCRIPTION', 'Gestionați toate limbile proiectului dvs. într-un singur loc. Adăugați limbi noi, atribuiți traducători și urmăriți progresul pentru a vă asigura că conținutul dvs. ajunge la un public global - în mod precis și consecvent.'),
(43, '619df74a-f881-4e12-8ce9-77e4da4d0f45', 1, 'eng', 'MENU_HEADER_ABOUT', 'About'),
(44, '3559ff6f-42e6-4555-8539-71bce718e414', 1, 'tur', 'MENU_HEADER_ABOUT', 'Hakkında'),
(45, '2d37490a-d488-4c35-b46e-f64932b9ff66', 1, 'rou', 'MENU_HEADER_ABOUT', 'Despre'),
(46, '648977b5-461f-44f0-b6b9-90fc71a7034d', 1, 'eng', 'MENU_HEADER_LANGUAGES', 'Languages'),
(47, '0887ecfd-96a7-493f-ad8b-a652f2eb372a', 1, 'tur', 'MENU_HEADER_LANGUAGES', 'Diller'),
(48, '66bf5dfa-a82e-4da2-aca6-7c9e1c919e26', 1, 'rou', 'MENU_HEADER_LANGUAGES', 'Limbi'),
(49, '3a484557-0143-4234-959d-29e6c80d9ee1', 1, 'eng', 'MENU_HEADER_TRANSLATIONS', 'Translations'),
(50, 'a7ed94a2-f2d0-4dfe-ae95-62de32affc75', 1, 'tur', 'MENU_HEADER_TRANSLATIONS', 'Çeviriler'),
(51, 'eaf76bd6-0835-45d7-96dd-e7dc51d3490d', 1, 'rou', 'MENU_HEADER_TRANSLATIONS', 'Traduceri'),
(52, '80199095-1185-494d-b599-ca5148a2d057', 1, 'eng', 'MENU_HEADER_CHANGE_LANGUAGE', 'Change App Language'),
(53, '14b5e8a1-3f76-4dad-a63b-1c4c8b79a4ad', 1, 'tur', 'MENU_HEADER_CHANGE_LANGUAGE', 'Program Dilini Değiştir'),
(54, '58fe65f3-72bb-4b1d-af87-0515b81aeeb2', 1, 'rou', 'MENU_HEADER_CHANGE_LANGUAGE', 'Schimbați Limbajul Programului'),
(55, '335ba6bb-6537-485a-a7b0-f0ecd52f5fc2', 1, 'eng', 'PAGES_LANGUAGES_MENU_ITEM_LIST', 'List'),
(56, '87a9a65f-89e1-4747-83b9-cc20df77ad91', 1, 'tur', 'PAGES_LANGUAGES_MENU_ITEM_LIST', 'Liste'),
(57, 'a871c475-e6c0-4951-b57d-e853af5e3e1d', 1, 'rou', 'PAGES_LANGUAGES_MENU_ITEM_LIST', 'Liste'),
(58, 'c452d745-2013-4693-8197-a98a1d0cee0b', 1, 'eng', 'PAGES_LANGUAGES_MENU_ITEM_ADD', 'Add'),
(59, '5dd92ba9-ea9a-41ca-9d37-f3d7a846f521', 1, 'tur', 'PAGES_LANGUAGES_MENU_ITEM_ADD', 'Ekle'),
(60, '8daad18a-c5ce-42cd-a0ed-0c75df12277b', 1, 'rou', 'PAGES_LANGUAGES_MENU_ITEM_ADD', 'Adăuga'),
(61, 'b587bfe8-c57c-4364-a8b6-e7814f2e9192', 1, 'eng', 'PAGES_NOTFOUND_TITLE', 'Not Found!'),
(62, 'aff8f3bd-5fb8-4b57-b2f8-c6165b476bc1', 1, 'tur', 'PAGES_NOTFOUND_TITLE', 'Bulunamadı!'),
(63, '593ebacc-15ea-460b-aef4-2b91453c4985', 1, 'rou', 'PAGES_NOTFOUND_TITLE', 'Nu a fost găsit'),
(64, '1ef4941a-07c0-4ab8-b71b-d8ecc3db8db9', 1, 'eng', 'PAGES_NOTFOUND_DESCRIPTION', 'The content you are looking for cannot be found!'),
(65, '3139b30f-c595-4f9d-aab2-fd79d1052983', 1, 'tur', 'PAGES_NOTFOUND_DESCRIPTION', 'Aradığınız içerik bulunamadı!'),
(66, '0d3f7fc8-3ebf-477e-af55-7748db1dfe51', 1, 'rou', 'PAGES_NOTFOUND_DESCRIPTION', 'Conținutul pe care îl căutați nu a fost găsit!!'),
(67, '9895eaf6-67db-4426-afd0-a468fd36768b', 1, 'eng', 'PAGES_TRANSLATION_MENU_ITEM_ADD', 'Add'),
(68, 'f2f1fef5-d83b-4c72-a541-2bbb293819b4', 1, 'tur', 'PAGES_TRANSLATION_MENU_ITEM_ADD', 'Ekle'),
(69, 'f9fbed83-2aa2-4c68-b30e-c8669e7040ed', 1, 'rou', 'PAGES_TRANSLATION_MENU_ITEM_ADD', 'Adăuga'),
(70, 'a4f7037a-2919-4d1e-a4ae-ae21d912845c', 1, 'eng', 'FORM_LABEL_ISO_CODE', 'ISO Code'),
(71, '070befcb-bcb3-4453-9b8d-a93a960e0f3c', 1, 'tur', 'FORM_LABEL_ISO_CODE', 'ISO Kodu'),
(72, 'bab8cd27-a508-41aa-86de-90ec1233bb83', 1, 'rou', 'FORM_LABEL_ISO_CODE', 'Cod ISO'),
(73, '38b8fdd3-19a0-4ba9-a9fb-dceb068c412f', 1, 'eng', 'FORM_LABEL_LANGUAGE', 'Language'),
(74, '0657d24c-fd4f-4c27-a0f9-5d3e640c8642', 1, 'tur', 'FORM_LABEL_LANGUAGE', 'Dil'),
(75, 'aa042cc0-a760-48e1-9d3d-40e316a31cae', 1, 'rou', 'FORM_LABEL_LANGUAGE', 'Limbă'),
(76, '4f9f795f-87ec-4353-b95a-95f9118e625a', 1, 'eng', 'FORM_LABEL_ACTIVE', 'Active'),
(77, 'd43ae276-8258-4bb7-b34b-7b3a80da3d88', 1, 'tur', 'FORM_LABEL_ACTIVE', 'Aktif'),
(78, '5448daaa-08c2-4ecf-ad38-9a2bf48f774a', 1, 'rou', 'FORM_LABEL_ACTIVE', 'Activ'),
(79, '139b9f05-ee96-473b-89fe-4303d619db4c', 1, 'eng', 'ANCHOR_LINK_EDIT', 'Edit'),
(80, '105efdca-139a-4505-9752-5cbcb5155098', 1, 'tur', 'ANCHOR_LINK_EDIT', 'Düzenle'),
(81, '5e2e8849-2ef8-4a93-b0bb-856e1b8b5706', 1, 'rou', 'ANCHOR_LINK_EDIT', 'Edita'),
(82, '33f61c7c-423e-4056-93ad-0f3953e25cdf', 1, 'eng', 'ANCHOR_LINK_DETAIL', 'Detail'),
(83, 'f350d166-89eb-46fe-a73e-795e4c022289', 1, 'tur', 'ANCHOR_LINK_DETAIL', 'Detay'),
(84, 'b13d2e14-7bac-4903-93ca-c258a5c28188', 1, 'rou', 'ANCHOR_LINK_DETAIL', 'Detaliu'),
(85, '4ea60f4c-3208-4ac4-a02a-451f9a548cb1', 1, 'eng', 'FORM_LABEL_SAVE', 'Save'),
(86, 'd4eef039-9069-4d98-8560-5f5a425715fb', 1, 'tur', 'FORM_LABEL_SAVE', 'Kaydet'),
(87, '16034a46-7429-4182-b9fe-483956adddc5', 1, 'rou', 'FORM_LABEL_SAVE', 'Salvează'),
(88, '58bfbd04-9d0d-426d-bbe4-d0adb3f6df88', 1, 'eng', 'MENU_HEADER_CHANGE_LANGUAGE_ERROR', 'There was an error during changing language. Please try again.'),
(89, '56b135c6-d9b6-45e7-8b4a-fd5c2227d517', 1, 'tur', 'MENU_HEADER_CHANGE_LANGUAGE_ERROR', 'Dil değiştirme sırasında bir hata oluştu. Lütfen tekrar deneyiniz.'),
(90, 'a7d55846-d12c-4100-9f8b-a314ea063c07', 1, 'rou', 'MENU_HEADER_CHANGE_LANGUAGE_ERROR', 'A apărut o eroare la schimbarea limbii. Vă rugăm să încercați din nou.'),
(91, 'e3796267-a1ed-4b37-bb70-39ae352c92e9', 1, 'eng', 'PAGES_CHANGE_LANGUAGE_SUCCESS', 'Application language has been changed successfully.'),
(92, 'd69a9c5f-6efa-478a-9327-252965f00628', 1, 'tur', 'PAGES_CHANGE_LANGUAGE_SUCCESS', 'Program dili başarı ile değiştirildi.'),
(93, '2f1d309a-b581-4c39-b6a7-ded11136c5d4', 1, 'rou', 'PAGES_CHANGE_LANGUAGE_SUCCESS', 'Limba aplicației a fost schimbată cu succes.'),
(94, '33c74007-b4f6-43b0-951b-07eac7e71d39', 0, 'eng', 'WEBSITE_PAGES_CONTACT', 'Contact'),
(95, '1dd75581-e885-474e-a922-41cd36b5b4ac', 0, 'tur', 'WEBSITE_PAGES_CONTACT', 'İletişim'),
(96, 'd9c56a8a-da3c-4ac9-80c4-b640b6c22f86', 0, 'rou', 'WEBSITE_PAGES_CONTACT', 'Comunicare'),
(97, 'ae5053dc-b84d-4287-839b-bdf88f27dfcc', 1, 'eng', 'PAGE_TRANSLATIONS_BUTTON_UPDATE', 'Update'),
(98, '8f29f6af-9ab0-424f-be3d-c54dd5c1630e', 1, 'tur', 'PAGE_TRANSLATIONS_BUTTON_UPDATE', 'Güncelle'),
(99, '0f2b1aea-2682-4211-adef-b3b1a5acd647', 1, 'rou', 'PAGE_TRANSLATIONS_BUTTON_UPDATE', 'Actualizare');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` char(31) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` char(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uuid` (`uuid`),
  ADD KEY `iso_code` (`iso_code`),
  ADD KEY `active` (`active`),
  ADD KEY `protected` (`protected`),
  ADD KEY `system` (`system`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `level` (`level`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `iso_code_3` (`iso_code`,`translation_key`),
  ADD KEY `uuid` (`uuid`),
  ADD KEY `translation_key` (`translation_key`),
  ADD KEY `iso_code` (`iso_code`),
  ADD KEY `system` (`system`),
  ADD KEY `iso_code_2` (`iso_code`,`translation_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uuid` (`uuid`),
  ADD KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
