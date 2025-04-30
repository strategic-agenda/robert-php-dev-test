// Component to display a list of translation units and their translations.

import React, { useEffect, useState } from 'react';
import TranslationForm from './TranslationForm';

const TranslationList = () => {
  const [units, setUnits] = useState([]);
  const [editData, setEditData] = useState(null);

  const fetchTranslations = async () => {
    const res = await fetch('http://localhost/api/translations.php');
    const data = await res.json();

    // Group translations by unit_id
    const grouped = {};
    data.forEach(item => {
      if (!grouped[item.unit_id]) {
        grouped[item.unit_id] = {
          unit_id: item.unit_id,
          source: item.source,
          source_language: item.source_language,
          translations: []
        };
      }
      if (item.translation_id) {
        grouped[item.unit_id].translations.push({
          translation_id: item.translation_id,
          target_language: item.target_language,
          translated: item.translated,
          version: item.version
        });
      }
    });

    setUnits(Object.values(grouped).slice(0, 10)); // limit to 10
  };

  useEffect(() => {
    fetchTranslations();
  }, []);

  const handleEdit = (unit, translation) => {
    setEditData({
      unit_id: unit.unit_id,
      translation_id: translation.translation_id,
      source: unit.source,
      source_language: unit.source_language,
      translated: translation.translated,
      target_language: translation.target_language
    });
  };

  return (
    <div>
      <h2>Translation Units</h2>
      <TranslationForm onSubmit={fetchTranslations} editData={editData} clearEdit={() => setEditData(null)} />

      <ul>
        {units.map(unit => (
          <li key={unit.unit_id} style={{ marginBottom: '1rem' }}>
            <strong>{unit.source}</strong> ({unit.source_language})
            <ul>
              {unit.translations.map(t => (
                <li key={t.translation_id}>
                  {t.translated} [{t.target_language}] (v{t.version})
                  <button onClick={() => handleEdit(unit, t)} style={{ marginLeft: '10px' }}>Edit</button>
                </li>
              ))}
              {unit.translations.length === 0 && <li>No translations yet</li>}
            </ul>
          </li>
        ))}
      </ul>
    </div>
  );
};

export default TranslationList;


