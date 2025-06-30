import React, { useContext, useEffect, useState } from 'react';

import MenuItemInterface from '../../Interfaces/MenuItemInterface';
import AppContextInterface from '../../Interfaces/AppContextInterface';
import translate from '../../Helpers/Translate';
import setMenuItemActive from '../../Helpers/SetMenuItemActive';
import { AppContext } from '../../Contexts/AppContext';

import './HeaderConmponent.scss';

const HeaderComponent = () => {
    const appContext = useContext<AppContextInterface>(AppContext)
    const [menu, setMenu] = useState<MenuItemInterface[]>([]);

    const menuWithoutTranslations = [
            {
                translationKey: 'MENU_HEADER_HOME',
                title: '',
                url: '/',
                position: 'left'
            },
            {
                translationKey: 'MENU_HEADER_ABOUT',
                title: '',
                url: '/about',
                position: 'left'
            },
            {
                translationKey: 'MENU_HEADER_LANGUAGES',
                title: '',
                url: '/languages',
                position: 'left'
            },
            {
                translationKey: 'MENU_HEADER_TRANSLATIONS',
                title: '',
                url: '/translations/grouped',
                position: 'left'
            },
            {
                translationKey: 'MENU_HEADER_TRANSLATION_NOT_FOUND',
                title: '',
                url: '/translation-not-found',
                position: 'left'
            },
            {
                translationKey: 'MENU_HEADER_CHANGE_LANGUAGE',
                title: '',
                url: '/system/language',
                position: 'right'
            }
        ]

    useEffect(() => {
        setMenu(menuItemsTranslations(menu));
    }, [appContext.translations.system])

    const menuItemsTranslations = (menu: MenuItemInterface[]) => {
        return menuWithoutTranslations.map((item: MenuItemInterface) => {
            return {
                ...item,
                title: translate(appContext.translations.system, item.translationKey, 'translation-not-found')
            }
        });
    }

    return (<header>
        <div className="container">
            <div>
                { menu.map((link, index) => {
                    return link.url && link.position === 'left' &&
                        <a href={ link.url } key={ 'header-menu' + index } className={ setMenuItemActive(appContext, link) }> { link.title }</a>
                      })
                }
            </div>
            <div className='ml-auto'>
                { menu.map((link, index) => {
                    return link.url && link.position === 'right' &&
                        <a href={ link.url } key={ 'header-menu' + index } className={ setMenuItemActive(appContext, link) }> { link.title }</a>
                    })
                }
            </div>
        </div>
    </header>);
}

export default HeaderComponent;
