import { createContext, useContext, useEffect, useState } from 'react';
import API_BASE_URL from './../config/api';

const LanguageContext = createContext();

export const useLanguages = () => useContext(LanguageContext);

export const LanguageProvider = ({ children }) => {
  const [languages, setLanguages] = useState([]);

  useEffect(() => {
    fetch(`${API_BASE_URL}languages.php`)
      .then(res => res.json())
      .then(data => setLanguages(data || []));
  }, []);

  return (
    <LanguageContext.Provider value={languages}>
      {children}
    </LanguageContext.Provider>
  );
};
