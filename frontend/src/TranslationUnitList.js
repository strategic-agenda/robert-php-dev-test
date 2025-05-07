import { useState, useEffect } from 'react';
import API_BASE_URL from './config/api';
import { useLanguages } from './contexts/LanguageContext';
import { FaEdit, FaTrash, FaChevronDown } from 'react-icons/fa';
import TranslationList from './TranslationList';

const TranslationUnitList = () => {
  const languages = useLanguages();
  const [units, setUnits] = useState([]);
  const [newSource, setNewSource] = useState('');
  const [newSourceLanguageId, setNewSourceLanguageId] = useState('');
  const [editSource, setEditSource] = useState('');
  const [editSourceLanguageId, setEditSourceLanguageId] = useState('');
  const [editId, setEditId] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [isDeleteConfirm, setIsDeleteConfirm] = useState(false);
  const [deleteId, setDeleteId] = useState(null);
  const [errors, setErrors] = useState({ source: '', language: '' });
  const [loading, setLoading] = useState({
    fetch: true,
    create: false,
    update: false,
    delete: false,
  });

  const fetchUnits = async () => {
    setLoading(prev => ({ ...prev, fetch: true }));
  
    try {
      const res = await fetch(`${API_BASE_URL}translation_units.php`);
      const data = await res.json();
      setUnits(data.map(u => ({ ...u, showTranslations: false })));
    } catch (error) {
      console.error('Failed to fetch translation units:', error);
    } finally {
      setLoading(prev => ({ ...prev, fetch: false }));
    }
  };  

  useEffect(() => {
    fetchUnits();
  }, []);

  const handleCreate = async () => {
    const sourceError = !newSource.trim() ? 'Source text is required.' : '';
    const languageError = !newSourceLanguageId ? 'Language selection is required.' : '';
    setErrors({ source: sourceError, language: languageError });

    if (sourceError || languageError) return;

    setLoading(prev => ({ ...prev, create: true }));

    const res = await fetch(`${API_BASE_URL}translation_units.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ source: newSource, source_language_id: newSourceLanguageId }),
    });

    if (res.ok) {
      setNewSource('');
      setNewSourceLanguageId('');
      setShowModal(false);
      setErrors({ source: '', language: '' });
      fetchUnits();
    }

    setLoading(prev => ({ ...prev, create: false }));
  };

  const handleUpdate = async () => {
    const sourceError = !editSource.trim() ? 'Source text is required.' : '';
    const languageError = !editSourceLanguageId ? 'Language selection is required.' : '';
    setErrors({ source: sourceError, language: languageError });

    if (sourceError || languageError) return;

    setLoading(prev => ({ ...prev, update: true }));

    const res = await fetch(`${API_BASE_URL}translation_units.php?id=${editId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ source: editSource, source_language_id: editSourceLanguageId }),
    });

    if (res.ok) {
      setEditSource('');
      setEditSourceLanguageId('');
      setEditId(null);
      setShowModal(false);
      setErrors({ source: '', language: '' });
      fetchUnits();
    }

    setLoading(prev => ({ ...prev, update: false }));
  };

  const handleDelete = async () => {
    setLoading(prev => ({ ...prev, delete: true }));

    const res = await fetch(`${API_BASE_URL}translation_units.php?id=${deleteId}`, {
      method: 'DELETE',
    });

    if (res.ok) {
      setUnits(units.filter(unit => unit.id !== deleteId));
      setIsDeleteConfirm(false);
      setDeleteId(null);
    }

    setLoading(prev => ({ ...prev, delete: false }));
  };

  const handleEdit = (unit) => {
    setEditSource(unit.source);
    setEditSourceLanguageId(unit.source_language_id);
    setEditId(unit.id);
    setErrors({ source: '', language: '' });
    setShowModal(true);
  };

  const handleAddNew = () => {
    setEditId(null);
    setNewSource('');
    setNewSourceLanguageId('');
    setErrors({ source: '', language: '' });
    setShowModal(true);
  };

  const toggleTranslations = (id) => {
    setUnits(units.map(u => u.id === id ? { ...u, showTranslations: !u.showTranslations } : u));
  };

  return (
    <div className="w-full px-4 mx-auto py-6">
      <div className="flex items-center justify-between mb-6">
        <h2 className="text-3xl font-bold text-gray-800">Translation Units</h2>
        <button
          onClick={handleAddNew}
          className="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition"
        >
          Add New Translation Unit
        </button>
      </div>
      {loading.fetch ? (
        <div className="flex justify-center items-center h-32">
          <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-gray-800"></div>
        </div>      
      ) : units.length > 0 ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {units.map(unit => (
            <div key={unit.id} className="p-2">
              <div className="border p-4 rounded-lg shadow-md space-y-4 bg-white">
                <div className="flex justify-between items-center">
                  <h3
                    className="text-xl font-semibold cursor-pointer flex items-center"
                    onClick={() => toggleTranslations(unit.id)}
                  >
                    <FaChevronDown
                      className={`mr-2 transition-transform duration-300 ${unit.showTranslations ? 'rotate-180' : ''}`}
                    />
                    {unit.source}
                  </h3>
                  <div className="space-x-2 min-w-[110px]">
                    <button
                      onClick={() => handleEdit(unit)}
                      className="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600"
                    >
                      <FaEdit />
                    </button>
                    <button
                      onClick={() => {
                        setIsDeleteConfirm(true);
                        setDeleteId(unit.id);
                      }}
                      className="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                    >
                      <FaTrash />
                    </button>
                  </div>
                </div>
                <p className="text-gray-600">Language: {unit.source_language_name}</p>
                {unit.showTranslations && (
                  <TranslationList
                    unitId={unit.id}
                    languages={languages}
                    onClose={() => toggleTranslations(unit.id)}
                  />
                )}
              </div>
            </div>
          ))}
        </div>
      ) : (
        <p className="text-xl text-center text-gray-500">No translation units found.</p>
      )}

      {showModal && (
        <div className="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50">
          <div className="bg-white p-6 rounded shadow-lg w-full max-w-lg">
            <h3 className="text-2xl font-semibold mb-4">
              {editId ? 'Edit Translation Unit' : 'Add Translation Unit'}
            </h3>
            <div className="mb-4">
              <input
                type="text"
                value={editId ? editSource : newSource}
                onChange={(e) => {
                  setErrors({ ...errors, source: '' });
                  editId ? setEditSource(e.target.value) : setNewSource(e.target.value);
                }}
                placeholder="Enter source text"
                className={`w-full px-4 py-2 border rounded ${errors.source ? 'border-red-500' : 'border-gray-300'}`}
              />
              {errors.source && <p className="text-red-500 text-sm mt-1">{errors.source}</p>}
            </div>
            <div className="mb-4">
              <select
                value={editId ? editSourceLanguageId : newSourceLanguageId}
                onChange={(e) => {
                  setErrors({ ...errors, language: '' });
                  editId ? setEditSourceLanguageId(e.target.value) : setNewSourceLanguageId(e.target.value);
                }}
                className={`w-full px-4 py-2 border rounded ${errors.language ? 'border-red-500' : 'border-gray-300'}`}
              >
                <option value="">Select Language</option>
                {languages.map(lang => (
                  <option key={lang.id} value={lang.id}>{lang.name}</option>
                ))}
              </select>
              {errors.language && <p className="text-red-500 text-sm mt-1">{errors.language}</p>}
            </div>
            <div className="flex justify-end space-x-4">
              <button
                onClick={() => setShowModal(false)}
                className="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700"
              >
                Cancel
              </button>
              <button
                onClick={editId ? handleUpdate : handleCreate}
                className="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 disabled:opacity-60"
                disabled={loading.create || loading.update}
              >
                {(loading.create || loading.update) ? 'Saving...' : (editId ? 'Update' : 'Add') + ' Translation Unit'}
              </button>
            </div>
          </div>
        </div>
      )}

      {isDeleteConfirm && (
        <div className="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50">
          <div className="bg-white p-6 rounded shadow-lg w-1/3">
            <h3 className="text-2xl font-semibold mb-4">
              Are you sure you want to delete this translation unit?
            </h3>
            <div className="flex justify-end space-x-4">
              <button
                onClick={() => setIsDeleteConfirm(false)}
                className="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700"
                disabled={loading.delete}
              >
                Cancel
              </button>
              <button
                onClick={handleDelete}
                className="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 disabled:opacity-60"
                disabled={loading.delete}
              >
                {loading.delete ? 'Deleting...' : 'Delete'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TranslationUnitList;
