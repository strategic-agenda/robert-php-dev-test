// Component to allow users to add/edit translations to a translation unit.

import React, { useState } from 'react';
import api from './config/api';

const TranslationForm = ({ onUnitAdded }) => {
  const [newUnit, setNewUnit] = useState({ source_text: '', target_text: '' });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleAddUnit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      await api.post('/translations.php', newUnit);
      setNewUnit({ source_text: '', target_text: '' });
      if (onUnitAdded) {
        onUnitAdded();
      }
    } catch (error) {
      console.error('Error adding unit:', error);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="mb-8 bg-white rounded-2xl shadow-md p-6 transform transition-all duration-300 hover:shadow-lg">
      <h2 className="text-xl font-bold mb-4 gradient-text">Add New Translation</h2>
      <form onSubmit={handleAddUnit} className="space-y-4">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label htmlFor="source_text" className="block text-sm font-medium text-gray-700 mb-1">
              Source Text
            </label>
            <input
              id="source_text"
              type="text"
              placeholder="Enter source text"
              value={newUnit.source_text}
              onChange={(e) => setNewUnit({ ...newUnit, source_text: e.target.value })}
              className="input-field"
              required
            />
          </div>
          <div>
            <label htmlFor="target_text" className="block text-sm font-medium text-gray-700 mb-1">
              Target Text
            </label>
            <input
              id="target_text"
              type="text"
              placeholder="Enter translation"
              value={newUnit.target_text}
              onChange={(e) => setNewUnit({ ...newUnit, target_text: e.target.value })}
              className="input-field"
            />
          </div>
        </div>
        <div className="flex justify-end mt-4">
          <button
            type="submit"
            disabled={isSubmitting}
            className={`btn-primary flex items-center ${isSubmitting ? 'opacity-75 cursor-not-allowed' : ''}`}
          >
            {isSubmitting ? (
              <>
                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Adding...
              </>
            ) : (
              'Add Translation'
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

export default TranslationForm;
