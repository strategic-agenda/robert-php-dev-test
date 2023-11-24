// Component to allow users to add/edit translations to a translation unit.
import React, { useState } from 'react';

const TranslationForm = ({ onCreate }) => {
    const [text, setText] = useState('');
    const [translated_text, settranslated_text] = useState('');

    async function AddTranslation(text, translated_text) {
   
        const response = await fetch('http://localhost:8000/api/translations.php/Add', {
            method: 'POST',
            headers: {
                // 'Content-Type': 'application/json',
                'Content-Type': 'text/plain',
            },
            body: JSON.stringify({
                'text'   : text,
                'trans_text' : translated_text,
                'langId' : 1 // The Language Id We Are Currently Working With
            })
        })
        
        console.log(response); 
        
        if (!response.ok) {
          throw new Error('Failed to add translation')
        }
        return response.json()
    }
    
    const handleSubmit = async (e) => {
      e.preventDefault();
 
      const newTranslation = await AddTranslation(text, translated_text);
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
        <input
          type="text"
          value={translated_text}
          onChange={(e) => settranslated_text(e.target.value)}
          placeholder="Enter translation text"
        />
        <button type="submit">Create</button>
      </form>
    );
};


export default TranslationForm