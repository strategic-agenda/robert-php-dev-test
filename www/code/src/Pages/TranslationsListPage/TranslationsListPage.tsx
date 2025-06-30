import React, { useContext, useEffect, useState } from 'react';

import TranslationInterface from '../../Interfaces/TranslationInterface';
import HttpGetRequestService from '../../Services/HttpGetRequestService';
import AppContextInterface from '../../Interfaces/AppContextInterface';
import TranslationPageMenuComponent from '../../Components/TranslationPageMenuComponent/TranslationPageMenuComponent';
import translate from '../../Helpers/Translate';

import { AppContext } from '../../Contexts/AppContext';

const TranslationsListPage = () => {
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
                        return <div key={ 'translation-container-' + index }>
                            <div key={ 'translation-' + index }>
                                <div className='flex'>
                                    <div className='fw-bold'>{ translation.translation_key } ( { translation.iso_code } )</div>
                                    <div className='menu-right ml-auto'>
                                        <a href={'/translations/' + translation.uuid + '' }>
                                            { translate(appContext.translations.system, 'ANCHOR_LINK_DETAIL', 'Detail') }
                                        </a>
                                        <a href={'/translations/' + translation.uuid + '/edit' }>
                                            { translate(appContext.translations.system, 'ANCHOR_LINK_EDIT', 'Edit') }
                                        </a>
                                    </div>
                                </div>
                                <div>{ translation.translation }</div>
                            </div><br />
                        </div>
                    })}
                </div>
            </div>
        </div>
    </main>);
}
export default TranslationsListPage;
