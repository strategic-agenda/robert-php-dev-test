'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import languages from '../../../../../languages';


const TranslationList = () => {
  const [translationUnits, setTranslationUnits] = useState([]);

  const fetchData = async () => {
    try {
      const response = await fetch('http://localhost:8000/translations');
      const data = await response.json();
      setTranslationUnits(data);
    } catch (error) {
      console.error('Error fetching data:', error);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const getLanguageLabel = (langCode) => {
    const foundLanguage = languages.find((lang) => lang.value === langCode);
    return foundLanguage ? foundLanguage.label : langCode;
  };

  const handleDelete = async (id) => {
    const confirmed = window.confirm('Are you sure you want to delete this Translation Unit?');
    if (confirmed) {
      await fetch(`http://localhost:8000/translations/${id}`, {
        method: 'DELETE'
      });

      fetchData();
    }
  };

  return (
    <div className=''>
      {translationUnits.map((unit) => (
        <div className='mt-4 mt-3 bg-white shadow-md rounded-xl px-8 pt-4 pb-6' key={unit.id}>
          <div className='flex'>
            <div className='text-xl'>Unit type: <b>{unit.unit_type}</b> |</div>
            <div className='ml-2 text-xl'>Language: <b>{getLanguageLabel(unit.lang_code)}</b></div>
          </div>
          <div className='my-3'><span className='italic'>{unit.unit_text}</span></div>
          <button className='mt-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'>
            <Link href={`/translations/${unit.id}`}>
              Edit
            </Link>
          </button>
          <button 
            className='ml-3 mt-3 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded'
            onClick={() => handleDelete(unit.id)}
          >
            Delete
          </button>
        </div>
      ))}
    </div>
  );
};

export default TranslationList;