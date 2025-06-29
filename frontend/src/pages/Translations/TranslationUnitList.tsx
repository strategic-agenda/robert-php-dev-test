import React, { useEffect, useState } from 'react';
import useTranslationUnitStore, { type TranslationUnit } from '../../store/translationUnits';
import TranslationUnitForm from './partials/TranslationUnitForm';

const TranslationUnitList: React.FC = () => {
  const {
    units,
    languages,
    loading,
    error,
    fetchUnits,
    deleteUnit,
    fetchLanguages,
  } = useTranslationUnitStore();
  
  const [isCreating, setIsCreating] = useState(false);
  const [editingUnit, setEditingUnit] = useState<TranslationUnit | undefined>(undefined);

  useEffect(() => {
    const initialize = async () => {
      await Promise.all([fetchLanguages(), fetchUnits()]);
    };
    initialize();
  }, [fetchUnits, fetchLanguages]);

  const handleDelete = async (id: number) => {
    if (window.confirm('Are you sure you want to delete this unit and all its translations?')) {
      await deleteUnit(id);
    }
  };

  if (loading && !units.length && !error) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
      </div>
    );
  }

  if (error) {
    return <div className="text-red-500 p-4">{error}</div>;
  }

  return (
    <div className="container mx-auto p-4">
      <h1 className="text-2xl font-bold mb-4">Translation Units</h1>

      <div className="mb-6 flex justify-between items-center">
        <button
          onClick={() => {
            setIsCreating(true);
            setEditingUnit(undefined);
          }}
          className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
          disabled={loading}
        >
          Create New Unit
        </button>
      </div>

      {(isCreating || editingUnit) && (
        <div className="mb-6 p-4 border border-gray-200 rounded bg-white">
          <TranslationUnitForm
            unit={editingUnit}
            onSuccess={() => {
              setIsCreating(false);
              setEditingUnit(undefined);
            }}
            onCancel={() => {
              setIsCreating(false);
              setEditingUnit(undefined);
            }}
          />
        </div>
      )}

      <div className="overflow-x-auto bg-white rounded-lg shadow">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ID
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Language
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Source Text
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {units.length === 0 ? (
              <tr>
                <td colSpan={4} className="px-6 py-4 text-center text-sm text-gray-500">
                  No translation units found
                </td>
              </tr>
            ) : (
              units.map((unit) => (
                <tr key={unit.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {unit.id}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {languages.find((l) => l.id === unit.source_language_id)?.name || 'Unknown'}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                    {unit.source_text}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <button
                      onClick={() => {
                        setEditingUnit(unit);
                        setIsCreating(false);
                      }}
                      className="text-blue-600 hover:text-blue-900"
                      disabled={loading}
                    >
                      Edit
                    </button>
                    <button
                      onClick={() => handleDelete(unit.id)}
                      className="text-red-600 hover:text-red-900"
                      disabled={loading}
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default TranslationUnitList;