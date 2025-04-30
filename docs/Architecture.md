# Structured Approach to Handle Multilingual Content in a CAT System
To handle multilingual content efficiently, we designed a scalable and relational system architecture based on Translation Units (TUs). Each TU represents a single source segment (sentence/phrase), and contains multiple translations in various target languages.

This structure ensures:
✅ One source → many translations (1:N)
✅ Language separation
✅ Efficient querying and filtering
✅ Clean deletion via cascading foreign keys

We created a single class: TranslationUnit to encapsulate all logic, including:
✅ findOrCreateUnit(): Prevents duplication of source units
✅ addTranslationToUnit(): Adds a new translation for a unit
✅ getAllUnits()	Returns all source units + their translations
✅ getUnitById($id): Returns a unit with all translations
✅ updateUnitAndTranslation(): Updates both source and a specific translation
✅ deleteUnit($id): Removes a unit and all translations

We exposed the system via a RESTful API at api/translations.php:
✅ POST	/translations.php (Add new unit + translation)
✅ GET	/translations.php (List all units)
✅ GET	/translations.php?id=1	(Get unit #1 and its translations)
✅ PUT	/translations.php?id=1&translation_id=2	(Update unit #1 and its translation #2)
✅ DELETE	/translations.php?id=1 (Delete unit #1 completely)


# Discuss the design patterns you would implement to ensure scalability, maintainability, and flexibility.

✅Language Separation (Source and target languages stored explicitly)
✅ 1:N Structure (One source supports many target translations)
✅ No Redundancy (Prevents duplication of identical source texts)
✅ Flexible API (Supports update/delete without extra joins)
✅ Cascading Deletes (Automatically handles linked data cleanup)
✅ Version-Ready (Translations can evolve by adding a version or history model later)

Encapsulates all database access logic into a single class 
✅ Clean separation of concerns
✅ Easy to mock/stub during unit testing
✅ Centralized data access layer
✅ Easier future migration to another DB

findOrCreateUnit() acts like a factory: it either retrieves an existing unit or creates a new one.
✅ Avoids duplication of source units
✅ Minimizes DB clutter

# Propose a database schema to store and manage translation units.
To manage multilingual content efficiently, we designed a normalized relational database schema:

A. translation_units
Stores the source text and its language.

B. translations
Stores each translated version of the source (one per target language).

C. translation_history
Stores historical versions for rollback

Why This Schema Works
✅ Normalized (Avoids redundancy by isolating source text)
✅ Multilingual (Allows multiple translations per source unit)
✅ Flexible (Easily supports versioning and rollback)
✅ Relational (Ensures integrity using foreign keys)
✅ Efficient Deletion (Cascade deletes all related translations with one query)


# Version Control Implementation for Translations
To implement version control for translations, we introduced a dedicated translation_history table that stores every previous version of a translated text. This allows the system to maintain a complete audit trail of changes over time. 

✅ Benefits of This Approach
Benefit	Description
-> Auditability	(Tracks every edit ever made to a translation)
-> Reversibility (Future rollback functionality is easily supported)
-> Version Tagging	(Enables version comparisons, user attribution)
-> Decoupled Design	(Keeps translations table clean and current)



