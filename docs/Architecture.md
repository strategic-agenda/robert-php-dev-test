# Architecture for Computer-Assisted Translation (CAT) Tool

## 1. Handling Multilingual Content Efficiently

To manage multilingual content efficiently, the system should be designed to:

- **Support Multiple Languages:** Use a relational database model where languages are stored in a `languages` table, and each translation can be linked to a specific language via foreign keys (`source_language_id` and `translated_language_id`).
  
- **Separation of Concerns:** Separate the translation units and their translations to enable independent management of source texts and their translated versions.

- **Caching Mechanism:** Implement caching for frequently requested translations to reduce database load and improve performance (e.g., using Redis or in-memory caching).

---

## 2. Design Patterns for Scalability, Maintainability, and Flexibility

To ensure scalability, maintainability, and flexibility, the following design patterns will be useful:

### 2.1. **Factory Pattern**
- The Factory pattern will be used to create instances of translation units, translations, and language objects. This helps in decoupling the object creation logic and makes it easier to manage the addition of new languages, translation units, or other entities in the future.

### 2.2. **Repository Pattern**
- The Repository pattern will abstract the database layer, providing a clean API for managing translations, units, and languages. This allows for easy unit testing, separation of concerns, and cleaner code.

### 2.3. **Singleton Pattern**
- The Singleton pattern could be used for managing the database connection instance, ensuring that there is only one instance of the connection used throughout the application. This avoids the overhead of multiple database connections.

### 2.4. **Observer Pattern**
- For handling translation updates or history tracking, the Observer pattern can be used to notify relevant services (e.g., update logs, cache, etc.) whenever a translation is updated or a translation unit is modified.

### 2.5. **Command Pattern**
- The Command pattern can be used for handling translation tasks (e.g., adding, editing, or deleting translations) in a way that allows future modifications, such as retrying failed tasks or logging all commands for audit purposes.

### 2.6. **Strategy Pattern**
- For different translation strategies (e.g., manual translation, machine translation, or post-editing), we can use the Strategy pattern to allow easy extension of translation methods.

---

## 3. Database Schema Design

The proposed schema for managing translation units, translations, and their histories is designed for flexibility and scalability.

### 3.1. **Languages Table**
- Stores language metadata, including the language name and its unique code.

```sql
CREATE TABLE languages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(10) NOT NULL
);
```
### 3.2. **Translation Units Table**
- Contains the original source text and links it to the source language. Each translation unit can have multiple translations in different languages.
  
```sql
CREATE TABLE translation_units (
  id INT PRIMARY KEY AUTO_INCREMENT,
  source TEXT NOT NULL,
  source_language_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (source_language_id) REFERENCES languages(id)
);
```

### 3.3. **Translation Unit History Table**
- This table stores historical changes to the translation units. Every time a translation unit is updated, a new record is created in this table to keep track of its history.

```sql
CREATE TABLE translation_unit_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id INT NOT NULL,
    source TEXT NOT NULL,
    source_language_id INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id) ON DELETE CASCADE,
    FOREIGN KEY (source_language_id) REFERENCES languages(id)
);
```

### 3.4. **Translations Table**
- This table stores the actual translated text for each translation unit. It maintains a relationship between the translation unit, the translated text, and the target language.

```sql
CREATE TABLE translations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  translation_unit_id INT NOT NULL,
  translated_text TEXT NOT NULL,
  translated_language_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id),
  FOREIGN KEY (translated_language_id) REFERENCES languages(id)
);
```

### 3.5. **Translation History Table**
- The translation history table stores the historical versions of each translation. It allows for version control of translations and helps track changes over time.

```sql
CREATE TABLE translation_history (
  id INT PRIMARY KEY AUTO_INCREMENT,
  translation_id INT NOT NULL,
  translation_unit_id INT NOT NULL,
  translated_text TEXT NOT NULL,
  translated_language_id INT NOT NULL,
  version INT NOT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (translation_id) REFERENCES translations(id),
  FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id),
  FOREIGN KEY (translated_language_id) REFERENCES languages(id)
);
```

## 4. Version Control for Translations

Version control is essential for maintaining a historical record of changes to translations. It allows users and developers to track changes, rollback to previous versions, and maintain the integrity of the translation process.

### 4.1. Why Version Control?

