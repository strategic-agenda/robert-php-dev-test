import React, { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import useTranslationUnitStore, { type TranslationUnit } from '../../../store/translationUnits';
import TranslationForm from './TranslationForm';

const schema = yup.object({
  source_text: yup.string().required('Source text is required'),
  source_language_id: yup.number().required('Language is required').positive(),
});

interface TranslationUnitFormProps {
  unit?: TranslationUnit;
  onSuccess?: () => void;
  onCancel?: () => void;
}

const TranslationUnitForm: React.FC<TranslationUnitFormProps> = ({ 
  unit, 
  onSuccess, 
  onCancel 
}) => {
  const { 
    languages, 
    loading, 
    createUnit, 
    updateUnit,
    translations,
    fetchTranslations,
  } = useTranslationUnitStore();
  
  const [selectedLanguageId, setSelectedLanguageId] = useState<number | null>(null);
  
  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm({
    resolver: yupResolver(schema),
    defaultValues: {
      source_text: unit?.source_text || '',
      source_language_id: unit?.source_language_id || languages[0]?.id || 0,
    },
  });

  useEffect(() => {
    if (unit?.id) {
      fetchTranslations(unit.id);
    }
  }, [unit?.id, fetchTranslations]);

  useEffect(() => {
    reset({
      source_text: unit?.source_text || '',
      source_language_id: unit?.source_language_id || languages[0]?.id || 0,
    });
  }, [unit, languages, reset]);

  const onSubmit = async (data: any) => {
    try {
      if (unit) {
        await updateUnit(unit.id, data);
      } else {
        await createUnit(data);
      }
      onSuccess?.();
    } catch (err) {

    }
  };

  const handleAddTranslation = (languageId: number) => {
    setSelectedLanguageId(languageId);
  };

  const handleTranslationSuccess = () => {
    setSelectedLanguageId(null);
    if (unit?.id) {
      fetchTranslations(unit.id);
    }
  };

  return (
    <div className="space-y-4">
      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4 border-b border-gray-200 pb-4">
        <div>
          <label className="block mb-1">Source Language</label>
          <select
            {...register('source_language_id')}
            disabled={!!unit}
            className={`w-full p-2 border  border-gray-200 rounded ${
              errors.source_language_id ? 'border-red-500' : ''
            } ${!!unit ? 'bg-gray-50' : ''}`}
          >
            {languages.map((lang) => (
              <option key={lang.id} value={lang.id}>
                {lang.name} ({lang.code})
              </option>
            ))}
          </select>
          {errors.source_language_id && (
            <p className="text-red-500 text-sm">{errors.source_language_id.message}</p>
          )}
        </div>

        <div>
          <label className="block mb-1">Source Text *</label>
          <textarea
            {...register('source_text')}
            className={`w-full p-2 border border-gray-200 rounded ${
              errors.source_text ? 'border-red-500' : ''
            }`}
            rows={3}
          />
          {errors.source_text && (
            <p className="text-red-500 text-sm">{errors.source_text.message}</p>
          )}
        </div>

        <div className="flex justify-end space-x-2">
          {onCancel && (
            <button
              type="button"
              onClick={onCancel}
              className="px-4 py-2 border border-gray-200 rounded"
              disabled={loading}
            >
              Cancel
            </button>
          )}
          <button
            type="submit"
            className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            disabled={loading}
          >
            {loading ? 'Processing...' : unit ? 'Update' : 'Create'}
          </button>
        </div>
      </form>

      {unit?.id && (
        <div>
          <h3 className="font-medium mb-2">Translations</h3>
          
          {translations[unit.id]?.map((translation) => (
            <div key={translation.id} className="mb-2 p-2 border border-gray-100 rounded">
              <div className="flex justify-between">
                <span className="font-medium">
                  {languages.find(l => l.id === translation.target_language_id)?.name}
                </span>
                <span className="text-gray-500 text-sm">
                  {new Date(translation.updated_at).toLocaleString()}
                </span>
              </div>
              <p className="mt-1">{translation.translated_text}</p>
              <div className="flex space-x-2 mt-1">
                <button 
                  onClick={() => handleAddTranslation(translation.target_language_id)}
                  className="text-sm text-blue-500"
                >
                  Edit
                </button>
              </div>
            </div>
          ))}

          <div className="mt-4">
            {selectedLanguageId ? (
              <TranslationForm
                unitId={unit.id}
                languageId={selectedLanguageId}
                existingTranslation={
                  translations[unit.id]?.find(
                    t => t.target_language_id === selectedLanguageId
                  )
                }
                onSuccess={handleTranslationSuccess}
                onCancel={() => setSelectedLanguageId(null)}
              />
            ) : (
              <div className="flex flex-wrap gap-2">
                {languages
                  .filter(lang =>
                    lang.id !== unit.source_language_id &&
                    !translations[unit.id]?.some(t => t.target_language_id === lang.id)
                  )
                  .map(lang => (
                    <button
                      key={lang.id}
                      onClick={() => handleAddTranslation(lang.id)}
                      className="px-3 py-1 text-sm border rounded hover:bg-gray-50"
                    >
                      Add {lang.name} translation
                    </button>
                  ))}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default TranslationUnitForm;