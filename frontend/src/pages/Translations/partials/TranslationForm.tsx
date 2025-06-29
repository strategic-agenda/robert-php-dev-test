import React from 'react';
import { useForm } from 'react-hook-form';
import { yupResolver } from '@hookform/resolvers/yup';
import * as yup from 'yup';
import useTranslationUnitStore, { type Translation } from '../../../store/translationUnits';

const schema = yup.object({
  translated_text: yup.string().required('Translation text is required'),
});

interface TranslationFormProps {
  unitId: number;
  languageId: number;
  existingTranslation?: Translation;
  onSuccess?: () => void;
  onCancel?: () => void;
}

const TranslationForm: React.FC<TranslationFormProps> = ({
  unitId,
  languageId,
  existingTranslation,
  onSuccess,
  onCancel,
}) => {
  const { 
    loading, 
    createTranslation, 
    updateTranslation,
    deleteTranslation,
    fetchTranslations,
    languages
  } = useTranslationUnitStore();
  
  const { 
    register, 
    handleSubmit, 
    reset 
  } = useForm({
    resolver: yupResolver(schema),
    defaultValues: {
      translated_text: existingTranslation?.translated_text || '',
    },
  });

  const onSubmit = async (data: { translated_text: string }) => {
    try {
      if (existingTranslation) {
        await updateTranslation(existingTranslation.id, data.translated_text);
      } else {
        await createTranslation(unitId, languageId, data.translated_text);
      }
      onSuccess?.();
    } catch (err) {
      
    }
  };

  const handleDelete = async () => {
    if (existingTranslation) {
      try {
        await deleteTranslation(existingTranslation.id);
        await fetchTranslations(unitId);
        onSuccess?.();
      } catch (err) {
        
      }
    }
  };

  const language = languages.find(l => l.id === (existingTranslation ? existingTranslation.target_language_id : languageId));

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-2 p-2 border rounded">
      <div className="font-medium">{language?.name} Translation</div>
      <textarea
        {...register('translated_text')}
        rows={3}
        className="w-full p-2 border rounded"
        placeholder={`Enter ${language?.name} translation...`}
      />
      <div className="flex justify-between items-center">
        <div className="flex space-x-2">
          {onCancel && (
            <button
              type="button"
              onClick={onCancel}
              className="px-3 py-1 text-sm border rounded"
              disabled={loading}
            >
              Cancel
            </button>
          )}
          <button
            type="submit"
            className="px-3 py-1 text-sm bg-green-500 text-white rounded hover:bg-green-600"
            disabled={loading}
          >
            {loading ? 'Saving...' : existingTranslation ? 'Update' : 'Save'}
          </button>
        </div>
      </div>
    </form>
  );
};

export default TranslationForm;