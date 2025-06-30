import React, { useContext } from 'react';

import MenuItemInterface from '../../Interfaces/MenuItemInterface';
import translate from '../../Helpers/Translate';
import setMenuItemActive from '../../Helpers/SetMenuItemActive';
import AppContextInterface from '../../Interfaces/AppContextInterface';
import { AppContext } from '../../Contexts/AppContext';

const TranslationPageMenuComponent = () => {
    const appContext = useContext<AppContextInterface>(AppContext);

    const pageInnerMenu: MenuItemInterface[] = [
        {
            translationKey: 'PAGES_TRANSLATION_MENU_ITEM_GROUPED',
            title: translate(appContext.translations.system, 'PAGES_TRANSLATION_MENU_ITEM_GROUPED', 'Grouped'),
            url: '/translations/grouped',
            position: 'left'
        },
        {
            translationKey: 'PAGES_TRANSLATION_MENU_ITEM_ALL',
            title: translate(appContext.translations.system, 'PAGES_TRANSLATION_MENU_ITEM_ALL', 'All'),
            url: '/translations/all',
            position: 'left'
        },
        {
            translationKey: 'PAGES_TRANSLATION_MENU_ITEM_ADD',
            title: translate(appContext.translations.system, 'PAGES_TRANSLATION_MENU_ITEM_ADD', 'Add'),
            url: '/translations/add',
            position: 'left'
        }
    ];

    return (<p className='flex'>
        { pageInnerMenu.map((link, index ) => {
            return <a href={ link.url } className={ 'page-menu-item' + setMenuItemActive(appContext, link) } key={ 'menu-item-' + index }>{ link.title }</a>
        })}
    </p>);
}

export default TranslationPageMenuComponent;
