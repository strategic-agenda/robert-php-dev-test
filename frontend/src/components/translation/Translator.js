import './translator.css';
import config from 'config';
import Select from 'react-select';
import axios from 'axios';
import React, { useState, useEffect } from 'react';

export default function Translator()
{
    const [languages, setLanguages] = useState([]);
    const [message, setMessage] = useState('');
    const [sourceOption, setSourceOption] = useState({
        value: null,
        label: 'Detect language'
    });
    const [targetOption, setTargetOption] = useState({
        value: null,
        label: 'Choose language on which translate'
    });
    const [inputValue, setInputValue] = useState('');
    const [resultValue, setResultValue] = useState('');
    const customStyles = {
        container: provided => ({
            ...provided,
            width: '30%'
        })
    };

    useEffect(() => {
        axios.get(config.backend + '/language')
            .then(response => {
                let newArray = response.data.items.map((item) => {
                    return {
                        value: item.language_code,
                        label: item.language_name,
                    };
                });

                setLanguages(newArray);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }, []);

    useEffect(() => {
        axios.get(config.backend + '/translator', {
            params: {
                search: inputValue,
                target_language: targetOption.value,
                source_language: sourceOption.value
            }
        })
            .then(response => {
                setResultValue(response.data.result);
                setMessage(response.data.message);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }, [inputValue, targetOption, sourceOption]);

    const handleSourceChange = (event) => {
        setSourceOption(event);
    };

    const handleTargetChange = (event) => {
        setTargetOption(event);
    };

    const handleInputChange = (event) => {
        setInputValue(event.target.value);
    };

    return (
        <>
            <div className="select-options">
                <Select styles={customStyles} options={languages} placeholder="Detect language" value={sourceOption} onChange={handleSourceChange}/>
                <Select styles={customStyles} options={languages} placeholder="Choose language on which translate" value={targetOption} onChange={handleTargetChange}/>
            </div>
            <div className="body-translator">
                <textarea onChange={handleInputChange} value={inputValue} placeholder="Enter you word..."></textarea>
                <textarea disabled value={resultValue}></textarea>
            </div>

            {(message) ? (
                <div className="alert alert-primary" role="alert">
                    {message}
                </div>
            ) : ''}
        </>
    );
}