While translation entries can be ordered by their `id` or `created_at` timestamp, this does not explicitly convey the version history of a translation. Adding a dedicated `version` field:

- Provides clear, sequential history.
- Makes it easy to compare different versions.
- Enables functionality like "rollback to version X."
- Avoids the ambiguity of relying solely on timestamps or auto-incremented IDs.

### 4.2. Implementation Strategy

We implement version control by maintaining a separate `translation_history` table that records each version of a translation. Any update to a translation creates a new entry in the `translation_history` table, incrementing the version number.

### 4.3. Translation History Table

```sql
CREATE TABLE translation_history (
  id INT PRIMARY KEY AUTO_INCREMENT,
  translation_id INT NOT NULL,
  translation_unit_id INT NOT NULL,
  translated_text TEXT NOT NULL,
  translated_language_id INT NOT NULL,
  version INT NOT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (translation_id) REFERENCES translations(id),
  FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id),
  FOREIGN KEY (translated_language_id) REFERENCES languages(id)
);
```

### 4.4. Versioning Logic in Code

To implement version control in code for translations, follow this logic when updating a translation:

#### Step 1: Fetch the Existing Translation
Before updating, retrieve the current version of the translation from the `translations` table.

#### Step 2: Insert a Snapshot into `translation_history`
Insert the fetched version into the `translation_history` table, incrementing the version number by one.

```php
$stmt = $this->db->prepare("SELECT * FROM translations WHERE id = ?");
$stmt->execute([$id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $this->db->prepare("
  SELECT MAX(version) as max_version FROM translation_history WHERE translation_id = ?
");
$stmt->execute([$id]);
$maxVersion = $stmt->fetchColumn();
$newVersion = $maxVersion ? $maxVersion + 1 : 1;

$stmt = $this->db->prepare("
  INSERT INTO translation_history 
  (translation_id, translation_unit_id, translated_text, translated_language_id, version) 
  VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([
  $id,
  $existing['translation_unit_id'],
  $existing['translated_text'],
  $existing['translated_language_id'],
  $newVersion
]);
```

#### Step 3: Update the Translation
Update the main translation entry with the new content.

```php
$stmt = $this->db->prepare("
  UPDATE translations SET translated_text = ?, translated_language_id = ?, updated_at = NOW() WHERE id = ?
");
$stmt->execute([$translatedText, $translatedLanguageId, $id]);
```

### 4.5. Rollback and Comparison Features

With version control implemented, the system can support several advanced features to help users manage translations more effectively:

- **View Version History:** Users can see a list of all past versions of a translation, including timestamps and the version number.
- **Compare Versions:** Implement a UI to compare two versions side-by-side, highlighting differences in translated text.
- **Rollback to Previous Version:** Allow users to restore a specific version. When a rollback is performed:
  - The selected version is copied into the main `translations` table, overwriting the current version.
  - A new history entry is recorded to maintain the rollback operation as part of the version chain.

### 4.6. Advantages of Versioning Translations

Implementing explicit version control offers several benefits:

- ✅ **Auditability:** Each change is stored permanently with a timestamp, which is crucial for accountability in professional translation workflows.
- ✅ **Collaboration:** Translators and reviewers can track changes and discuss revisions across versions.
- ✅ **Data Recovery:** Mistakes or poor edits can be undone easily by rolling back to a known good state.
- ✅ **Change Awareness:** Users can track and understand how translations evolve over time.

---

## 5. Conclusion

The architecture of the Computer-Assisted Translation (CAT) tool is designed with flexibility, maintainability, and scalability in mind. It uses clear separation of concerns, normalized relational schema, and thoughtful use of design patterns like Repository, MVC, and Factory (where needed).

Key strengths of the system include:

- ✅ Clear structure for multilingual content with normalized `languages`, `translation_units`, and `translations`.
- ✅ Robust history tracking via `translation_unit_history` and `translation_history`.
- ✅ Scalable and testable backend classes using Dependency Injection and separation of logic.
- ✅ Version control support for full audit trails and collaboration.

This architecture positions the platform for future enhancements such as:

- User authentication and roles for translators, reviewers, and admins.
- Workflow automation for translation approval.
- Integration with third-party machine translation APIs.
- Real-time collaboration via WebSockets or polling.

By applying sound software engineering principles, this CAT tool can evolve into a reliable and feature-rich system for managing translations at scale.
