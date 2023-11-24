// Component to display a list of translation units and their translations.
import React, { useState } from 'react';
 
const TranslationItem = ({ translation, onUpdate, onDelete }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [text, setText] = useState(translation.text);
  
    const handleUpdate = async () => { 
        try {
            const response = await fetch('http://localhost:3000/api/translations.php', {
                method: 'UPDATE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    text: translation.text, 
                    id: translation.id
                }), 
            })

            if (!response.ok) {
                throw new Error('Failed to update translation unit')
            }

            onUpdate(translation.id, { ...translation, text });
            setIsEditing(!isEditing); 
        } catch (error) {
            console.error('Error:', error)
        }
    };
    
    const handleDelete = async () => {
        try {
            const response = await fetch(`http://localhost:3000/api/translations.php?id=${translation.id}`, {
                method: 'DELETE',
            })

            if (response.ok) {
                onDelete(translation.id)
            } else {
                console.error('Error deleting translation')
            }
        } catch (error) {
            console.error('Error:', error)
        }
    }
  
    return (
      <li>
        {isEditing ? (
          <input
            type="text"
            value={text}
            onChange={(e) => setText(e.target.value)}
          />
        ) : (
          <span>{translation.text}</span>
        )}
        <button onClick={handleUpdate}>
          {isEditing ? 'Save' : 'Edit'}
        </button>
        <button onClick={handleDelete}>Delete</button>
      </li>
    );
};

const TranslationList = ({ translations, onUpdate, onDelete }) => {
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

export default TranslationList