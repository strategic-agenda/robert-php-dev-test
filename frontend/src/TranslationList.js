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
            const response = await fetch(`http://127.0.0.1:8000/api/translations.php?id=${id}`, {
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
        <div className="mx-4">
        <h2 className="text-center">Translation Units</h2>
    <table className="table">
        <thead>
        <tr>
        <th>Text</th>
        <th>Action</th>
        </tr>
        </thead>
        <tbody>

        {units && units.map((unit) => (
                <tr key={unit.id}>
                    <td>{unit.text}</td>
                    <td>
                        <button className="btn btn-sm btn-primary m-1" onClick={() => handleEdit(unit)}>Edit</button>
                        <button className="btn btn-sm btn-danger m-1" onClick={() => handleDelete(unit.id)}>Delete</button>
                    </td>
    </tr>

))}
    </tbody>
    </table>
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
