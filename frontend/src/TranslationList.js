// Component to display a list of translation units and their translations.

import React, { useState, useEffect } from "react";
import axios from "axios";
import TranslationForm from "./TranslationForm";
import "./translations.css";
import { API_ENDPOINTS } from "./config/constants";

const TranslationList = () => {
  const [translations, setTranslations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [editingTranslation, setEditingTranslation] = useState(null);

  const fetchTranslations = async () => {
    try {
      setLoading(true);
      const response = await axios.get(API_ENDPOINTS.TRANSLATIONS);
      setTranslations(response.data);
      setError(null);
    } catch (err) {
      setError("Failed to fetch translations. Please try again later.");
      console.error("Error fetching translations:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTranslations();
  }, []);

  const handleEdit = (translation) => {
    setEditingTranslation(translation);
  };

  const handleDelete = async (id) => {
    try {
      await axios.delete(API_ENDPOINTS.TRANSLATION_BY_ID(id));
      fetchTranslations();
    } catch (err) {
      setError("Failed to delete translation. Please try again later.");
      console.error("Error deleting translation:", err);
    }
  };

  const handleFormSubmit = async (formData) => {
    try {
      if (editingTranslation) {
        await axios.put(
          API_ENDPOINTS.TRANSLATION_BY_ID(editingTranslation.id),
          formData
        );
      } else {
        await axios.post(API_ENDPOINTS.TRANSLATIONS, formData);
      }
      setEditingTranslation(null);
      fetchTranslations();
    } catch (err) {
      setError("Failed to save translation. Please try again later.");
      console.error("Error saving translation:", err);
    }
  };

  if (loading) return <div className="loading">Loading translations...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="translation-container">
      <h2>Translation Units</h2>

      <TranslationForm
        onSubmit={handleFormSubmit}
        initialData={editingTranslation}
        onCancel={() => setEditingTranslation(null)}
      />

      <div className="translation-list">
        {translations.map((translation) => (
          <div key={translation.id} className="translation-item">
            <div className="translation-content">
              <h3>{translation.source_text}</h3>
              <p>Target Text: {translation.target_text}</p>
              <p>Source Language: {translation.source_language}</p>
              <p>Target Language: {translation.target_language}</p>
            </div>
            <div className="translation-actions">
              <button
                onClick={() => handleEdit(translation)}
                className="edit-button"
              >
                Edit
              </button>
              <button
                onClick={() => handleDelete(translation.id)}
                className="delete-button"
              >
                Delete
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default TranslationList;
