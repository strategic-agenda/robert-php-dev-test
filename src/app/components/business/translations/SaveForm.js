'use client';

import { useRouter, useParams } from 'next/navigation'
import React, { useState, useEffect } from 'react';
import languages from '../../../../../languages';


const TranslationSaveForm = () => {
  const params = useParams();
  const router = useRouter();
  const action = params.id === 'new' ? 'add' : 'edit';

  const [formData, setFormData] = useState({
    languageCode: 'ru',
    type: 'Word',
    text: '',
  });

  const [history, setHistory] = useState([]);

  useEffect(() => {
    const fetchUnit = async () => {
      const response = await fetch(`http://localhost:8000/translations/${params.id}`);
      const data = await response.json();
      setFormData({
        languageCode: data.lang_code,
        type: data.unit_type,
        text: data.unit_text
      });
    };

    const fetchUnitHistory = async () => {
      const response = await fetch(`http://localhost:8000/history/${params.id}`);
      const data = await response.json();
      setHistory(data);
      console.log('data', data);
    };

    if (action === 'edit') {
      fetchUnit();
      fetchUnitHistory();
    }
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }));
  };

  const handleSubmit = async () => {
    if (action === 'edit') {
      formData.id = params.id;
    }

    const response = await fetch('http://localhost:8000/translations', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData)
    });

    if (response.ok) {
      router.push('/');
    } 
  };

  return (
    <div className='w-full w-1/2'>
      <form className="w-full bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div className="w-full">
          <label className="block tracking-wide text-gray-700 font-bold mb-2" htmlFor="languageCode">
            Language code
          </label>
          <div className="relative">
            <select
              name="languageCode" 
              className="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" 
              id="languageCode"
              onChange={handleChange}
              value={formData.languageCode}
            >
              {languages.map((lang) => (
                <option 
                  key={lang.value} 
                  value={lang.value} 
                >
                  {lang.label}
                </option>
              ))}
            </select>
            <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
              <svg className="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
            </div>
          </div>
        </div>

        <div className="mt-3 w-full">
          <label className="block tracking-wide text-gray-700 font-bold mb-2" htmlFor="type">
            Type
          </label>
          <div className="relative">
            <select
              name="type" 
              className="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" 
              id="type"
              onChange={handleChange}
              value={formData.type}
            >
              <option value="word">Word</option>
              <option value="sentence">Sentence</option>
              <option value="paragraph">Paragraph</option>
            </select>
            <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
              <svg className="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
            </div>
          </div>
        </div>

        <div className="w-full mt-4">
          <label className="block tracking-wide text-gray-700 font-bold mb-2" htmlFor="unit-text">
            Text
          </label>
          <textarea
            name="text" 
            className="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" 
            id="unit-text" 
            rows="4"
            value={formData.text}
            onChange={handleChange}
          ></textarea>
        </div>

        {history.length > 0 &&
          <div className='w-full mt-4'>
            <h1 className='text-2xl mb-3'>Changes history</h1>
            <ul>
              {history.map((item, index) => (
                <li key={index}>{ item.message }</li>
              ))}
            </ul>
          </div>
        }

        <div className="mt-5 flex items-center justify-between">
          <button
            className="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            type="button"
            onClick={handleSubmit}
          >
            { action === 'edit' ? 'Update' : 'Add' }
          </button>
        </div>
      </form>
    </div>
  );
};

export default TranslationSaveForm;