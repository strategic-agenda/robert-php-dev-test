// Component to allow users to add/edit translations to a translation unit.
import React, { useState } from 'react';

const TranslationForm = ({ onCreate }) => {
    const [text, setText] = useState('');
    const [translated_text, settranslated_text] = useState('');

    function AddTranslation(text, translated_text) {
    
        fetch('http://localhost:8000/api/translations.php/Add', {
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
        }).then((response) => {
          if (!response.ok) {
            throw new Error('Network response was not ok')
          }
          console.log(response);
          return response.json();
        }).then((data) => { 
          console.log(data);
          if (data.length != 0 && typeof data != 'boolean' ) { 
            onCreate({ 
              id : data, 
              translated_text : translated_text,  
              unit_text : text
            });
          }
        }).catch((error) => {
          console.error('Fetch error:', error)
        })
        
    }
    
    const handleSubmit = async (e) => {
      e.preventDefault();

      AddTranslation(text, translated_text);
      // const newTranslation = await AddTranslation(text, translated_text);
      // onCreate(newTranslation);
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