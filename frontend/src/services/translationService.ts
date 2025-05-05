import axios from "axios";

const API_URL = "http://localhost:8000/api/translations";

export interface TranslationUnit {
  id?: number;
  document_id: number;
  sequence_number: number;
  source_content: string;
  context?: string;
  translations: Record<number, Translation>;
}

export interface Translation {
  content: string;
  translated_by: number;
  status: "draft" | "reviewed" | "approved" | "rejected";
  reviewed_by?: number;
  created_at?: string;
  updated_at?: string;
}

export interface TranslationResponse {
  success: boolean;
  data: TranslationUnit | TranslationUnit[] | { message: string } | any;
}

export interface PaginationInfo {
  page: number;
  limit: number;
  total: number;
  pages: number;
}

export interface TranslationListResponse {
  success: boolean;
  data: {
    units: TranslationUnit[];
    pagination: PaginationInfo;
  };
}

const translationService = {
  /**
   * Get translation units for a document with pagination
   */
  getTranslationsByDocument: async (
    documentId: number,
    page: number = 1,
    limit: number = 10
  ): Promise<{ units: TranslationUnit[]; pagination: PaginationInfo }> => {
    try {
      const response = await axios.get<TranslationListResponse>(
        `${API_URL}?document_id=${documentId}&page=${page}&limit=${limit}`
      );
      return response.data.data;
    } catch (error) {
      console.error("Error fetching translation units:", error);
      throw error;
    }
  },

  /**
   * Get a specific translation unit by ID
   */
  getTranslationUnit: async (id: number): Promise<TranslationUnit> => {
    try {
      const response = await axios.get<TranslationResponse>(`${API_URL}/${id}`);
      return response.data.data as TranslationUnit;
    } catch (error) {
      console.error("Error fetching translation unit:", error);
      throw error;
    }
  },

  /**
   * Create a new translation unit
   */
  createTranslationUnit: async (
    unit: TranslationUnit
  ): Promise<TranslationUnit> => {
    try {
      const response = await axios.post<TranslationResponse>(API_URL, unit);
      return response.data.data as TranslationUnit;
    } catch (error) {
      console.error("Error creating translation unit:", error);
      throw error;
    }
  },

  /**
   * Update a translation unit
   */
  updateTranslationUnit: async (
    unit: TranslationUnit
  ): Promise<TranslationUnit> => {
    try {
      const response = await axios.put<TranslationResponse>(
        `${API_URL}/${unit.id}`,
        unit
      );
      return response.data.data as TranslationUnit;
    } catch (error) {
      console.error("Error updating translation unit:", error);
      throw error;
    }
  },

  /**
   * Delete a translation unit
   */
  deleteTranslationUnit: async (id: number): Promise<void> => {
    try {
      await axios.delete(`${API_URL}/${id}`);
    } catch (error) {
      console.error("Error deleting translation unit:", error);
      throw error;
    }
  },

  /**
   * Add a translation to a unit
   */
  addTranslation: async (
    unitId: number,
    languageId: number,
    content: string,
    translatedBy: number
  ): Promise<TranslationUnit> => {
    try {
      const response = await axios.post<TranslationResponse>(
        `${API_URL}/${unitId}/translate`,
        {
          language_id: languageId,
          content,
          translated_by: translatedBy,
        }
      );
      return response.data.data as TranslationUnit;
    } catch (error) {
      console.error("Error adding translation:", error);
      throw error;
    }
  },

  /**
   * Update translation status
   */
  updateTranslationStatus: async (
    unitId: number,
    languageId: number,
    status: string,
    reviewedBy: number
  ): Promise<TranslationUnit> => {
    try {
      const response = await axios.put<TranslationResponse>(
        `${API_URL}/${unitId}/status`,
        {
          language_id: languageId,
          status,
          reviewed_by: reviewedBy,
        }
      );
      return response.data.data as TranslationUnit;
    } catch (error) {
      console.error("Error updating translation status:", error);
      throw error;
    }
  },

  /**
   * Get translation history
   */
  getTranslationHistory: async (unitId: number): Promise<any[]> => {
    try {
      const response = await axios.get<TranslationResponse>(
        `${API_URL}/${unitId}/history`
      );
      return (response.data.data as any).history || [];
    } catch (error) {
      console.error("Error fetching translation history:", error);
      throw error;
    }
  },
};

export default translationService;
