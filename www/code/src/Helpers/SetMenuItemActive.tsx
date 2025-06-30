import MenuItemInterface from '../Interfaces/MenuItemInterface';
import AppContextInterface from '../Interfaces/AppContextInterface';

const setMenuItemActive = (appContext: AppContextInterface, item: MenuItemInterface) => {
    return appContext.location === item.url ? ' active' : '';
}

export default setMenuItemActive;
