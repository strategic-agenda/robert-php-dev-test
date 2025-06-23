import React from "react";

const TranslationForm = ({
  formMode,
  formData,
  documents,
  handleInputChange,
  handleSubmit,
  handleCancel,
}) => {
  return (
    <div className="translation-form-container">
      <h2>{formMode === "add" ? "Add New Translation" : "Edit Translation"}</h2>
      <form onSubmit={handleSubmit} className="translation-form">
        {formMode === "add" && (
          <>
            <div className="form-group">
              <label htmlFor="document_id">Document:</label>
              <select
                id="document_id"
                name="document_id"
                value={formData.document_id}
                onChange={handleInputChange}
                required
              >
                <option value="">Select a document</option>
                {documents.map((doc) => (
                  <option key={doc.id} value={doc.id}>
                    {doc.name}
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label htmlFor="source_text">Source Text:</label>
              <textarea
                id="source_text"
                name="source_text"
                value={formData.source_text}
                onChange={handleInputChange}
                required
              />
            </div>
          </>
        )}

        <div className="form-group">
          <label htmlFor="target_text">Target Text:</label>
          <textarea
            id="target_text"
            name="target_text"
            value={formData.target_text}
            onChange={handleInputChange}
            required={formMode === "edit"}
          />
        </div>

        <div className="form-group">
          <label htmlFor="status">Status:</label>
          <select
            id="status"
            name="status"
            value={formData.status}
            onChange={handleInputChange}
          >
            <option value="new">New</option>
            <option value="changed">Changed</option>
          </select>
        </div>

        <div className="form-buttons">
          <button type="submit" className="btn-primary">
            {formMode === "add" ? "Add Translation" : "Save Changes"}
          </button>
          {formMode === "edit" && (
            <button
              type="button"
              className="btn-secondary"
              onClick={handleCancel}
            >
              Cancel
            </button>
          )}
        </div>
      </form>
    </div>
  );
};

export default TranslationForm;
