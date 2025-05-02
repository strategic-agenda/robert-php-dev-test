import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import App from './App';
import { AppProvider } from './context/AppContext'; // Make sure the path matches your project structure

describe('App component', () => {
    const renderWithProvider = () =>
        render(
            <AppProvider>
                <App />
            </AppProvider>
        );

    it('renders without crashing', () => {
        renderWithProvider();
    });

    it('shows the main title and subtitle', () => {
        renderWithProvider();
        expect(screen.getAllByText(/Robert CAT Tool/i)).toHaveLength(2);
        expect(screen.getByText(/Computer-Assisted Translation Tool/i)).toBeInTheDocument();
    });

    it('renders navigation links', () => {
        renderWithProvider();
        expect(screen.getByRole('link', { name: /Translations/i })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: /Languages/i })).toBeInTheDocument();
    });

    it('renders footer with current year', () => {
        renderWithProvider();
        const year = new Date().getFullYear();
        expect(screen.getByText(`© ${year} Robert CAT Tool`)).toBeInTheDocument();
    });
});
