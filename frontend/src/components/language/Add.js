import {useParams} from 'react-router-dom';
import { BrowserRouter as Router, Route, Navigate } from 'react-router-dom';
import React, {useState, useEffect} from 'react';
import axios from 'axios';
import config from 'config';
import Select from 'react-select';

export default function Add() {
    const [data, setData] = useState({
        language_code: '',
        language_name: ''
    });

    let handleChange = (e) => {
        setData({ ...data, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            console.log(data);
            const response = await axios.post(config.backend + '/language/save', data,{
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
            <h1>Add Language</h1>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label htmlFor="language_name" className="form-label">Name</label>
                    <input type="text" name="language_name" value={data.language_name} className="form-control" id="language_name" onChange={handleChange} placeholder="Name..." />
                    <small>Ukraine, English, Polish...</small>
                </div>
                <div className="mb-3">
                    <label htmlFor="language_code" className="form-label">Code</label>
                    <input type="text" name="language_code" className="form-control" id="language_code" value={data.language_code} onChange={handleChange} placeholder="Code..." />
                    <small>UA, EN, PL...</small>
                </div>
                <button type="submit" className="btn btn-primary">Submit</button>
            </form>
        </>
    );
}