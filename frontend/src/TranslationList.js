import { useState, useEffect } from 'react';
import API_BASE_URL from './config/api';
import { FaEdit, FaTrash, FaPlus } from 'react-icons/fa';
import TranslationForm from './TranslationForm';

const TranslationList = ({ unitId, languages }) => {
  const [translations, setTranslations] = useState([]);
  const [showModal, setShowModal] = useState(false);
  const [editId, setEditId] = useState(null);
  const [deleteId, setDeleteId] = useState(null);
  const [isDeleteConfirm, setIsDeleteConfirm] = useState(false);
  const [loading, setLoading] = useState({
    fetch: true,
    delete: false,
  });

  const fetchTranslations = async () => {
    setLoading(prev => ({ ...prev, fetch: true }));
  
    try {
      const res = await fetch(`${API_BASE_URL}translations.php?unit_id=${unitId}`);
      const data = await res.json();
      setTranslations(data || []);
    } catch (error) {
      console.error('Failed to fetch translations:', error);
      setTranslations([]);
    } finally {
      setLoading(prev => ({ ...prev, fetch: false }));
    }
  };
  

  useEffect(() => {
    fetchTranslations();
  }, [unitId]);

  const handleAddNew = () => {
    setEditId(null);
    setShowModal(true);
  };

  const handleEdit = (translation) => {
    setEditId(translation.id);
    setShowModal(true);
  };

  const handleDelete = async () => {
    setLoading(prev => ({ ...prev, delete: true }));

    const res = await fetch(`${API_BASE_URL}translations.php?id=${deleteId}`, {
      method: 'DELETE',
    });

    if (res.ok) {
      setIsDeleteConfirm(false);
      setDeleteId(null);
      fetchTranslations();
    }

    setLoading(prev => ({ ...prev, delete: false }));
  };

  return (
    <div className="mt-4 border-t pt-4">
      <div className="flex justify-between items-center mb-4">
        <h4 className="text-l font-semibold">Translations</h4>
        <button
          onClick={handleAddNew}
          className="bg-blue-600 text-white p-2 rounded hover:bg-blue-700"
          title="Add New Translation"
        >
          <FaPlus />
        </button>
      </div>

      {loading.fetch ? (
        <div className="flex justify-center items-center h-16">
          <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-gray-800"></div>
        </div>      
      ) : translations.length === 0 ? (
        <p className="text-gray-500 mb-4 text-l">No translations found.</p>
      ) : (
        translations.map(t => (
          <div key={t.id} className="flex justify-between items-center mb-2 border p-2 rounded">
            <div>
              <p>{t.translated_text}</p>
              <small className="text-gray-500">Language: {t.translated_language_name}</small>
            </div>
            <div className="space-x-2 min-w-[40px]">
              <button onClick={() => handleEdit(t)} className="text-yellow-600">
                <FaEdit />
              </button>
              <button onClick={() => { setDeleteId(t.id); setIsDeleteConfirm(true); }} className="text-red-600">
                <FaTrash />
              </button>
            </div>
          </div>
        ))
      )}

      {showModal && (
        <TranslationForm
          unitId={unitId}
          editId={editId}
          languages={languages}
          onClose={() => setShowModal(false)}
          onSave={fetchTranslations}
        />
      )}

      {isDeleteConfirm && (
        <div className="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50">
          <div className="bg-white p-6 rounded shadow-lg w-1/3">
            <h3 className="text-2xl font-semibold mb-4">Are you sure you want to delete this translation?</h3>
            <div className="flex justify-end space-x-4">
              <button onClick={() => setIsDeleteConfirm(false)} className="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                Cancel
              </button>
              <button
                onClick={handleDelete}
                className="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700"
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

export default TranslationList;
