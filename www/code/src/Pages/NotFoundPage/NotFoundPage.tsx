import { useContext } from 'react';

import translate from '../../Helpers/Translate';
import AppContextInterface from '../../Interfaces/AppContextInterface';

import { AppContext } from '../../Contexts/AppContext';

const NotFoundPage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);

    return (<main>
        <div className='container'>
            <div>
                <h1>{ translate(appContext.translations.system, 'PAGES_NOTFOUND_TITLE', 'Not Found!') }</h1>
                <div>
                    { translate(appContext.translations.system, 'PAGES_NOTFOUND_DESCRIPTION', 'The content you are looking for cannot be found!') }
                </div>
            </div>
        </div>
    </main>);
}
export default NotFoundPage;
