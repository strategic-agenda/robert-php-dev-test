import React, { useContext, useEffect, useState } from 'react';

import AppContextInterface from '../../Interfaces/AppContextInterface';
import translate from '../../Helpers/Translate';
import TranslationPageMenuComponent from '../../Components/TranslationPageMenuComponent/TranslationPageMenuComponent';
import HttpGetRequestService from '../../Services/HttpGetRequestService';

import { AppContext } from '../../Contexts/AppContext';
import TranslationInterface from '../../Interfaces/TranslationInterface';

const  TranslationsDetailPage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);
    const [translations, setTranslations] = useState<TranslationInterface[]>([]);
    const url = window.location.pathname;

    useEffect(() => {
        HttpGetRequestService(url)
            .then((response) => {
                setTranslations(response.data);
            });
    }, []);

    return (<main>
        <div className='container'>
            <div className='w-full'>
                <h1>{ translate(appContext.translations.system, 'PAGES_TRANSLATIONS_TITLE', 'Translation Management') }</h1>
                <p>
                    { translate(appContext.translations.system, 'PAGES_TRANSLATIONS_DESCRIPTION', 'Your central place to manage translations, collaborate with teams, and localize faster.') }
                </p><br />
                <TranslationPageMenuComponent />
                <div className='body-bg'>
                    { translations.map((translation, index) => {
                        return <p key={'translation-' + index }>[ { translation.translation_key } { translation.iso_code } ] { translation.translation } </p>
                    })}
                </div>
            </div>
        </div>
    </main>);
}

export default TranslationsDetailPage;
