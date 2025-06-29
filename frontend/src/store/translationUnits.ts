import { create } from 'zustand';
import axios from 'axios';

export interface Language {
  id: number;
  code: string;
  name: string;
}

export interface TranslationUnit {
  id: number;
  source_text: string;
  source_language_id: number;
  source_language_code: string;
  created_at: string;
}

export interface Translation {
  id: number;
  unit_id: number;
  target_language_id: number;
  translated_text: string;
  created_at: string;
  updated_at: string;
}

interface TranslationUnitStore {
  units: TranslationUnit[];
  translations: Record<number, Translation[]>;
  languages: Language[];
  loading: boolean;
  error: string | null;
  
  fetchUnits: () => Promise<void>;
  createUnit: (data: Omit<TranslationUnit, 'id' | 'created_at'>) => Promise<void>;
  updateUnit: (id: number, data: Partial<TranslationUnit>) => Promise<void>;
  deleteUnit: (id: number) => Promise<void>;
  
  fetchTranslations: (unitId: number) => Promise<void>;
  createTranslation: (unitId: number, languageId: number, text: string) => Promise<void>;
  updateTranslation: (translationId: number, text: string) => Promise<void>;
  deleteTranslation: (translationId: number) => Promise<void>;

  fetchLanguages: () => Promise<void>;
}

const API_URL = 'http://localhost:8000/api';

const useTranslationUnitStore = create<TranslationUnitStore>((set) => ({
  units: [],
  translations: {},
  languages: [],
  loading: false,
  error: null,

  fetchLanguages: async () => {
    set({ loading: true });
    try {
      const response = await axios.get(`${API_URL}/languages`);
      set({ languages: response.data });
    } catch (err) {
      set({ error: 'Failed to load languages' });
    } finally {
      set({ loading: false });
    }
  },

  fetchUnits: async () => {
    set({ loading: true, error: null });
    try {
      const response = await axios.get(`${API_URL}/translation-units`);
      set({ units: Array.isArray(response.data) ? response.data : [] });
    } catch (err) {
      set({ error: 'Failed to load units' });
    } finally {
      set({ loading: false });
    }
  },

  createUnit: async (data) => {
    set({ loading: true });
    try {
      const response = await axios.post(`${API_URL}/translation-units`, data);
      set((state) => ({ units: [...state.units, response.data] }));
    } catch (err) {
      set({ error: 'Failed to create unit' });
      throw err;
    } finally {
      set({ loading: false });
    }
  },

  updateUnit: async (id, data) => {
    set({ loading: true });
    try {
      const response = await axios.put(`${API_URL}/translation-units/${id}`, data);
      set((state) => ({
        units: state.units.map((unit) => 
          unit.id === id ? { ...unit, ...response.data } : unit
        ),
      }));
    } catch (err) {
      set({ error: 'Failed to update unit' });
      throw err;
    } finally {
      set({ loading: false });
    }
  },

  deleteUnit: async (id) => {
    set({ loading: true });
    try {
      await axios.delete(`${API_URL}/translation-units/${id}`);
      set((state) => ({
        units: state.units.filter((unit) => unit.id !== id),
        translations: Object.fromEntries(
          Object.entries(state.translations).filter(([unitId]) => Number(unitId) !== id)
        ),
      }));
    } catch (err) {
      set({ error: 'Failed to delete unit' });
      throw err;
    } finally {
      set({ loading: false });
    }
  },

  fetchTranslations: async (unitId) => {
    set({ loading: true });
    try {
      const response = await axios.get(`${API_URL}/translation-units/${unitId}/translations`);
      set((state) => ({
        translations: {
          ...state.translations,
          [unitId]: response.data,
        },
      }));
    } catch (err) {
      set({ error: 'Failed to load translations' });
    } finally {
      set({ loading: false });
    }
  },

  createTranslation: async (unitId, languageId, text) => {
    set({ loading: true });
    try {
      const response = await axios.post(`${API_URL}/translations`, {
        unit_id: unitId,
        language_id: languageId,
        translated_text: text,
      });
      set((state) => ({
        translations: {
          ...state.translations,
          [unitId]: [
            ...(state.translations[unitId] || []),
            response.data,
          ],
        },
      }));
    } catch (err) {
      set({ error: 'Failed to create translation' });
      throw err;
    } finally {
      set({ loading: false });
    }
  },

  updateTranslation: async (translationId, text) => {
    set({ loading: true });
    try {
      const response = await axios.put(`${API_URL}/translations/${translationId}`, {
        translated_text: text,
      });
      set((state) => ({
        translations: Object.fromEntries(
          Object.entries(state.translations).map(([unitId, translations]) => [
            unitId,
            translations.map((t) =>
              t.id === translationId ? { ...t, ...response.data } : t
            ),
          ])
        ),
      }));
    } catch (err) {
      set({ error: 'Failed to update translation' });
      throw err;
    } finally {
      set({ loading: false });
    }
  },

  deleteTranslation: async (translationId) => {
    set({ loading: true });
    try {
      await axios.delete(`${API_URL}/translations/${translationId}`);
      set((state) => ({
        translations: Object.fromEntries(
          Object.entries(state.translations).map(([unitId, translations]) => [
            unitId,
            translations.filter((t) => t.id !== translationId),
          ])
        ),
      }));
    } catch (err) {
      set({ error: 'Failed to delete translation' });
      throw err;
    } finally {
      set({ loading: false });
    }
  },
}));

export default useTranslationUnitStore;