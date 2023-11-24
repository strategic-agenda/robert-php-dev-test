// Component to allow users to add/edit translations to a translation unit.
import React, { useState } from 'react';

const TranslationForm = ({ onCreate }) => {
    const [text, setText] = useState('');

    async function AddTranslation(text) {
        const response = await fetch('http://localhost:3000/api/translations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                text: text,
                language: 1 // The Language Id We Are Currently Working With
            })
        })
        if (!response.ok) {
          throw new Error('Failed to add translation')
        }
        return response.json()
    }
    
    const handleSubmit = async (e) => {
      e.preventDefault();
 
      const newTranslation = await AddTranslation(text);
      onCreate(newTranslation);
      setText('');
    
    };
  
    return (
      <form onSubmit={handleSubmit}>
        <input
          type="text"
          value={text}
          onChange={(e) => setText(e.target.value)}
          placeholder="Enter text to translate"
        />
        <button type="submit">Create</button>
      </form>
    );
};


export default TranslationForm