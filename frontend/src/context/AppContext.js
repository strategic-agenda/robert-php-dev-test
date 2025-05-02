import React, { createContext, useContext, useState } from 'react';

// Create the context
export const AppContext = createContext(null);

// Custom hook for using the context
export const useAppContext = () => {
    const context = useContext(AppContext);
    if (!context) {
        throw new Error('useAppContext must be used within an AppProvider');
    }
    return context;
};

// Context provider component
export const AppProvider = ({ children }) => {
    // Toast notification state
    const [toasts, setToasts] = useState([]);

    // Function to add a toast notification
    const addToast = (message, type = 'success', duration = 3000) => {
        const id = Date.now();
        setToasts(prev => [...prev, { id, message, type, duration }]);
        return id;
    };

    // Function to remove a toast notification
    const removeToast = (id) => {
        setToasts(prev => prev.filter(toast => toast.id !== id));
    };

    // Context value
    const contextValue = {
        toasts,
        addToast,
        removeToast
    };

    return (
        <AppContext.Provider value={contextValue}>
            {children}
        </AppContext.Provider>
    );
};

export default AppProvider;
