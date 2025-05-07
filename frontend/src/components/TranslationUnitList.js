import React, { useState, useEffect } from 'react';
import axios from 'axios';

function TranslationUnitList({ units, onEdit }) {
  const [languages, setLanguages] = useState({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Fetch languages from the API
    const fetchLanguages = async () => {
      try {
        const response = await axios.get('http://localhost:8080/api/languages');
        const languageMap = {};
        response.data.forEach(lang => {
          languageMap[lang.code] = lang.name;
        });
        setLanguages(languageMap);
        setLoading(false);
      } catch (err) {
        console.error('Failed to fetch languages', err);
        setLoading(false);
      }
    };

    fetchLanguages();
  }, []);

  if (loading) {
    return <div className="loading">Loading languages...</div>;
  }
  
  if (!units.length) {
    return <div className="no-units">No translation units available</div>;
  }

  const getLanguageName = (code) => {
    return languages[code] || code;
  };

  return (
    <div className="translation-units">
      {units.map(unit => (
        <div key={unit.id} className="translation-unit">
          <div className="unit-header">
            <span className="unit-id">ID: {unit.id}</span>
            <button className="edit-button" onClick={() => onEdit(unit)}>Edit</button>
          </div>
          <div className="source-text">
            <h3>Source Text ({getLanguageName(unit.sourceLanguage)})</h3>
            <p>{unit.sourceText}</p>
          </div>
          <div className="target-text">
            <h3>Target Text ({getLanguageName(unit.targetLanguage)})</h3>
            <p>{unit.targetText}</p>
          </div>
          {unit.history && unit.history.length > 0 && (
            <div className="history">
              <h4>History</h4>
              <ul>
                {unit.history.map((entry, index) => (
                  <li key={index}>
                    {entry.target_text} <span className="timestamp">(Updated: {entry.updated_at})</span>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      ))}
    </div>
  );
}

export default TranslationUnitList; 