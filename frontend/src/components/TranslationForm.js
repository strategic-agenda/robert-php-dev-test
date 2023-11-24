import React, { useState } from 'react'

const TranslationForm = ({ onSubmit, onUpdate, initialData   }) => {
  const [text, setText] = useState(initialData?.text || '')

    async function addTranslationUnit (text) {
      const response = await fetch('http://localhost/api/translations.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ source_text: text, language_id: 1 }), // Adjust language_id as needed
      })
      if (!response.ok) {
        throw new Error('Failed to add translation unit')
      }
      return response.json()
    }

  async function updateTranslationUnit (id, text) {
    const response = await fetch('http://localhost/api/translations.php', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ source_text: text, id: id }), // Adjust language_id as needed
    })
    if (!response.ok) {
      throw new Error('Failed to update translation unit')
    }
    return response.json()
  }

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      if (initialData?.id) {
        // Update existing translation unit
        const updatedUnit = await updateTranslationUnit(initialData.id, text);
        console.log('updatedUnit:', updatedUnit);
        onUpdate(initialData.id, text);
      } else {
        // Create a new translation unit
        const newUnit = await addTranslationUnit(text);
        console.log('newUnit:', newUnit);
        onSubmit(newUnit);
      }

      setText('');
    } catch (error) {
      console.error('Error:', error);
    }
  }

  return (
    <form onSubmit={handleSubmit}>
            <textarea
              value={text}
              onChange={(e) => setText(e.target.value)}
              placeholder="Enter translation text"
            />
      <button type="submit">Submit</button>
    </form>
  )
}

export default TranslationForm
