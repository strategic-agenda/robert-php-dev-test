// Component to display a list of translation units and their translations.
const TranslationList = () => {
  const [units, setUnits] = React.useState([]);
  const [selectedUnit, setSelectedUnit] = React.useState(null);
  const [showForm, setShowForm] = React.useState(false);
  const [editingTranslation, setEditingTranslation] = React.useState(null);

  React.useEffect(() => {
    apiClient.getTranslationUnits().then((data) => setUnits(data));
  }, []);

  const handleAddTranslation = async (unitId, translation) => {
    await apiClient.addTranslation(unitId, translation);
    setShowForm(false);
    const updatedUnits = await apiClient.getTranslationUnits();
    setUnits(updatedUnits);
  };

  const handleUpdateTranslation = async (unitId, targetLanguage, translation) => {
    await apiClient.updateTranslation(unitId, targetLanguage, translation);
    setEditingTranslation(null);
    const updatedUnits = await apiClient.getTranslationUnits();
    setUnits(updatedUnits);
  };

  return (
    <div className="max-w-4xl mx-auto">
      <h1 className="text-2xl font-bold mb-4">Translation Manager</h1>
      {units.map((unit) => (
        <div key={unit.id} className="border p-4 mb-4 rounded-lg">
          <h2 className="text-xl font-semibold">{unit.source_text}</h2>
          <p className="text-sm text-gray-600">Source Language: {unit.source_language}</p>
          <div className="mt-2">
            <h3 className="text-lg font-medium">Translations</h3>
            {unit.translations.map((translation) => (
              <div key={translation.target_language} className="mt-2">
                <p><strong>{translation.target_language}:</strong> {translation.target_text}</p>
                <p className="text-sm text-gray-600">
                  Translator: {translation.translator_id} | Updated: {translation.updated_at}
                </p>
                <button
                  onClick={() => {
                    setSelectedUnit(unit);
                    setEditingTranslation(translation);
                  }}
                  className="text-blue-500 hover:underline"
                >
                  Edit
                </button>
              </div>
            ))}
          </div>
          <button
            onClick={() => {
              setSelectedUnit(unit);
              setShowForm(true);
              setEditingTranslation(null);
            }}
            className="mt-2 bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600"
          >
            Add Translation
          </button>
        </div>
      ))}
      {(showForm || editingTranslation) && selectedUnit && (
        <TranslationForm
          unitId={selectedUnit.id}
          onSubmit={(translation) =>
            editingTranslation
              ? handleUpdateTranslation(selectedUnit.id, translation.target_language, translation)
              : handleAddTranslation(selectedUnit.id, translation)
          }
          existingTranslation={editingTranslation}
        />
      )}
    </div>
  );
};