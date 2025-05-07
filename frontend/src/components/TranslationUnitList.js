import React, { useState, useEffect } from 'react';
import axios from 'axios';

function TranslationUnitList({ units, onEdit, onDelete }) {
  const [languages, setLanguages] = useState({});
  const [loading, setLoading] = useState(true);
  const [historyModal, setHistoryModal] = useState({
    isOpen: false,
    unitId: null,
    history: []
  });

  // Ensure units is an array
  const safeUnits = Array.isArray(units) ? units : [];

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
  
  if (!safeUnits.length) {
    return <div className="no-units">
      <h3>No Translation Units Available</h3>
      <p>Create your first translation unit by clicking the "Add New Translation Unit" button above.</p>
    </div>;
  }

  const getLanguageName = (code) => {
    return languages[code] || code;
  };

  const formatDate = (dateString) => {
    const options = { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString(undefined, options);
  };
  
  const showHistoryModal = (unitId, unit) => {
    setHistoryModal({
      isOpen: true,
      unitId,
      history: unit.history || [],
      sourceText: unit.source_text || unit.sourceText,
      sourceLanguage: getLanguageName(unit.source_language || unit.sourceLanguage),
      targetLanguage: getLanguageName(unit.target_language || unit.targetLanguage)
    });
  };

  const closeHistoryModal = () => {
    setHistoryModal({
      isOpen: false,
      unitId: null,
      history: []
    });
  };

  return (
    <div className="translation-units">
      <h2 className="section-title">Translation Units ({safeUnits.length})</h2>
      
      <div className="table-responsive">
        <table className="units-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Source Language</th>
              <th>Source Text</th>
              <th>Target Language</th>
              <th>Target Text</th>
              <th>History</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {safeUnits.map(unit => {
              // Map backend field names (snake_case) to frontend field names (camelCase) if needed
              const id = unit.id;
              const sourceLanguage = unit.source_language || unit.sourceLanguage;
              const targetLanguage = unit.target_language || unit.targetLanguage;
              const sourceText = unit.source_text || unit.sourceText;
              const targetText = unit.target_text || unit.targetText;
              const history = unit.history || [];
              
              return (
                <tr key={id}>
                  <td>{id}</td>
                  <td>{getLanguageName(sourceLanguage)}</td>
                  <td className="text-cell">{sourceText}</td>
                  <td>{getLanguageName(targetLanguage)}</td>
                  <td className="text-cell">{targetText}</td>
                  <td>
                    {history && history.length > 0 ? (
                      <button 
                        className="history-button" 
                        onClick={() => showHistoryModal(id, unit)}
                      >
                        Show History
                      </button>
                    ) : (
                      <span className="no-history">None</span>
                    )}
                  </td>
                  <td>
                    <div className="table-actions">
                      <button className="edit-button" onClick={() => onEdit(unit)}>
                        Edit
                      </button>
                      <button className="delete-button" onClick={() => onDelete(id)}>
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
      
      {/* History Modal */}
      {historyModal.isOpen && (
        <div className="modal-overlay">
          <div className="modal history-modal">
            <div className="modal-header">
              <h2>Translation History ({historyModal.history.length} {historyModal.history.length === 1 ? 'version' : 'versions'})</h2>
            </div>
            <div className="modal-body">
              <div className="history-context">
                <div className="context-item">
                  <span className="context-label">Source Language:</span>
                  <span className="context-value">{historyModal.sourceLanguage}</span>
                </div>
                <div className="context-item">
                  <span className="context-label">Target Language:</span>
                  <span className="context-value">{historyModal.targetLanguage}</span>
                </div>
                <div className="context-item source-context">
                  <span className="context-label">Source Text:</span>
                  <div className="context-value">{historyModal.sourceText}</div>
                </div>
              </div>
              <table className="history-table">
                <thead>
                  <tr>
                    <th>Previous Translation</th>
                    <th>Updated At</th>
                  </tr>
                </thead>
                <tbody>
                  {historyModal.history.map((entry, index) => (
                    <tr key={index}>
                      <td>{entry.target_text}</td>
                      <td>{formatDate(entry.updated_at)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <div className="modal-footer">
              <button className="cancel-button" onClick={closeHistoryModal}>
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default TranslationUnitList; 