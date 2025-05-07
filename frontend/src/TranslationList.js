import React, { useState, useEffect } from 'react';
import TranslationForm from './TranslationForm';

/**
 * Component to display a list of translation units and their translations.
 */
const TranslationList = () => {
    const [units, setUnits] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
  
    // Form control states
    const [formMode, setFormMode] = useState('none'); // 'none', 'add', 'edit'
    const [editingId, setEditingId] = useState(null);

    const API_URL = '/api/translations';

    /**
     * Fetch translation units on the component mount
     */
    useEffect(() => {
        fetchTranslationUnits();
    }, []);

    /**
     * Fetch translation units from the API
     */
    const fetchTranslationUnits = async () => {
        setLoading(true);

        try {
            const response = await fetch(API_URL);

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            const data = await response.json();

            // Limit to 10 units
            setUnits(Array.isArray(data) ? data.slice(0, 10) : []);
            setError(null);
        } catch (err) {
            setError(`Failed to fetch translation units: ${err.message}`);
        } finally {
            setLoading(false);
        }
    };

    /**
     * Handle adding a new translation unit
     */
    const handleAddUnit = async (formData) => {
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            const addedUnit = await response.json();

            // update list with new unit
            setUnits(prevUnits => {
                const newUnits = [...prevUnits, addedUnit];
                return newUnits.slice(0, 10); // Keep only 10 units
            });

            // Close the form
            setFormMode('none');
        } catch (err) {
            console.error(`Failed to add translation unit: ${err.message}`);
        }
    };

    // Handle updating an existing unit
    const handleUpdateUnit = async (formData) => {
        try {
            const response = await fetch(API_URL, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }

            const updatedUnit = await response.json();

            // Update unit in the list
            setUnits(prevUnits =>
                prevUnits.map(unit =>
                    unit.id === formData.id ? updatedUnit : unit,
                ),
            );

            // Exit edit mode
            setEditingId(null);
            setFormMode('none');
        } catch (err) {
            console.error(`Failed to update translation unit: ${err.message}`);
        }
    };
    
    // Close the current form
    const handleCancelForm = () => {
        setFormMode('none');
        setEditingId(null);
    };
    
    // Start editing a unit
    const startEditing = (unit) => {
        setEditingId(unit.id);
        setFormMode('edit');

        // Scroll to top where the form is
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // get the unit currently being edited
    const getEditingUnit = () => {
        return units.find(unit => unit.id === editingId);
    };

    // render loading state
    if (loading && units.length === 0) {
        return <div className="loading">Loading translation units...</div>;
    }

    // render error state
    if (error && units.length === 0) {
        return <div className="error">{error}</div>;
    }

    return (
        <div className="translation-list-container">
            <h1>Translation Units</h1>

            {/* form controls */}
            <div className="form-controls">
                {formMode === 'none' && (
                    <button
                        onClick={() => setFormMode('add')}
                    >
                        Add New Translation Unit
                    </button>
                )}
            </div>
    
            {/* Form Section - always in the same position above the list */}
            <div className="form-section">
                {formMode === 'add' && (
                    <TranslationForm
                        onSave={handleAddUnit}
                        onCancel={handleCancelForm}
                    />
                )}
                
                {formMode === 'edit' && editingId && (
                    <TranslationForm
                        unit={getEditingUnit()}
                        onSave={handleUpdateUnit}
                        onCancel={handleCancelForm}
                        isEditing={true}
                    />
                )}
            </div>
    
            {/* units heading */}
            <h2 className="units-heading">All Translation Units</h2>
    
            {/* display the list of translation units */}
            <div className="units-list">
                {units.length === 0 ? (
                    <p>No translation units found.</p>
                ) : (
                    units.map(unit => (
                        <div key={unit.id}>
                            <div>
                                <h3>Source Text:</h3>
                                <p>{unit.source_text}</p>
                            </div>
    
                            <div>
                                <h3>Translations:</h3>
                                {Object.keys(unit.translations).length === 0 ? (
                                    <p>No translations available</p>
                                ) : (
                                    <ul>
                                        {Object.entries(unit.translations).map(([lang, text]) => (
                                            <li key={lang}>
                                                <strong>{lang}:</strong> {text}
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
    
                            <button
                                onClick={() => startEditing(unit)}
                            >
                                Edit
                            </button>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};

export default TranslationList;
