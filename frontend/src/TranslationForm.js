// Component to allow users to add/edit translations to a translation unit.
import React, { useState, useEffect } from 'react';

export default function TranslationForm({ unit, onSave, onCancel }) {
  const [content, setContent] = useState(unit ? unit.content : '');

  useEffect(() => {
    if (unit) setContent(unit.content);
  }, [unit]);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!content.trim()) return;
    onSave({ ...unit, content });
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        value={content}
        onChange={e => setContent(e.target.value)}
        placeholder="Enter translation"
      />
      <button type="submit">Save</button>
      {onCancel && <button type="button" onClick={onCancel}>Cancel</button>}
    </form>
  );
}