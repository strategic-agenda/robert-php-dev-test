import { useContext, useEffect, useState } from 'react';

import AppContextInterface from '../../Interfaces/AppContextInterface';
import translate from '../../Helpers/Translate';
import HttpGetRequestService from '../../Services/HttpGetRequestService';
import LanguagesInterface from '../../Interfaces/LanguageInterface';

import { AppContext } from '../../Contexts/AppContext';

const SystemLanguagePage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);
    const [languages, setLanguages] = useState<LanguagesInterface[]>([]);
    const url = window.location.pathname;

    useEffect(() => {
        HttpGetRequestService(url)
            .then((response) => {
                setLanguages(response.data);
                console.log(['>>>>>>>> ', response.data]);
            });

    }, []);

    return (<main>
        <div className='container'>
            <div className='w-full'>
                <h1>{ translate(appContext.translations.system, 'MENU_HEADER_CHANGE_LANGUAGE', 'Change App Language') }</h1>
                <p className='menu-left'>
                    { languages.map((language, index) => {
                        return <a href={'/system/language/' + language.uuid } key={ 'language-key-' + index }>{ language.title }</a>
                    })}
                </p>
            </div>
        </div>
    </main>);
}
export default SystemLanguagePage;
