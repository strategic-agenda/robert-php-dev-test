// Component to allow users to add/edit translations to a translation unit.

import React, { useState, useEffect } from "react";
import { FORM_FIELDS, INITIAL_FORM_STATE } from "./config/constants";

/**
 * TranslationForm Component
 *
 * A form component for adding and editing translation units.
 * Handles form state management and submission of translation data.
 *
 * @param {Object} props Component props
 * @param {Function} props.onSubmit Callback function when form is submitted
 * @param {Object} [props.initialData] Initial form data for editing mode
 * @param {Function} [props.onCancel] Callback function when form is cancelled
 */
const TranslationForm = ({ onSubmit, initialData, onCancel }) => {
  const [formData, setFormData] = useState(INITIAL_FORM_STATE);

  /**
   * Update form data when initialData changes
   */
  useEffect(() => {
    if (initialData) {
      setFormData(initialData);
    }
  }, [initialData]);

  /**
   * Handle input field changes
   * @param {Event} e The change event
   */
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  /**
   * Handle form submission
   * @param {Event} e The submit event
   */
  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(formData);
    if (!initialData) {
      // Reset form only if it's a new translation
      setFormData(INITIAL_FORM_STATE);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="translation-form">
      <h3>{initialData ? "Edit Translation" : "Add New Translation"}</h3>

      <div className="form-group">
        <label htmlFor={FORM_FIELDS.SOURCE_TEXT}>Source Text:</label>
        <input
          type="text"
          id={FORM_FIELDS.SOURCE_TEXT}
          name={FORM_FIELDS.SOURCE_TEXT}
          value={formData[FORM_FIELDS.SOURCE_TEXT]}
          onChange={handleChange}
          required
        />
      </div>

      <div className="form-group">
        <label htmlFor={FORM_FIELDS.TARGET_TEXT}>Target Text:</label>
        <input
          type="text"
          id={FORM_FIELDS.TARGET_TEXT}
          name={FORM_FIELDS.TARGET_TEXT}
          value={formData[FORM_FIELDS.TARGET_TEXT]}
          onChange={handleChange}
          required
        />
      </div>

      <div className="form-group">
        <label htmlFor={FORM_FIELDS.SOURCE_LANGUAGE}>Source Language:</label>
        <input
          type="text"
          id={FORM_FIELDS.SOURCE_LANGUAGE}
          name={FORM_FIELDS.SOURCE_LANGUAGE}
          value={formData[FORM_FIELDS.SOURCE_LANGUAGE]}
          onChange={handleChange}
          required
        />
      </div>

      <div className="form-group">
        <label htmlFor={FORM_FIELDS.TARGET_LANGUAGE}>Target Language:</label>
        <input
          type="text"
          id={FORM_FIELDS.TARGET_LANGUAGE}
          name={FORM_FIELDS.TARGET_LANGUAGE}
          value={formData[FORM_FIELDS.TARGET_LANGUAGE]}
          onChange={handleChange}
          required
        />
      </div>

      <div className="form-actions">
        <button type="submit" className="submit-button">
          {initialData ? "Update Translation" : "Add Translation"}
        </button>
        {initialData && (
          <button type="button" onClick={onCancel} className="cancel-button">
            Cancel
          </button>
        )}
      </div>
    </form>
  );
};

export default TranslationForm;
