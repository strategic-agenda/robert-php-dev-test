import axios from 'axios';
import React, { useState, useEffect } from 'react';
import Select from 'react-select';
import "./body/index.css";
import config from 'config';
import {Link} from 'react-router-dom';

export default function Index() {
    const [data, setData] = useState([]);
    const [languages, setLanguages] = useState([]);
    const [sourceOption, setSourceOption] = useState({
        value: null,
        label: 'Select Source LanguageList'
    });
    const [targetOption, setTargetOption] = useState({
        value: null,
        label: 'Select Target LanguageList'
    });
    const [inputValue, setInputValue] = useState('');

    useEffect(() => {
        console.log(config);
        axios.get(config.backend + '/translate')
            .then(response => {
                setData(response.data.items);
                console.log(response.data);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
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
        console.log(sourceOption.value);
        axios.get(config.backend + '/translate', {
            params: {
                search: inputValue,
                source_language: sourceOption.value,
                target_language: targetOption.value
            }
        }).then(response => {
            setData(response.data.items);
            console.log(response.data);
        })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }, [sourceOption, targetOption, inputValue]);

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
        <div>
            <h2>Items</h2>
            <div className="select-options">
                <Select options={languages} placeholder="Select Source Language" value={sourceOption} onChange={handleSourceChange}/>
                <Select options={languages} placeholder="Select Target Language" value={targetOption} onChange={handleTargetChange}/>
                <input type="text" placeholder="Enter word" value={inputValue} onChange={handleInputChange}/>
            </div>

            <table className="table">
                <thead>
                <tr>
                    <th scope="col">Source language</th>
                    <th scope="col">Target language</th>
                    <th scope="col">Source text</th>
                    <th scope="col">Translated text</th>
                </tr>
                </thead>
                <tbody>

                {data.map(item => (
                    <tr>
                        <td>{item.source_language}</td>
                        <td>{item.target_language}</td>
                        <td>{item.source_text}</td>
                        <td>{item.translated_text}</td>
                    </tr>
                ))}
                </tbody>
            </table>
            <script>
                $('#languages-select').select2()
            </script>
        </div>
    );
}
