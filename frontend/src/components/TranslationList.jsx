import React from "react";

const TranslationList = ({
  translationUnits,
  loading,
  editingId,
  handleEdit,
}) => {
  return (
    <div className="translation-list-container">
      <h2>Translation Units</h2>
      {loading ? (
        <div className="loading">Loading translation units...</div>
      ) : translationUnits.length > 0 ? (
        <table className="translation-table">
          <thead>
            <tr>
              <th>Source Text</th>
              <th>Target Text</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {translationUnits.map((unit) => (
              <tr key={unit.id} className={`status-${unit.status}`}>
                <td>{unit.source_text}</td>
                <td>{unit.target_text || <em>Not translated yet</em>}</td>
                <td>
                  <span className={`status-badge ${unit.status}`}>
                    {unit.status}
                  </span>
                </td>
                <td>
                  <button
                    className="btn-edit"
                    onClick={() => handleEdit(unit)}
                    disabled={editingId === unit.id}
                  >
                    Edit
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      ) : (
        <div className="no-data">No translation units found.</div>
      )}
    </div>
  );
};

export default TranslationList;
