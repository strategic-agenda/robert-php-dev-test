import React, { useState, useEffect } from 'react';
import {
  Container,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  MenuItem,
  Typography,
  Box
} from '@mui/material';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

function App() {
  const [units, setUnits] = useState([]);
  const [open, setOpen] = useState(false);
  const [editingUnit, setEditingUnit] = useState(null);
  const [formData, setFormData] = useState({
    sourceText: '',
    targetText: '',
    sourceLanguage: 'en',
    targetLanguage: 'es'
  });

  const languages = [
    { code: 'en', name: 'English' },
    { code: 'es', name: 'Spanish' },
    { code: 'fr', name: 'French' },
    { code: 'de', name: 'German' },
    { code: 'it', name: 'Italian' }
  ];

  useEffect(() => {
    fetchUnits();
  }, []);

  const fetchUnits = async () => {
    try {
      const response = await axios.get(`${API_URL}/translation-units`);
      setUnits(response.data);
    } catch (error) {
      console.error('Error fetching units:', error);
    }
  };

  const handleOpen = (unit = null) => {
    if (unit) {
      setEditingUnit(unit);
      setFormData({
        sourceText: unit.sourceText,
        targetText: unit.targetText,
        sourceLanguage: unit.sourceLanguage,
        targetLanguage: unit.targetLanguage
      });
    } else {
      setEditingUnit(null);
      setFormData({
        sourceText: '',
        targetText: '',
        sourceLanguage: 'en',
        targetLanguage: 'es'
      });
    }
    setOpen(true);
  };

  const handleClose = () => {
    setOpen(false);
    setEditingUnit(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editingUnit) {
        await axios.put(`${API_URL}/translation-units/${editingUnit.id}`, {
          targetText: formData.targetText
        });
      } else {
        await axios.post(`${API_URL}/translation-units`, formData);
      }
      handleClose();
      fetchUnits();
    } catch (error) {
      console.error('Error saving unit:', error);
    }
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  return (
    <Container maxWidth="lg" sx={{ mt: 4 }}>
      <Box sx={{ mb: 4, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Typography variant="h4" component="h1">
          Translation Units
        </Typography>
        <Button variant="contained" color="primary" onClick={() => handleOpen()}>
          Add New Unit
        </Button>
      </Box>

      <TableContainer component={Paper}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell>Source Text</TableCell>
              <TableCell>Target Text</TableCell>
              <TableCell>Source Language</TableCell>
              <TableCell>Target Language</TableCell>
              <TableCell>Actions</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {units.map((unit) => (
              <TableRow key={unit.id}>
                <TableCell>{unit.sourceText}</TableCell>
                <TableCell>{unit.targetText}</TableCell>
                <TableCell>{unit.sourceLanguage}</TableCell>
                <TableCell>{unit.targetLanguage}</TableCell>
                <TableCell>
                  <Button onClick={() => handleOpen(unit)}>Edit</Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>

      <Dialog open={open} onClose={handleClose}>
        <DialogTitle>
          {editingUnit ? 'Edit Translation Unit' : 'Add New Translation Unit'}
        </DialogTitle>
        <DialogContent>
          <Box component="form" onSubmit={handleSubmit} sx={{ mt: 2 }}>
            <TextField
              fullWidth
              label="Source Text"
              name="sourceText"
              value={formData.sourceText}
              onChange={handleChange}
              disabled={!!editingUnit}
              margin="normal"
            />
            <TextField
              fullWidth
              label="Target Text"
              name="targetText"
              value={formData.targetText}
              onChange={handleChange}
              margin="normal"
            />
            <TextField
              select
              fullWidth
              label="Source Language"
              name="sourceLanguage"
              value={formData.sourceLanguage}
              onChange={handleChange}
              disabled={!!editingUnit}
              margin="normal"
            >
              {languages.map((lang) => (
                <MenuItem key={lang.code} value={lang.code}>
                  {lang.name}
                </MenuItem>
              ))}
            </TextField>
            <TextField
              select
              fullWidth
              label="Target Language"
              name="targetLanguage"
              value={formData.targetLanguage}
              onChange={handleChange}
              disabled={!!editingUnit}
              margin="normal"
            >
              {languages.map((lang) => (
                <MenuItem key={lang.code} value={lang.code}>
                  {lang.name}
                </MenuItem>
              ))}
            </TextField>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose}>Cancel</Button>
          <Button onClick={handleSubmit} variant="contained" color="primary">
            {editingUnit ? 'Update' : 'Create'}
          </Button>
        </DialogActions>
      </Dialog>
    </Container>
  );
}

export default App; 