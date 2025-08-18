import React, { useState, useEffect } from 'react';

const TranslationForm = ({ onSubmit, initialData }) => {
  const [form, setForm] = useState(
    initialData || {
      source_text: '',
      translated_text: '',
      language_from: 'en',
      language_to: 'pt'
    }
  );

  useEffect(() => {
    if (initialData) {
      setForm(initialData);
    } else {
      setForm({
        source_text: '',
        translated_text: '',
        language_from: 'en',
        language_to: 'pt'
      });
    }
  }, [initialData]);

  const handleChange = e =>
    setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(form);
  };

  return (
    <form onSubmit={handleSubmit}>
      <h3>{initialData ? 'Edit Translation' : 'Add Translation'}</h3>
      <input
        name="source_text"
        placeholder="Source Text"
        value={form.source_text}
        onChange={handleChange}
        required
      />
      <input
        name="translated_text"
        placeholder="Translated Text"
        value={form.translated_text}
        onChange={handleChange}
        required
      />
      <input
        name="language_from"
        placeholder="From"
        value={form.language_from}
        onChange={handleChange}
      />
      <input
        name="language_to"
        placeholder="To"
        value={form.language_to}
        onChange={handleChange}
      />
      <button type="submit">{initialData ? 'Update' : 'Create'}</button>
    </form>
  );
};

export default TranslationForm;