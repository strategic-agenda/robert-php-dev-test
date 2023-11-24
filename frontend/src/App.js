import React, { useEffect, useState } from 'react'
import TranslationForm from './TranslationForm'
import TranslationList from './TranslationList'

function App () {
  const [units, setUnits] = useState([])

  useEffect(() => {
    console.log('Effect running')

    fetch('http://127.0.0.1:8000/api/translations.php').then((response) => {
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      return response.json()
    }).then((data) => {
      console.log(data)
      setUnits(data)
    }).catch((error) => {
      console.error('Fetch error:', error)
    })
  }, [])

  const handleAddTranslationUnit = (newUnit) => {
    setUnits([...units, newUnit])
  }

  const handleDelete = (id) => {
    console.log('Deleting translation unit:', id)
    setUnits((prevUnits) => prevUnits.filter((unit) => unit.id !== id))
  }

  const handleUpdate = (id, newText) => {
    setUnits((prevUnits) =>
        prevUnits.map((unit) =>
            unit.id === id ? { ...unit, text: newText } : unit,
        ),
    )
  }

  return (
      <div className="App">
      <h1 className="text-center">Translation App</h1>
  <TranslationForm onSubmit={handleAddTranslationUnit}/>
  <TranslationList units={units} onDelete={handleDelete} onUpdate={handleUpdate}/>
  </div>
)
}

export default App
