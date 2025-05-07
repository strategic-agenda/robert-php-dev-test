import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './App.css';
import TranslationUnitList from './components/TranslationUnitList';
import TranslationForm from './components/TranslationForm';
import ConfirmationModal from './components/ConfirmationModal';

function App() {
  const [units, setUnits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showForm, setShowForm] = useState(false);
  const [editingUnit, setEditingUnit] = useState(null);
  const [confirmDelete, setConfirmDelete] = useState({
    isOpen: false,
    unitId: null
  });

  useEffect(() => {
    fetchUnits();
  }, []);

  const fetchUnits = async () => {
    try {
      const response = await axios.get('http://localhost:8080/api/units');
      const unitsData = Array.isArray(response.data) ? response.data : [];
      setUnits(unitsData);
      setLoading(false);
    } catch (err) {
      console.error('Failed to fetch translation units:', err);
      setError('Failed to fetch translation units');
      setLoading(false);
    }
  };

  const handleAddUnit = async (unitData) => {
    try {
      const response = await axios.post('http://localhost:8080/api/units', unitData);
      setUnits([response.data, ...units]);
      setShowForm(false);
    } catch (err) {
      setError('Failed to add translation unit');
    }
  };

  const handleEditUnit = async (unitData) => {
    try {
      const response = await axios.put(`http://localhost:8080/api/units/${editingUnit.id}`, unitData);
      setUnits(units.map(unit => unit.id === editingUnit.id ? response.data : unit));
      setEditingUnit(null);
    } catch (err) {
      setError('Failed to update translation unit');
    }
  };

  const handleEdit = (unit) => {
    const standardizedUnit = {
      id: unit.id,
      sourceLanguage: unit.source_language || unit.sourceLanguage,
      targetLanguage: unit.target_language || unit.targetLanguage,
      sourceText: unit.source_text || unit.sourceText,
      targetText: unit.target_text || unit.targetText,
      history: unit.history || []
    };
    setEditingUnit(standardizedUnit);
  };

  const handleCancelEdit = () => {
    setEditingUnit(null);
  };

  const handleDeleteClick = (unitId) => {
    setConfirmDelete({
      isOpen: true,
      unitId
    });
  };

  const handleConfirmDelete = async () => {
    try {
      await axios.delete(`http://localhost:8080/api/units/${confirmDelete.unitId}`);
      setUnits(units.filter(unit => unit.id !== confirmDelete.unitId));
      setConfirmDelete({ isOpen: false, unitId: null });
    } catch (err) {
      setError('Failed to delete translation unit');
    }
  };

  const handleCancelDelete = () => {
    setConfirmDelete({ isOpen: false, unitId: null });
  };

  if (loading) return <div className="loading">Loading...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="App">
      <header className="App-header">
        <h1>Computer-Assisted Translation Tool</h1>
      </header>
      <main>
        <div className="controls">
          {!showForm && !editingUnit && (
            <button className="add-button" onClick={() => setShowForm(true)}>
              Add New Translation Unit
            </button>
          )}
        </div>

        {showForm && (
          <TranslationForm 
            onSubmit={handleAddUnit} 
            onCancel={() => setShowForm(false)} 
          />
        )}

        {editingUnit && (
          <TranslationForm 
            unit={editingUnit} 
            onSubmit={handleEditUnit} 
            onCancel={handleCancelEdit} 
          />
        )}

        <TranslationUnitList 
          units={units} 
          onEdit={handleEdit} 
          onDelete={handleDeleteClick} 
        />

        <ConfirmationModal
          isOpen={confirmDelete.isOpen}
          title="Delete Translation Unit"
          message="Are you sure you want to delete this translation unit? This action cannot be undone."
          onConfirm={handleConfirmDelete}
          onCancel={handleCancelDelete}
        />
      </main>
    </div>
  );
}

export default App; 