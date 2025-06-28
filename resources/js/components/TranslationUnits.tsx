import React, { useEffect, useState } from 'react';
import axios from 'axios';

export default function TranslationUnits() {
    const [units, setUnits] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [editingId, setEditingId] = useState(null);
    const [editText, setEditText] = useState('');
    const [newUnit, setNewUnit] = useState({
        document_id: '',
        segment_index: '',
        source_text: '',
        source_locale: '',
        target_locale: ''
    });

    useEffect(() => {
        fetchUnits();
    }, []);

    const fetchUnits = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/translation-units');
            setUnits(response.data.data || response.data);
        } catch {
            setError('Failed to load translation units.');
        } finally {
            setLoading(false);
        }
    };

    const handleCreate = async () => {
        const payload = {
            document_id: parseInt(newUnit.document_id),
            segment_index: parseInt(newUnit.segment_index),
            source_text: newUnit.source_text,
            source_locale: newUnit.source_locale,
            target_locale: newUnit.target_locale
        };
        try {
            await axios.post('/api/translation-units', payload);
            setNewUnit({ document_id: '', segment_index: '', source_text: '', source_locale: '', target_locale: '' });
            fetchUnits();
        } catch {
            setError('Create failed.');
        }
    };

    const handleEditClick = (unit) => {
        setEditingId(unit.id);
        setEditText(unit.versions[0]?.translated_text || '');
    };

    const handleSave = async (unitId) => {
        try {
            await axios.put(`/api/translation-units/${unitId}`, { translated_text: editText });
            setEditingId(null);
            setEditText('');
            fetchUnits();
        } catch {
            setError('Save failed.');
        }
    };

    if (loading) return <p className="text-center">Loading...</p>;
    if (error) return <p className="text-center" style={{ color: 'red' }}>{error}</p>;

    return (
        <div className="container">
            <h2>Translation Units</h2>

            {/* New Unit Form */}
            <div className="new-unit-form">
                <input
                    type="number"
                    className="unit-input"
                    placeholder="Document ID"
                    value={newUnit.document_id}
                    onChange={e => setNewUnit({ ...newUnit, document_id: e.target.value })}
                />
                <input
                    type="number"
                    className="unit-input"
                    placeholder="Segment Index"
                    value={newUnit.segment_index}
                    onChange={e => setNewUnit({ ...newUnit, segment_index: e.target.value })}
                />
                <input
                    type="text"
                    className="unit-input"
                    placeholder="Source Text"
                    value={newUnit.source_text}
                    onChange={e => setNewUnit({ ...newUnit, source_text: e.target.value })}
                />
                <input
                    type="text"
                    className="unit-input"
                    placeholder="Source Locale"
                    value={newUnit.source_locale}
                    onChange={e => setNewUnit({ ...newUnit, source_locale: e.target.value })}
                />
                <input
                    type="text"
                    className="unit-input"
                    placeholder="Target Locale"
                    value={newUnit.target_locale}
                    onChange={e => setNewUnit({ ...newUnit, target_locale: e.target.value })}
                />
                <button className="btn btn-green" onClick={handleCreate}>
                    Create New Unit
                </button>
            </div>

            {/* Unit List */}
            <ul className="unit-list">
                {units.length === 0 && <li>No translation units available.</li>}
                {units.map(unit => (
                    <li key={unit.id} className="unit-card">
                        <div>
                            <h3>{unit.source_text}</h3>
                            <p><em>{unit.source_locale} → {unit.target_locale}</em></p>
                            <div className="latest">
                                <strong>Latest:</strong>{' '}
                                {unit.versions[0]?.translated_text || '—'}
                            </div>
                        </div>

                        {editingId === unit.id ? (
                            <div>
                <textarea
                    className="unit-textarea"
                    value={editText}
                    onChange={e => setEditText(e.target.value)}
                    placeholder="Enter translation here..."
                />
                                <div>
                                    <button className="btn btn-blue" onClick={() => handleSave(unit.id)}>Save</button>
                                    <button className="btn btn-gray" onClick={() => setEditingId(null)}>Cancel</button>
                                </div>
                            </div>
                        ) : (
                            <button className="btn btn-green" onClick={() => handleEditClick(unit)}>
                                Add / Edit Translation
                            </button>
                        )}
                    </li>
                ))}
            </ul>
        </div>
    );
}
