import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './App.css';
import TranslationUnitList from './components/TranslationUnitList';
import TranslationForm from './components/TranslationForm';

function App() {
  const [units, setUnits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showForm, setShowForm] = useState(false);
  const [editingUnit, setEditingUnit] = useState(null);

  useEffect(() => {
    fetchUnits();
  }, []);

  const fetchUnits = async () => {
    try {
      const response = await axios.get('http://localhost:8080/api/units');
      setUnits(response.data);
      setLoading(false);
    } catch (err) {
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
    setEditingUnit(unit);
  };

  const handleCancelEdit = () => {
    setEditingUnit(null);
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

        <TranslationUnitList units={units} onEdit={handleEdit} />
      </main>
    </div>
  );
}

export default App; 