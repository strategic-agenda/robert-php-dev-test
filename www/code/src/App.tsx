import React, { useEffect, useState } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';

import TranslationInterface from './Interfaces/TranslationInterface';
import AboutPage from './Pages/AboutPage/AboutPage';
import HomePage from './Pages/HomePage/HomePage';
import SystemLanguagePage from './Pages/SystemLanguagePage/SystemLanguagePage';
import NotFoundPage from './Pages/NotFoundPage/NotFoundPage';
import TranslationsListPage from './Pages/TranslationsListPage/TranslationsListPage';
import TranslationsNotFoundPage from './Pages/TranslationsNotFoundPage/TranslationsNotFoundPage';
import HeaderComponent from './Components/HeaderComponent/HeaderComponent';
import HttpGetRequestService from './Services/HttpGetRequestService';
import AppContextInterface from './Interfaces/AppContextInterface';
import LanguagesPage from './Pages/LanguagesPage/LanguagesPage';
import TranslationsEditPage from './Pages/TranslationsEditPage/TranslationsEditPage';
import TranslationsDetailPage from './Pages/TranslationsDetailPage/TranslationsDetailPage';
import TranslationsAddPage from './Pages/TranslationsAddPage/TranslationsAddPage';
import SystemLanguageChangePage from './Pages/SystemLanguageChangePage/SystemLanguageChangePage';

import { AppContext, AppContextDefaultContent } from './Contexts/AppContext';

import './App.scss';

function App() {
    const [appContext, setAppContext] = useState<AppContextInterface>(AppContextDefaultContent);

    useEffect(() => {
        HttpGetRequestService('/system/translations')
            .then((response) => {
                response.data.map((translation: TranslationInterface) => {
                    applicationTranslationObject[translation.translation_key] = translation.translation;
                });

                setAppContext({...appContext, translations: { ...appContext.translations, system: { ...applicationTranslationObject }}});
            });
    }, []);

    const applicationTranslationObject: { [key: string]: string } = {}

    const routes = () => {
        return [
            { path: '/', component: HomePage },
            { path: '/about', component: AboutPage },
            { path: '/languages', component: LanguagesPage },
            { path: '/languages/add', component: LanguagesPage },
            { path: '/system/language', component: SystemLanguagePage },
            { path: '/system/language/:uuid', component: SystemLanguageChangePage },
            { path: '/translations/grouped', component: TranslationsListPage },
            { path: '/translations/all', component: TranslationsListPage },
            { path: '/translations/add', component: TranslationsAddPage },
            { path: '/translations/:uuid', component: TranslationsDetailPage },
            { path: '/translations/:uuid/edit', component: TranslationsEditPage },
            { path: '/translation-not-found', component: TranslationsNotFoundPage },
        ];
    }

    return (
        <>
            <AppContext.Provider value={ appContext }>
                <HeaderComponent />
                <BrowserRouter>
                    <Routes>
                        { routes().map((route, index) => {
                            return <Route path={ route.path }  element={ <route.component /> } key={ 'route_' + index } />
                        })}
                        <Route path='/not-found' element={ <NotFoundPage /> } />
                        <Route path='*' element={ <Navigate to='/not-found' replace={ true } /> } />
                    </Routes>
                </BrowserRouter>
            </AppContext.Provider>
        </>
    );
}

export default App;
