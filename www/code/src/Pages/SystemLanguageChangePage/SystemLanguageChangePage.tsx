import { useContext, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

import HttpGetRequestService from '../../Services/HttpGetRequestService';
import translate from '../../Helpers/Translate';
import AppContextInterface from '../../Interfaces/AppContextInterface';

import { AppContext } from '../../Contexts/AppContext';

const SystemLanguageChangePage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);
    const [error, setError] = useState(false);
    const url = window.location.pathname;
    const navigate = useNavigate();

    useEffect(() => {
        HttpGetRequestService(url)
            .then((response) => {
                if (response.success) {
                    navigate('/');
                } else {
                    setError(true);
                }
            });
    }, []);

    return (
        <main>
            <div
                className='container'>
                <div
                    className='w-full'>
                    <h1>{ translate(appContext.translations.system, 'MENU_HEADER_CHANGE_LANGUAGE', 'Change App Language') }</h1>
                    <p>
                        { error &&
                            translate(appContext.translations.system, 'PAGES_CHANGE_LANGUAGE_ERROR', 'There was an error during changing language. Please try again.')
                        }
                        { !error &&
                            translate(appContext.translations.system, 'PAGES_CHANGE_LANGUAGE_SUCCESS', 'Language has been changed successfully')
                        }
                    </p>
                </div>
            </div>
        </main>);
}
export default SystemLanguageChangePage;
