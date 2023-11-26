// Component to display a list of translation units and their translations.
import React, { useState } from 'react';

const TranslationItem = ({ translation, onUpdate, onDelete }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [text] = useState(translation.unit_text);
    const [translated_text, setTranslated_text] = useState(translation.translated_text);

    // Function to handle updating translations
    const handleUpdate = async () => {
        setIsEditing(!isEditing);
        onUpdate(translation.id, { ...translation, translated_text });

        if (isEditing) {
            try {
                const response = await fetch('http://localhost:8000/api/translations.php/Update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'text/plain',
                    },
                    body: JSON.stringify({
                        'id': translation.id,
                        'trans_text': translated_text,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Failed to update translation unit');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    };

    // Function to handle deleting translations
    const handleDelete = async () => {
        try {
            const response = await fetch(`http://localhost:8000/api/translations.php/Delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'text/plain',
                },
                body: JSON.stringify({
                    'id': translation.id,
                }),
            });

            if (response.ok) {
                onDelete(translation.id);
            } else {
                console.error('Error deleting translation');
            }
        } catch (error) {
            console.error('Error:', error);
        }
    };

    return (
        <li className='listItem'>
            <span>{text}</span>
            {isEditing ? (
                <input
                    type="text"
                    value={translated_text}
                    onChange={(e) => setTranslated_text(e.target.value)}
                />
            ) : (
                <span>{translated_text}</span>
            )}
            <button onClick={handleUpdate}>
                {isEditing ? 'Save' : 'Edit'}
            </button>
            <button onClick={handleDelete}>Delete</button>
        </li>
    );
};

const TranslationList = ({ translations, onUpdate, onDelete }) => {
    if (translations.length === 0) {
        return null; // Returning null or an appropriate component when there are no translations
    }

    return (
        <ul>
            {translations.map((translation) => (
                <TranslationItem
                    key={translation.id}
                    translation={translation}
                    onUpdate={onUpdate}
                    onDelete={onDelete}
                />
            ))}
        </ul>
    );
};

export default TranslationList;
