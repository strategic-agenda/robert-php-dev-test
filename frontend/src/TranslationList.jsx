import React, { useState } from "react";
import { translationApi } from "./api";

export function TranslationList({ units, onUnitsChange }) {
  const [editId, setEditId] = useState(null);
  const [editTranslation, setEditTranslation] = useState("");
  const [selectedLanguage, setSelectedLanguage] = useState("en");
  const [error, setError] = useState(null);
  const [historyId, setHistoryId] = useState(null);
  const [history, setHistory] = useState([]);
  const [loadingHistory, setLoadingHistory] = useState(false);

  const startEdit = (id, translation) => {
    setEditId(id);
    setEditTranslation(translation);
  };

  const saveEdit = async (id) => {
    try {
      const data = await translationApi.updateUnit(id, {
        [selectedLanguage]: editTranslation,
      });
      onUnitsChange(units.map((u) => (u.id === id ? data : u)));
      setError(null);
    } catch (err) {
      setError("Failed to update translation");
      console.error("Error updating unit:", err);
    }
  };

  const deleteUnit = async (id) => {
    try {
      await translationApi.deleteUnit(id);
      onUnitsChange(units.filter((u) => u.id !== id));
      setError(null);
    } catch (err) {
      setError("Failed to delete translation unit");
      console.error("Error deleting unit:", err);
    }
  };

  const toggleHistory = async (id) => {
    if (historyId === id) {
      setHistoryId(null);
      setHistory([]);
    } else {
      setHistoryId(id);
      setLoadingHistory(true);
      try {
        const historyData = await translationApi.getHistory(id);
        setHistory(historyData);
        setError(null);
      } catch (err) {
        setError("Failed to load history");
        console.error("Error loading history:", err);
      } finally {
        setLoadingHistory(false);
      }
    }
  };

  return (
    <div>
      {error && <div className="error">{error}</div>}
      <ul>
        {units.map((unit) => (
          <li key={unit.id}>
            <div>
              <div>
                <strong>{unit.source}</strong>
                <div>
                  <div>
                    {Object.entries(unit.translations || {}).map(
                      ([lang, text]) => (
                        <div key={lang}>
                          <span>{lang.toUpperCase()}:</span>
                          <span>{text}</span>
                        </div>
                      ),
                    )}
                  </div>
                  <button
                    onClick={() =>
                      startEdit(
                        unit.id,
                        unit.translations?.[selectedLanguage] || "",
                      )
                    }
                  >
                    Edit
                  </button>
                  <button onClick={() => deleteUnit(unit.id)}>Delete</button>
                  <button onClick={() => toggleHistory(unit.id)}>
                    {historyId === unit.id ? "Hide History" : "Show History"}
                  </button>
                  {editId === unit.id && (
                    <div>
                      <select
                        value={selectedLanguage}
                        onChange={(e) => {
                          const selectedLanguage = e.target.value;
                          setSelectedLanguage(selectedLanguage);
                          setEditTranslation(
                            unit.translations?.[selectedLanguage] ?? "",
                          );
                        }}
                      >
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                      </select>
                      <input
                        value={editTranslation}
                        onChange={(e) => setEditTranslation(e.target.value)}
                      />
                      <button onClick={() => saveEdit(unit.id)}>Save</button>
                      <button onClick={() => setEditId(null)}>Cancel</button>
                    </div>
                  )}
                  {historyId === unit.id && (
                    <div className="history-section">
                      {loadingHistory ? (
                        <div>Loading history...</div>
                      ) : (
                        <div>
                          <h4>Translation History</h4>
                          <ul>
                            {history.map((entry, index) => (
                              <li key={index}>
                                <div>Language: {entry.lang}</div>
                                <div>Old: {entry.old}</div>
                                <div>New: {entry.new}</div>
                                <div>
                                  Timestamp:{" "}
                                  {new Date(entry.timestamp).toLocaleString()}
                                </div>
                              </li>
                            ))}
                          </ul>
                        </div>
                      )}
                    </div>
                  )}
                </div>
              </div>
            </div>
          </li>
        ))}
      </ul>
    </div>
  );
}
