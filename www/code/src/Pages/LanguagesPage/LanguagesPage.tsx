import React, { useContext, useEffect, useState } from 'react';

import HttpGetRequestService from '../../Services/HttpGetRequestService';
import LanguagesInterface from '../../Interfaces/LanguageInterface';
import AppContextInterface from '../../Interfaces/AppContextInterface';
import MenuItemInterface from '../../Interfaces/MenuItemInterface';
import setMenuItemActive from '../../Helpers/SetMenuItemActive';
import translate from '../../Helpers/Translate';

import { AppContext } from '../../Contexts/AppContext';

const LanguagesPage = () => {
    const appContext = useContext<AppContextInterface>(AppContext);
    const [languages, setLanguages] = useState<LanguagesInterface[]>([]);
    const url = window.location.pathname;

    const [name, setName] = useState("");

    useEffect(() => {
        HttpGetRequestService(url)
            .then((response) => {
                setLanguages(response.data);
            });
    }, []);

    const pageInnerMenu: MenuItemInterface[] = [
        {
            translationKey: 'PAGES_LANGUAGES_MENU_ITEM_LIST',
            title: translate(appContext.translations.system, 'PAGES_LANGUAGES_MENU_ITEM_LIST', 'List'),
            url: '/languages',
            position: 'left'
        },
        {
            translationKey: 'PAGES_LANGUAGES_MENU_ITEM_ADD',
            title: translate(appContext.translations.system, 'PAGES_LANGUAGES_MENU_ITEM_ADD', 'Add'),
            url: '/languages/add',
            position: 'left'
        },
    ];

    const handleSubmit = (event: React.FormEvent) => {
        event.preventDefault();

        const formData = new FormData();

        console.log(formData);
    }

    return (<main>
        <div className='container'>
            <div className='w-full'>
                <h1>{ translate(appContext.translations.system, 'PAGES_LANGUAGES_TITLE', 'Translation Languages') }</h1>
                <p>{ translate(appContext.translations.system, 'PAGES_LANGUAGES_DESCRIPTION', 'Manage all your project languages in one place. Add new languages, assign translators, and track progress to ensure your content reaches a global audience—accurately and consistently.') }</p><br />
                <p className='flex'>
                    { pageInnerMenu.map((link, index ) => {
                        return <a href={ link.url } className={ 'page-menu-item' + setMenuItemActive(appContext, link) } key={ 'menu-item-' + index }>{ link.title }</a>
                    })}
                </p>
                <div className='w-full body-bg'>
                    { window.location.pathname === '/languages' &&
                        languages.map((language, index) => {
                            return <div key={ 'language-' + index }>
                                { language.title }
                            </div>
                        })
                    }
                    { window.location.pathname === '/languages/add' &&
                        <div>
                            <form action='#' onSubmit={ handleSubmit }>
                                <div>
                                    <label htmlFor='isoCode'>{ translate(appContext.translations.system, 'FORM_LABEL_ISO_CODE', 'ISO Code') }</label>
                                    <input type='text' name='iso_code' id='isoCode' />
                                </div>
                                <div>
                                    <label htmlFor='language'>{ translate(appContext.translations.system, 'FORM_LABEL_LANGUAGE', 'Language') }</label>
                                    <input type='text' name='language' id='language' />
                                </div>
                                <div>
                                    <label htmlFor='active'>{ translate(appContext.translations.system, 'FORM_LABEL_ACTIVE', 'Active') }</label>
                                    <input type='checkbox' name='active' id='active' />
                                </div>
                                <div>
                                    <button>{ translate(appContext.translations.system, 'FORM_LABEL_SAVE', 'Save') }</button>
                                </div>
                            </form>
                        </div>
                    }
                </div>
            </div>
        </div>
    </main>);
}

export default LanguagesPage;
