// Component to display a list of translation units and their translations.
import React, { useState, useEffect } from 'react';
import TranslationForm from './TranslationForm';

export default function TranslationList() {
  const [units, setUnits] = useState([]);
  const [editingUnit, setEditingUnit] = useState();

  const [adding, setAdding] = useState(false);

  useEffect(() => {
    fetch('/units')
      .then(res => res.json()).then(data => setUnits(data.slice(0, 10)));
  }, []);

  const saveUnit = (unit) => {
    const method = unit.id ? 'PUT' : 'POST';
    const url = unit.id ? `/units/${unit.id}` : '/units';

    fetch(url , {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ content: unit.content })
    })
      .then(res => res.json())

       .then(savedUnit => {
        setUnits(prev => {
          if (unit.id) {
            // update existing unit
            return prev.map(u => (u.id === savedUnit.id ? savedUnit : u) );
          } else {
            // add new unit
            return [savedUnit, ...prev];
          }
        });
        setEditingUnit(null);

        setAdding(false);
      });
  };

  return (
    <div>
      <h3>Translation Units</h3>

      {adding ? (
        <TranslationForm
          onSave={saveUnit}
          onCancel={() => setAdding(false)}
        />
      ) : (
        <button onClick={() => setAdding(true)}>
            Add New Translation
            </button>
      )}

      <ul>
        {
        units.map(unit => (
          <li key={unit.id} >

            {editingUnit?.id === unit.id ? (
              <TranslationForm
                unit={editingUnit}
                onSave={saveUnit}
                onCancel={() => setEditingUnit(null)}
              />
            ) : (
              <>
                <span>{unit.content } </span>
                <button onClick={ () => setEditingUnit( unit)}>Edit</button>
              </>
            )}
          </li>
        ))}
      </ul>
    </div>
  );
}