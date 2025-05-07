import { useState, useEffect } from 'react';
import API_BASE_URL from './config/api';

const TranslationForm = ({ unitId, editId, languages, onClose, onSave }) => {
  const [translated_text, setTranslatedText] = useState('');
  const [translated_language_id, setTranslatedLanguageId] = useState('');
  const [loading, setLoading] = useState({ fetch: false, save: false });
  const [errors, setErrors] = useState({ translated_text: '', translated_language_id: '' });

  useEffect(() => {
    const fetchTranslation = async () => {
      if (editId) {
        setLoading(prev => ({ ...prev, fetch: true }));
        try {
          const res = await fetch(`${API_BASE_URL}translations.php?id=${editId}`);
          const data = await res.json();
          if (data) {
            setTranslatedText(data.translated_text);
            setTranslatedLanguageId(data.translated_language_id);
          }
        } catch (error) {
          console.error('Error fetching translation:', error);
        } finally {
          setLoading(prev => ({ ...prev, fetch: false }));
        }
      }
    };

    fetchTranslation();
  }, [editId]);

  const handleSave = async () => {
    const textError = !translated_text.trim() ? 'Translation text is required.' : '';
    const langError = !translated_language_id ? 'Language selection is required.' : '';
    setErrors({ translated_text: textError, translated_language_id: langError });
    if (textError || langError) return;

    setLoading(prev => ({ ...prev, save: true }));

    const url = editId
      ? `${API_BASE_URL}translations.php?id=${editId}`
      : `${API_BASE_URL}translations.php?translation_unit_id=${unitId}`;
    const method = editId ? 'PUT' : 'POST';

    try {
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ translated_text, translated_language_id }),
      });

      if (res.ok) {
        onSave();
        onClose();
      }
    } catch (error) {
      console.error('Error saving translation:', error);
    } finally {
      setLoading(prev => ({ ...prev, save: false }));
    }
  };

  return (
    <div className="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50">
      <div className="bg-white p-6 rounded shadow-lg w-full max-w-lg">
        <h3 className="text-2xl font-semibold mb-4">
          {editId ? 'Edit Translation' : 'Add Translation'}
        </h3>

        {loading.fetch ? (
          <div className="text-center py-8">
            <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-gray-800 mx-auto mb-4" />
            <p className="text-gray-600">Loading translation...</p>
          </div>
        ) : (
          <>
            <div className="mb-4">
              <input
                type="text"
                name="translated_text"
                value={translated_text}
                onChange={(e) => {
                  setErrors(prev => ({ ...prev, translated_text: '' }));
                  setTranslatedText(e.target.value);
                }}
                placeholder="Enter translation text"
                className={`w-full px-4 py-2 border rounded ${errors.translated_text ? 'border-red-500' : 'border-gray-300'}`}
              />
              {errors.translated_text && <p className="text-red-500 text-sm mt-1">{errors.translated_text}</p>}
            </div>

            <div className="mb-4">
              <select
                name="translated_language_id"
                value={translated_language_id}
                onChange={(e) => {
                  setErrors(prev => ({ ...prev, translated_language_id: '' }));
                  setTranslatedLanguageId(e.target.value);
                }}
                className={`w-full px-4 py-2 border rounded ${errors.translated_language_id ? 'border-red-500' : 'border-gray-300'}`}
              >
                <option value="">Select Language</option>
                {languages.map(lang => (
                  <option key={lang.id} value={lang.id}>{lang.name}</option>
                ))}
              </select>
              {errors.translated_language_id && <p className="text-red-500 text-sm mt-1">{errors.translated_language_id}</p>}
            </div>

            <div className="flex justify-end space-x-4">
              <button
                onClick={onClose}
                className="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700"
                disabled={loading.save}
              >
                Cancel
              </button>
              <button
                onClick={handleSave}
                className="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 disabled:opacity-60"
                disabled={loading.save}
              >
                {loading.save
                  ? (editId ? 'Updating...' : 'Saving...')
                  : (editId ? 'Update' : 'Add') + ' Translation'}
              </button>
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default TranslationForm;
