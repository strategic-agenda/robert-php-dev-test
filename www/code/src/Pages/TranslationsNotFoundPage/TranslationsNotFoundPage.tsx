import { useContext } from 'react';

import translate from '../../Helpers/Translate';
import AppContextInterface from '../../Interfaces/AppContextInterface';

import { AppContext } from '../../Contexts/AppContext';

const TranslationsNotFoundPage = () =>  {
    const appContext = useContext<AppContextInterface>(AppContext);

    return (<main>
        <div className='container'>
            <div>
                <h1>{ translate(appContext.translations.system, 'PAGES_TRANSLATION_NOT_FOUND_TITLE', 'Translation Not Found Page') }</h1>
                <div>
                    { translate(appContext.translations.system, 'PAGES_TRANSLATION_NOT_FOUND_DESCRIPTION', 'The translation not found page is a sample page. When the requested text doesn’t have a translation available, system returns "translation-not-found". This text is collected across all the platform to help you create missing translations.') }
                </div>
            </div>
        </div>
    </main>);
}

export default TranslationsNotFoundPage;
