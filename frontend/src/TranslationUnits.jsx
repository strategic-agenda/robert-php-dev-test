import React, { useEffect, useState } from "react";
import { TranslationForm } from "./TranslationForm";
import { TranslationList } from "./TranslationList";
import { translationApi } from "./api";

export function TranslationUnits() {
  const [units, setUnits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchUnits();
  }, []);

  const fetchUnits = async () => {
    try {
      setLoading(true);
      const data = await translationApi.getUnits();
      setUnits(data);
      setError(null);
    } catch (err) {
      setError("Failed to fetch translation units");
      console.error("Error fetching units:", err);
    } finally {
      setLoading(false);
    }
  };

  const handleUnitAdded = (newUnit) => {
    setUnits([newUnit, ...units]);
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <div>
      <h2>Translation Units</h2>

      {error && <div>{error}</div>}

      <TranslationForm onUnitAdded={handleUnitAdded} />
      <TranslationList units={units} onUnitsChange={setUnits} />
    </div>
  );
}
