import React, { useState, useEffect } from "react";
import TranslationForm from "./TranslationForm";
import TranslationList from "./TranslationList.JSX";


const API_BASE_URL = `${import.meta.env.VITE_API_URL}` || 'http://localhost:8000';

const TranslationUnitManager = () => {
  const [translationUnits, setTranslationUnits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const [formMode, setFormMode] = useState("add");
  const [editingId, setEditingId] = useState(null);
  const [formData, setFormData] = useState({
    document_id: "",
    source_text: "",
    target_text: "",
    status: "new",
  });

  const [documents, setDocuments] = useState([]);

  useEffect(() => {
    fetchTranslationUnits();
    fetchDocuments();
  }, []);

  const fetchTranslationUnits = async () => {
    try {
      setLoading(true);
      console.log("Fetching from:", `${API_BASE_URL}/api/translation-units`);

      const response = await fetch(`${API_BASE_URL}/api/translation-units`);

      if (!response.ok) {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
          const errorData = await response.json();
          throw new Error(
            errorData.error || "Failed to fetch translation units"
          );
        } else {
          // For non-JSON responses
          const text = await response.text();
          console.error("Non-JSON response:", text);
          throw new Error(
            `Server returned ${response.status}: ${response.statusText}`
          );
        }
      }

      const data = await response.json();
      setTranslationUnits(data);
      setError(null);
    } catch (err) {
      setError("Error fetching translation units: " + err.message);
      console.error("Error fetching translation units:", err);
    } finally {
      setLoading(false);
    }
  };

  const fetchDocuments = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/api/documents`);
      if (!response.ok) throw new Error("Failed to fetch documents");
      const data = await response.json();

      setDocuments(data);
    } catch (err) {
      console.error("Error fetching documents:", err);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleEdit = (unit) => {
    setFormMode("edit");
    setEditingId(unit.id);
    setFormData({
      document_id: unit.document_id,
      source_text: unit.source_text,
      target_text: unit.target_text || "",
      status: unit.status,
    });
  };

  const handleCancel = () => {
    setFormMode("add");
    setEditingId(null);
    setFormData({
      document_id: "",
      source_text: "",
      target_text: "",
      status: "new",
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const endpoint =
        formMode === "add"
          ? `${API_BASE_URL}/api/translation-units`
          : `${API_BASE_URL}/api/translation-units/${editingId}`;
      const method = formMode === "add" ? "POST" : "PUT";

      const payload =
        formMode === "add"
          ? formData
          : {
              target_text: formData.target_text,
              status: formData.status,
            };

      const response = await fetch(endpoint, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || "Failed to save translation unit");
      }

      handleCancel();
      fetchTranslationUnits();
    } catch (err) {
      setError(err.message);
      console.error("Error submitting form:", err);
    }
  };

  return (
    <div className="translation-container">
      <h1>Translation Unit Manager</h1>

      {error && <div className="error-message">{error}</div>}

      <TranslationForm
        formMode={formMode}
        formData={formData}
        documents={documents}
        handleInputChange={handleInputChange}
        handleSubmit={handleSubmit}
        handleCancel={handleCancel}
      />

      <TranslationList
        translationUnits={translationUnits}
        loading={loading}
        editingId={editingId}
        handleEdit={handleEdit}
      />
    </div>
  );
};

export default TranslationUnitManager;
