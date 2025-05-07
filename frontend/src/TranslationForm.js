import React, {useState} from 'react';

/**
 * Component to allow users to add/edit translations to a translation unit.
 */
const TranslationForm = ({
    unit,
    onSave,
    onCancel,
    isEditing = false,
}) => {
    // initialize form data based on whether we're editing or adding
    const [formData, setFormData] = useState({
        id: unit?.id || '',
        source_text: unit?.source_text || '',
        translations: unit?.translations ? {...unit.translations} : {},
    });

    // state for adding new translations
    const [newTransLang, setNewTransLang] = useState('');
    const [newTransText, setNewTransText] = useState('');

    /**
     * Handler for form submission
     */
    const handleSubmit = (e) => {
        e.preventDefault();

        // validate required fields
        if (!formData.id || !formData.source_text) {
            alert('ID and Source Text are required!');

            return;
        }

        onSave(formData);
    };

    /**
     * Handle source text changes
     */
    const handleSourceTextChange = (e) => {
        setFormData({...formData, source_text: e.target.value});
    };

    /**
     * Handle translation text changes
     */
    const handleTranslationTextChange = (lang, value) => {
        const updatedTranslations = {...formData.translations};
        updatedTranslations[lang] = value;
        
        setFormData({
            ...formData,
            translations: updatedTranslations,
        });
    };

    /**
     * Add a new translation to the form data
     */
    const addTranslation = () => {
        if (!newTransLang || !newTransText) {
            alert('Language code and translation text are required!');

            return;
        }

        setFormData(prev => ({
            ...prev,
            translations: {
                ...prev.translations,
                [newTransLang]: newTransText,
            },
        }));

        // clear inputs
        setNewTransLang('');
        setNewTransText('');
    };

    /**
     * Remove a translation
     */
    const removeTranslation = (lang) => {
        const updatedTranslations = {...formData.translations};

        delete updatedTranslations[lang];

        setFormData({
            ...formData,
            translations: updatedTranslations,
        });
    };

    return (
        <div className="translation-form">
            <h2>{isEditing ? 'Edit Translation Unit' : 'Add New Translation Unit'}</h2>
            <form onSubmit={handleSubmit}>

                {/* display the source text */}
                <div>
                    <label>Source Text:</label>
                    <textarea
                        value={formData.source_text}
                        onChange={handleSourceTextChange}
                        required
                    />
                </div>

                <div>
                    {/* display the list of existing translations */}
                    <h3>Translations:</h3>
                    {Object.entries(formData.translations).
                        map(([lang, text]) => (
                            <div key={lang}>
                                <strong>{lang}:</strong>
                                <input
                                    type="text"
                                    value={text}
                                    onChange={(e) => handleTranslationTextChange(lang, e.target.value)}
                                />
                                <button
                                    type="button"
                                    onClick={() => removeTranslation(lang)}
                                >
                                    Remove
                                </button>
                            </div>
                        ))
                    }

                    {/* show fields for adding a new translation */}
                    <div>
                        <input
                            type="text"
                            placeholder="Language code (e.g., es, fr)"
                            value={newTransLang}
                            onChange={(e) => setNewTransLang(e.target.value)}
                        />
                        <input
                            type="text"
                            placeholder="Translation text"
                            value={newTransText}
                            onChange={(e) => setNewTransText(e.target.value)}
                        />
                        <button
                            type="button"
                            onClick={addTranslation}
                        >
                            Add Translation
                        </button>
                    </div>
                </div>

                <div>
                    <button type="submit">Save</button>
                    <button type="button" onClick={onCancel}>Cancel</button>
                </div>
            </form>
        </div>
    );
};

export default TranslationForm;
