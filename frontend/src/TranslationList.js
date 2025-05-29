// Component to display a list of translation units and their translations.

import React, { useState, useEffect } from 'react';
import api from './config/api';
import TranslationForm from './TranslationForm';

const TranslationList = () => {
  const [units, setUnits] = useState([]);
  const [editingUnit, setEditingUnit] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchUnits();
  }, []);

  const fetchUnits = async () => {
    setIsLoading(true);
    try {
      const response = await api.get('/translations.php');
      setUnits(response.data.slice(0, 10));
    } catch (error) {
      console.error('Error fetching units:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleUpdateUnit = async (id, newTargetText) => {
    try {
      await api.put(`/translations.php?id=${id}`, { target_text: newTargetText });
      setEditingUnit(null);
      fetchUnits();
    } catch (error) {
      console.error('Error updating unit:', error);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-b from-indigo-50 to-pink-50 py-8">
      <div className="container mx-auto px-4 max-w-5xl">
        <header className="text-center mb-12">
          <h1 className="text-4xl font-bold gradient-text mb-2">
            Robert Translation Tool
          </h1>
          <p className="text-gray-600">
            Streamline your translation workflow with our intuitive interface
          </p>
        </header>

        <TranslationForm onUnitAdded={fetchUnits} />

        <div className="space-y-6">
          {isLoading ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500"></div>
            </div>
          ) : units.length === 0 ? (
            <div className="text-center py-12">
              <p className="text-gray-500 text-lg">No translations yet. Add your first one above!</p>
            </div>
          ) : (
            units.map((unit) => (
              <div
                key={unit.id}
                className="translation-card"
              >
                <div className="flex flex-col space-y-4">
                  <div>
                    <label className="text-sm font-medium text-gray-500">Source Text</label>
                    <p className="text-lg font-semibold text-gray-900">{unit.source_text}</p>
                  </div>
                  
                  {editingUnit === unit.id ? (
                    <div className="space-y-2">
                      <label className="text-sm font-medium text-gray-500">Edit Translation</label>
                      <div className="flex gap-2">
                        <input
                          type="text"
                          defaultValue={unit.target_text}
                          className="input-field"
                          onKeyDown={(e) => {
                            if (e.key === 'Enter') {
                              handleUpdateUnit(unit.id, e.target.value);
                            }
                          }}
                          autoFocus
                        />
                        <button
                          onClick={() => setEditingUnit(null)}
                          className="btn-secondary"
                        >
                          Cancel
                        </button>
                      </div>
                    </div>
                  ) : (
                    <div>
                      <label className="text-sm font-medium text-gray-500">Translation</label>
                      <div className="flex justify-between items-start mt-1">
                        <p className="text-lg text-gray-800">
                          {unit.target_text || (
                            <span className="text-gray-400 italic">Not translated yet</span>
                          )}
                        </p>
                        <button
                          onClick={() => setEditingUnit(unit.id)}
                          className="btn-success"
                        >
                          Edit
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};

export default TranslationList;
