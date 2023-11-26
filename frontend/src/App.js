import React, { useState, useEffect } from 'react';
import './App.css';

import TranslationForm from './components/TranslationForm';
import TranslationList from './components/TranslationList';

function App() {
    // Define state variable translations and set it to an empty array
    const [translations, setTranslations] = useState([]);

    // Use the useEffect hook to fetch data from the server
    useEffect(() => {
        console.log('Effect running');

        fetch('http://localhost:8000/api/translations.php/Get?id=-1')
        .then((response) => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }).then((data) => {
            if (data.length !== 0 && typeof data !== 'boolean') {
                console.log(data);
                setTranslations(data);
            }
        }).catch((error) => {
            console.error('Fetch error:', error);
        });

    }, []);

    // Define a function to handle the creation of new translations
    const handleCreate = (newTranslation) => {
        console.log(newTranslation);
        if (translations) {
            setTranslations([...translations, newTranslation]);
        } else {
            setTranslations([newTranslation]);
        }
    };

    // Define a function to handle the updating of translations
    const handleUpdate = (id, updatedTranslation) => {
        const updatedTranslations = translations.map((translation) =>
            translation.id === id ? updatedTranslation : translation
        );
        setTranslations(updatedTranslations);
    };

    // Define a function to handle the deletion of translations
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
