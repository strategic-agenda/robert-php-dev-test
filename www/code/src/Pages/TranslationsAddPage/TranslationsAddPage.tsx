import React, { useContext } from 'react';

import AppContextInterface from '../../Interfaces/AppContextInterface';
import TranslationPageMenuComponent from '../../Components/TranslationPageMenuComponent/TranslationPageMenuComponent';
import translate from '../../Helpers/Translate';

import { AppContext } from '../../Contexts/AppContext';

const TranslationsAddPage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);

    return (<main>
        <div className='container'>
            <div className='w-full'>
                <h1>{ translate(appContext.translations.system, 'PAGES_TRANSLATIONS_TITLE', 'Translation Management') }</h1>
                <p>
                    { translate(appContext.translations.system, 'PAGES_TRANSLATIONS_DESCRIPTION', 'Your central place to manage translations, collaborate with teams, and localize faster.') }
                </p><br />
                <TranslationPageMenuComponent />
                <div className='body-bg'>
                    add translation form
                </div>
            </div>
        </div>
    </main>);
}

export default TranslationsAddPage;
