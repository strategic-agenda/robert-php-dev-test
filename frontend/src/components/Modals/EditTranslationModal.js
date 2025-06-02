import React, { useState, useEffect } from 'react';
import {
  Dialog, DialogTitle, DialogContent, DialogActions,
  Button, TextField, MenuItem
} from '@mui/material';

const LANG_API_URL = 'http://localhost:8000/api/languages.php';
const API_URL = 'http://localhost:8000/api/translations.php';

export default function EditTranslationModal({ open, onClose, unit, onSave }) {
  const [languages, setLanguages] = useState([]);
  const [form, setForm] = useState({
    unit_id: '',
    source_text: '',
    target_lang_id: '',
    translated_text: ''
  });

  useEffect(() => {
    if (unit) {
      setForm({
        unit_id: unit.id || '',
        source_text: unit.source_text || '',
        target_lang_id: unit.target_lang_id || '',
        translated_text: unit.translated_text || ''
      });
    }
  }, [unit]);

  useEffect(() => {
    fetch(LANG_API_URL)
      .then(res => res.json())
      .then(data => setLanguages(data));
  }, []);

  const handleChange = e => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async () => {
    try {
      const res = await fetch(API_URL, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          unit_id: parseInt(form.unit_id),
          source_text: form.source_text,
          target_lang_id: parseInt(form.target_lang_id),
          translated_text: form.translated_text
        })
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Failed to update translation');

      onSave(); 
      onClose();
    } catch (err) {
      alert(err.message);
    }
  };

  return (
    <Dialog open={open} onClose={onClose}>
      <DialogTitle>Edit Translation</DialogTitle>
      <DialogContent>
        <TextField
          margin="dense"
          name="source_text"
          label="Source Text"
          fullWidth
          value={form.source_text}
          onChange={handleChange}
        />
        <TextField
          margin="dense"
          name="target_lang_id"
          label="Target Language"
          select
          fullWidth
          value={form.target_lang_id}
          onChange={handleChange}
        >
          {languages.map(lang => (
            <MenuItem key={lang.id} value={lang.id}>{lang.name}</MenuItem>
          ))}
        </TextField>
        <TextField
          margin="dense"
          name="translated_text"
          label="Translated Text"
          fullWidth
          value={form.translated_text}
          onChange={handleChange}
        />
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Cancel</Button>
        <Button onClick={handleSubmit} variant="contained">Save</Button>
      </DialogActions>
    </Dialog>
  );
}
