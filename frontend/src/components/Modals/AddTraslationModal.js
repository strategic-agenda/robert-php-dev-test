import React, { useState, useEffect } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  MenuItem,
} from "@mui/material";

const LANG_API_URL = "http://localhost:8000/api/languages.php";
const API_URL = "http://localhost:8000/api/translations.php";

export default function AddTranslationModal({ open, onClose, onAdd }) {
  const [languages, setLanguages] = useState([]);
  const [form, setForm] = useState({
    source_text: "",
    source_lang_id: "",
    target_lang_id: "",
    translated_text: "",
  });

  useEffect(() => {
    fetch(LANG_API_URL)
      .then((res) => res.json())
      .then((data) => setLanguages(data));
  }, []);

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async () => {
    try {
      const res = await fetch(API_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          source_text: form.source_text,
          source_lang_id: parseInt(form.source_lang_id),
        }),
      });

      const data = await res.json();
      if (!res.ok) throw new Error(data.error || "Failed to add unit");

      await fetch(API_URL, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          unit_id: data.unit_id,
          target_lang_id: parseInt(form.target_lang_id),
          translated_text: form.translated_text,
        }),
      });

      onAdd();
      onClose();
      setForm({
        source_text: "",
        source_lang_id: "",
        target_lang_id: "",
        translated_text: "",
      });
    } catch (err) {
      alert(err.message);
    }
  };

  return (
    <Dialog open={open} onClose={onClose}>
      <DialogTitle>Add Translation</DialogTitle>
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
          name="source_lang_id"
          label="Source Language"
          select
          fullWidth
          value={form.source_lang_id}
          onChange={handleChange}
        >
          {languages.map((lang) => (
            <MenuItem key={lang.id} value={lang.id}>
              {lang.name}
            </MenuItem>
          ))}
        </TextField>
        <TextField
          margin="dense"
          name="target_lang_id"
          label="Target Language"
          select
          fullWidth
          value={form.target_lang_id}
          onChange={handleChange}
        >
          {languages.map((lang) => (
            <MenuItem key={lang.id} value={lang.id}>
              {lang.name}
            </MenuItem>
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
        <Button onClick={handleSubmit} variant="contained">
          Add
        </Button>
      </DialogActions>
    </Dialog>
  );
}
