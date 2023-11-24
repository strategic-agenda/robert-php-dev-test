import React, { useState } from 'react'
import TranslationForm from './TranslationForm'

const TranslationList = ({units, onDelete, onUpdate}) => {
  const [editingUnit, setEditingUnit] = useState(null)

  const handleEdit = (unit) => {
    setEditingUnit(unit)
  }

  const handleUpdate = (id, newText) => {
    onUpdate(id, newText)

    setEditingUnit(null)
  }

  const handleDelete = async (id) => {
    try {
      const response = await fetch(`http://localhost/api/translations.php?id=${id}`, {
        method: 'DELETE',
      })

      if (response.ok) {
        onDelete(id)
      }
      else {
        console.error('Error deleting translation unit')
      }
    } catch (error) {
      console.error('Error:', error)
    }
  }

  return (
    <div>
      <h2>Translation Units</h2>
      {units && units.map((unit) => (
        <div key={unit.id}>
          {unit.source_text} - Language: {unit.language_id}
          <button onClick={() => handleEdit(unit)}>Edit</button>
          <button onClick={() => handleDelete(unit.id)}>Delete</button>
        </div>
      ))}
      {editingUnit && (
        <TranslationForm
          initialData={editingUnit}
          onSubmit={(id, newUnit) => {
            handleUpdate(id, newUnit)
            console.log('onSubmit', id, newUnit)
            setEditingUnit(null)
          }}
          onCancel={() => {
            setEditingUnit(null)
          }}
          onUpdate={handleUpdate}
        />
      )}
    </div>
  )
}

export default TranslationList
