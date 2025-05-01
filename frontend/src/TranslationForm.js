// Component to allow users to add/edit translations to a translation unit.
const TranslationForm = ({ unitId, onSubmit, existingTranslation = null }) => {
  const [targetLanguage, setTargetLanguage] = React.useState(existingTranslation?.target_language || 'es');
  const [targetText, setTargetText] = React.useState(existingTranslation?.target_text || '');
  const [translatorId, setTranslatorId] = React.useState(existingTranslation?.translator_id || 'translator_001');
  const [reason, setReason] = React.useState('');

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit({
      target_language: targetLanguage,
      target_text: targetText,
      translator_id: translatorId,
      ...(existingTranslation && { reason })
    });
    setTargetText('');
    setReason('');
  };

  return (
    <div className="bg-gray-100 p-4 rounded-lg mb-4">
      <h3 className="text-lg font-semibold mb-2">
        {existingTranslation ? 'Edit Translation' : 'Add Translation'}
      </h3>
      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium">Target Language</label>
          <select
            value={targetLanguage}
            onChange={(e) => setTargetLanguage(e.target.value)}
            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          >
            <option value="es">Spanish (es)</option>
            <option value="fr">French (fr)</option>
            <option value="de">German (de)</option>
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium">Translation Text</label>
          <textarea
            value={targetText}
            onChange={(e) => setTargetText(e.target.value)}
            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            rows="3"
            required
          />
        </div>
        <div>
          <label className="block text-sm font-medium">Translator ID</label>
          <input
            type="text"
            value={translatorId}
            onChange={(e) => setTranslatorId(e.target.value)}
            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            required
          />
        </div>
        {existingTranslation && (
          <div>
            <label className="block text-sm font-medium">Reason for Update</label>
            <input
              type="text"
              value={reason}
              onChange={(e) => setReason(e.target.value)}
              className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            />
          </div>
        )}
        <button
          onClick={handleSubmit}
          className="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
        >
          {existingTranslation ? 'Update Translation' : 'Add Translation'}
        </button>
      </div>
    </div>
  );
};