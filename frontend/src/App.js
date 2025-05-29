import React from "react";
import TranslationList from "./TranslationList";
import "./App.css";

/**
 * App Component
 *
 * The root component of the application.
 * Renders the main layout and the TranslationList component.
 *
 * @returns {JSX.Element} The rendered application
 */
function App() {
  return (
    <div className="App">
      <header className="App-header">
        <h1>Translation Management System</h1>
      </header>
      <main>
        <TranslationList />
      </main>
    </div>
  );
}

export default App;
