# Explaining the architecture and design decisions

# How would you structure the system to handle multilingual content efficiently?
I would use lang_code column in DB for setting language of every Translation Unit.
This way we can create any amount of Units and each of them will have language setting.
If we need same Unit, but with different language, we can just insert another row in DB table.

# Discuss the design patterns you would implement to ensure scalability, maintainability, and flexibility.
I would go with MVC pattern because it proved efficient on large scale applications and every modern framework uses it.
Models' level in our case is /backend/models directory. In contains business logic and connection to DB.
Controllers' level is /backend/api directory. It handles initial request from client passes it to Models level and returns response back to client.
Views' level is /src/app directory. It is SPA using React / Tailwind.

# Propose a database schema to store and manage translation units.
I would with classic MySQL DB. Schema contains two tables with one to many relationship.
Main table for Translation Units:
    translation_units:
    id - primary key
    unit_text  - main data of Unit
    unit_type  - enum word, sentence, paragraph
    lang_code  - multilingual implementation
    created_at - date of creation
    updated_at - date of last update

    unit_history:
    id         - primary key
    unit_id    - foreign key to translation_units table
    old_value  - storing old value
    new_value  - storing new value
    message    - brief description of change
    created_at - date of creation

# Briefly describe how you would implement version control for translations.
I would create another table connected to Translation Unit's table. Every change on Unit would insert new row in history table