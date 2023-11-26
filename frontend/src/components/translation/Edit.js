import {useParams} from 'react-router-dom';
import { BrowserRouter as Router, Route, Navigate } from 'react-router-dom';
import React, {useState, useEffect} from 'react';
import axios from 'axios';
import config from 'config';
import Select from 'react-select';

export default function Edit() {
    const {translation_id} = useParams();
    const [languages, setLanguages] = useState([]);
    const [data, setData] = useState({
        target_language: '',
        source_language: '',
        source_text: '',
        translated_text: ''
    });

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

    let handleChange = (e) => {
        setData({ ...data, [e.target.name]: e.target.value });
        console.log(data);
    };

    let handleChangeSourceSelect = (source_language) => {
        setData({ ...data, source_language: source_language.value });
    };

    let handleChangeTargetSelect = (target_language) => {
        setData({ ...data, target_language: target_language.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            console.log(data);
            const response = await axios.post(config.backend + '/translate/save', data,{
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            console.log(response);

            window.location.href = '/';
        } catch (error) {
            // Handle errors, e.g., show an error message
            console.log(error);
            alert(error.response.data.message);
        }
    };

    return (
        <>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label htmlFor="source-language" className="form-label">Source language</label>
                    <Select
                        options={languages}
                        onChange={handleChangeSourceSelect}
                        name="source_language"
                        placeholder="Select Source Language"
                        id="source-language"/>
                </div>
                <div className="mb-3">
                    <label htmlFor="target-language" className="form-label">Target language</label>
                    <Select options={languages} name="target_language" placeholder="Select Target Language" onChange={handleChangeTargetSelect} id="target-language"/>
                </div>
                <div className="mb-3">
                    <label htmlFor="source-text" className="form-label">Source text</label>
                    <input type="text" name="source_text" value={data.source_text} className="form-control" id="source-text" onChange={handleChange} placeholder="Source text..." />
                </div>
                <div className="mb-3">
                    <label htmlFor="translated-text" className="form-label">Translated text</label>
                    <input type="text" name="translated_text" className="form-control" id="translated-text" value={data.translated_text} onChange={handleChange} placeholder="Translated text..." />
                </div>
                <button type="submit" className="btn btn-primary">Submit</button>
            </form>
        </>
    );
}