import React, { useContext, useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';

import AppContextInterface from '../../Interfaces/AppContextInterface';
import translate from '../../Helpers/Translate';
import TranslationPageMenuComponent from '../../Components/TranslationPageMenuComponent/TranslationPageMenuComponent';
import HttpGetRequestService from '../../Services/HttpGetRequestService';
import TranslationInterface from '../../Interfaces/TranslationInterface';

import { AppContext } from '../../Contexts/AppContext';

const TranslationsEditPage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);
    const params = useParams();
    const [translations, setTranslations] = useState<TranslationInterface[]>([]);

    useEffect(() => {
        HttpGetRequestService('/translations/' + params.uuid)
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
                        return <p key={ 'translation-' + index }>
                            <textarea key={ 'translation-input-' + index } className='w-full'>{ translation.translation }</textarea><br />
                            <button>{ translate(appContext.translations.system, 'PAGE_TRANSLATIONS_BUTTON_UPDATE', 'Update') }</button>
                        </p>
                    }) }
                </div>
            </div>
        </div>
    </main>);
}

export default TranslationsEditPage;
