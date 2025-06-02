
import React, { useEffect, useState } from 'react';
import {
  Dialog, DialogTitle, DialogContent,
  List, ListItem, ListItemText, CircularProgress
} from '@mui/material';

const API_URL = 'http://localhost:8000/api/translations.php';

export default function TranslationHistoryModal({ open, onClose, translationId }) {
  const [history, setHistory] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (open && translationId) {
      setLoading(true);
      fetch(`${API_URL}?history=${translationId}`)

        .then(res => res.json())
        .then(data => setHistory(data))
        .finally(() => setLoading(false));
    }
  }, [open, translationId]);

  return (
    <Dialog open={open} onClose={onClose} fullWidth>
      <DialogTitle>Translation History</DialogTitle>
      <DialogContent>
        {loading ? <CircularProgress /> : (
          <List>
            {history.map((entry, idx) => (
              <ListItem key={idx}>
                <ListItemText
                  primary={`Version ${entry.version}`}
                  secondary={entry.previous_text}
                />
              </ListItem>
            ))}
            {history.length === 0 && <p>No previous versions found.</p>}
          </List>
        )}
      </DialogContent>
    </Dialog>
  );
}
