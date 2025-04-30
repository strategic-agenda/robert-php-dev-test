import React, { useState } from "react";
import { translationApi } from "./api";

export function TranslationForm({ onUnitAdded }) {
  const [newSource, setNewSource] = useState("");
  const [newTranslation, setNewTranslation] = useState("");
  const [selectedLanguage, setSelectedLanguage] = useState("en");
  const [error, setError] = useState(null);

  const addUnit = async () => {
    if (!newSource.trim()) return;

    try {
      const data = await translationApi.addUnit(newSource, {
        [selectedLanguage]: newTranslation,
      });
      onUnitAdded(data);
      setNewSource("");
      setNewTranslation("");
      setError(null);
    } catch (err) {
      setError("Failed to add translation unit");
      console.error("Error adding unit:", err);
    }
  };

  return (
    <div>
      {error && <div>{error}</div>}
      <input
        value={newSource}
        onChange={(e) => setNewSource(e.target.value)}
        placeholder="Source text"
      />
      <select
        value={selectedLanguage}
        onChange={(e) => setSelectedLanguage(e.target.value)}
      >
        <option value="en">English</option>
        <option value="es">Spanish</option>
        <option value="fr">French</option>
        <option value="de">German</option>
      </select>
      <input
        value={newTranslation}
        onChange={(e) => setNewTranslation(e.target.value)}
        placeholder={`${selectedLanguage.toUpperCase()} translation`}
      />
      <button onClick={addUnit}>Add</button>
    </div>
  );
}
