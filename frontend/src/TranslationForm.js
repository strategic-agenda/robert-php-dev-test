// Component to allow users to add/edit translations to a translation unit.

import React, { useEffect, useState } from 'react';

const TranslationForm = ({ onSubmit, editData, clearEdit }) => {
  const [formData, setFormData] = useState({
    source: '',
    translated: '',
    source_language: '',
    target_language: ''
  });

  useEffect(() => {
    if (editData) {
      setFormData({
        source: editData.source || '',
        translated: editData.translated || '',
        source_language: editData.source_language || '',
        target_language: editData.target_language || ''
      });
    }
  }, [editData]);

  const handleChange = (e) => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const isEdit = editData && editData.unit_id && editData.translation_id;
    const method = isEdit ? 'PUT' : 'POST';

    const url = isEdit
      ? `http://localhost/api/translations.php?id=${editData.unit_id}&translation_id=${editData.translation_id}`
      : 'http://localhost/api/translations.php';

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });

    const result = await res.json();
    if (res.ok) {
      onSubmit(); // refresh list
      setFormData({ source: '', translated: '', source_language: '', target_language: '' });
      clearEdit(); // exit edit mode
    } else {
      alert(result.error || 'Operation failed.');
    }
  };

  return (
    <form onSubmit={handleSubmit} style={{ marginBottom: '1rem' }}>
      <input
        name="source"
        placeholder="Source"
        value={formData.source}
        onChange={handleChange}
      />
      <input
        name="translated"
        placeholder="Translated"
        value={formData.translated}
        onChange={handleChange}
      />
      <input
        name="source_language"
        placeholder="Source Lang (e.g., en)"
        value={formData.source_language}
        onChange={handleChange}
      />
      <input
        name="target_language"
        placeholder="Target Lang (e.g., fr)"
        value={formData.target_language}
        onChange={handleChange}
      />
      <button type="submit">{editData ? 'Update' : 'Add'} Translation</button>
      {editData && <button type="button" onClick={clearEdit}>Cancel</button>}
    </form>
  );
};

export default TranslationForm;


