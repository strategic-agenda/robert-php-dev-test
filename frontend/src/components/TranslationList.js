// Component to display a list of translation units and their translations.
import React, { useState } from 'react';
 
const TranslationItem = ({ translation, onUpdate, onDelete }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [text, setText] = useState(translation.unit_text);
    const [translated_text, settranslated_text] = useState(translation.translated_text);
  
    const handleUpdate = async () => { 
        
        setIsEditing(!isEditing); 
        onUpdate(translation.id, { ...translation, translated_text });
        if (isEditing) {
            console.log(translation);
            try {
                const response = await fetch('http://localhost:8000/api/translations.php/Update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'text/plain',
                    },
                    body: JSON.stringify({  
                        'id': translation.id,
                        'trans_text' : translation.translated_text
                    }),
                })
    
                if (!response.ok) {
                    throw new Error('Failed to update translation unit')
                }
    
            } catch (error) {
                console.error('Error:', error)
            }
        }
        
    };
    
    const handleDelete = async () => {
        try {
            const response = await fetch(`http://localhost:8000/api/translations.php/Delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'text/plain',
                },
                body: JSON.stringify({  
                    'id': translation.id
                }),
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
      <li className='listItem'>
        <span>
            {text}
        </span>
        {isEditing ? (
          <input
            type="text"
            value={translated_text}
            onChange={(e) => settranslated_text(e.target.value)}
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
    if (translations == []) {
        return;
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

export default TranslationList