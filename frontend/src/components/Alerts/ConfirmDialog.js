import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogActions from '@mui/material/DialogActions';
import DialogContent from '@mui/material/DialogContent';
import DialogContentText from '@mui/material/DialogContentText';
import DialogTitle from '@mui/material/DialogTitle';
import Button from '@mui/material/Button';

export default function ConfirmDialog({ open, onClose, onConfirm, title, content }) {
  return (
    <Dialog open={open} onClose={onClose}>
      <DialogTitle>{title || "Are you sure?"}</DialogTitle>
      <DialogContent>
        <DialogContentText>
          {content || "This action cannot be undone."}
        </DialogContentText>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Cancel</Button>
        <Button onClick={onConfirm} autoFocus color="error">
          Delete
        </Button>
      </DialogActions>
    </Dialog>
  );
}
