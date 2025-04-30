import axios from 'axios';

const API_BASE_URL = "http://localhost:8080/api";

export const translationApi = {
    addUnit: async (source, translations) => {
        const response = await axios.post(`${API_BASE_URL}/units`, {
            source,
            translations,
        });
        return response.data;
    },

    getUnits: async () => {
        const response = await axios.get(`${API_BASE_URL}/units`);
        return response.data;
    },

    updateUnit: async (id, translations) => {
        const response = await axios.put(`${API_BASE_URL}/units/${id}`, {
            translations,
        });
        return response.data;
    },

    deleteUnit: async (id) => {
        await axios.delete(`${API_BASE_URL}/units/${id}`);
    },

    getHistory: async (id) => {
        const response = await axios.get(`${API_BASE_URL}/units/${id}/history`);
        return response.data;
    },
};
