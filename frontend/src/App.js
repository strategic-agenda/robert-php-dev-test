import React, { useState, useEffect } from 'react';
import './App.css';

import TranslationForm from './components/TranslationForm'
import TranslationList from './components/TranslationList'

function App() {
  const [translations, setTranslations] = useState([]);
 
  useEffect(() => {
    console.log('Effect running')

    fetch('http://localhost/api/translations.php').then((response) => {
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      return response.json()
    }).then((data) => {
      console.log(data)
      setTranslations(data)
    }).catch((error) => {
      console.error('Fetch error:', error)
    })
  }, []);

  const handleCreate = (newTranslation) => {
    setTranslations([...translations, newTranslation]);
  };

  const handleUpdate = (id, updatedTranslation) => {
    const updatedTranslations = translations.map((translation) =>
      translation.id === id ? updatedTranslation : translation
    );
    setTranslations(updatedTranslations);
  };

  const handleDelete = (id) => {
    const updatedTranslations = translations.filter(
      (translation) => translation.id !== id
    );
    setTranslations(updatedTranslations);
  };
 
  return (
    <div className="App">
      <h1>Computer-Assisted Translation tool</h1>
      <TranslationForm onCreate={handleCreate} />
      <TranslationList
        translations={translations}
        onUpdate={handleUpdate}
        onDelete={handleDelete}
      />
    </div>
  );
}

export default App;
