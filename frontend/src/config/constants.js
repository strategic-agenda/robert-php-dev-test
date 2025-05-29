// API Base URL from environment variable
export const API_BASE_URL =
  process.env.REACT_APP_API_BASE_URL || "http://localhost:8000";

// API Endpoints
export const API_ENDPOINTS = {
  TRANSLATIONS: `${API_BASE_URL}/api/translations`,
  TRANSLATION_BY_ID: (id) => `${API_BASE_URL}/api/translations/${id}`,
};

// Form field names
export const FORM_FIELDS = {
  SOURCE_TEXT: "source_text",
  TARGET_TEXT: "target_text",
  SOURCE_LANGUAGE: "source_language",
  TARGET_LANGUAGE: "target_language",
};

// Initial form state
export const INITIAL_FORM_STATE = {
  [FORM_FIELDS.SOURCE_TEXT]: "",
  [FORM_FIELDS.TARGET_TEXT]: "",
  [FORM_FIELDS.SOURCE_LANGUAGE]: "",
  [FORM_FIELDS.TARGET_LANGUAGE]: "",
};
