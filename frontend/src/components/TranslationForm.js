import React, { useState, useEffect } from 'react';
import axios from 'axios';

function TranslationForm({ unit, onSubmit, onCancel }) {
  const isEditing = !!unit;
  const [languages, setLanguages] = useState([]);
  const [loading, setLoading] = useState(true);
  
  const [formData, setFormData] = useState({
    sourceText: unit?.sourceText || '',
    targetText: unit?.targetText || '',
    sourceLanguage: unit?.sourceLanguage || 'en',
    targetLanguage: unit?.targetLanguage || 'fr'
  });

  useEffect(() => {
    // Fetch languages from the API
    const fetchLanguages = async () => {
      try {
        const response = await axios.get('http://localhost:8080/api/languages');
        setLanguages(response.data);
        setLoading(false);
      } catch (err) {
        console.error('Failed to fetch languages', err);
        setLoading(false);
      }
    };

    fetchLanguages();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(formData);
  };

  if (loading) {
    return <div className="loading">Loading languages...</div>;
  }

  return (
    <div className="translation-form">
      <h2>{isEditing ? 'Edit Translation Unit' : 'Add New Translation Unit'}</h2>
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="sourceLanguage">Source Language</label>
          <select
            id="sourceLanguage"
            name="sourceLanguage"
            value={formData.sourceLanguage}
            onChange={handleChange}
            disabled={isEditing}
            required
          >
            {languages.map(lang => (
              <option key={lang.code} value={lang.code}>
                {lang.name}
              </option>
            ))}
          </select>
        </div>

        <div className="form-group">
          <label htmlFor="targetLanguage">Target Language</label>
          <select
            id="targetLanguage"
            name="targetLanguage"
            value={formData.targetLanguage}
            onChange={handleChange}
            disabled={isEditing}
            required
          >
            {languages.map(lang => (
              <option key={lang.code} value={lang.code}>
                {lang.name}
              </option>
            ))}
          </select>
        </div>

        <div className="form-group">
          <label htmlFor="sourceText">Source Text</label>
          <textarea
            id="sourceText"
            name="sourceText"
            value={formData.sourceText}
            onChange={handleChange}
            disabled={isEditing}
            required
          />
        </div>

        <div className="form-group">
          <label htmlFor="targetText">Target Text</label>
          <textarea
            id="targetText"
            name="targetText"
            value={formData.targetText}
            onChange={handleChange}
            required
          />
        </div>

        <div className="form-actions">
          <button type="button" className="cancel-button" onClick={onCancel}>
            Cancel
          </button>
          <button type="submit" className="submit-button">
            {isEditing ? 'Update' : 'Add'}
          </button>
        </div>
      </form>
    </div>
  );
}

export default TranslationForm; 