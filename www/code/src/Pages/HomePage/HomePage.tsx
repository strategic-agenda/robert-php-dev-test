import { useContext } from 'react';

import translate from '../../Helpers/Translate';
import AppContextInterface from '../../Interfaces/AppContextInterface';
import { AppContext } from '../../Contexts/AppContext';

const HomePage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);

    return (<main>
        <div className='container'>
            <div>
                <h1>{ translate(appContext.translations.system, 'PAGES_HOME_TITLE', 'Welcome to Translation Management App') }</h1>
                <p>
                    { translate(appContext.translations.system, 'PAGES_HOME_DESCRIPTION', 'Your hub for managing translations, streamlining localization, and reaching global audiences with ease. Start by adding your content, inviting your team, and letting us handle the rest. Let’s make your message multilingual!')}
                </p>
            </div>
        </div>
    </main>);
}

export default HomePage;
