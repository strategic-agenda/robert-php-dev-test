import React, { useContext } from 'react';

import AppContextInterface from '../../Interfaces/AppContextInterface';
import { AppContext } from '../../Contexts/AppContext';
import translate from '../../Helpers/Translate';


const AboutPage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);

    return (<main>
        <div className='container'>
            <div>
                <h1>{ translate(appContext.translations.system, 'PAGES_ABOUT_TITLE', 'About Translation Management App') }</h1>
                <p>
                    { translate(appContext.translations.system, 'PAGES_ABOUT_DESCRIPTION', 'Translation Management App is a tool designed to streamline the process of translating content across multiple languages. It helps teams collaborate efficiently by organizing translation tasks, managing language assets like glossaries and translation memories, and integrating with content platforms. Ideal for businesses and localization teams, the app improves consistency, reduces manual effort, and speeds up global content delivery.') }
                </p>
            </div>
        </div>
    </main>);
}

export default AboutPage;
