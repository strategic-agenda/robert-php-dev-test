import React, { useState, useEffect } from 'react';
import LoadingSpinner from '../components/LoadingSpinner';
import ConfirmModal from '../components/ConfirmModal';
import { useAppContext } from '../context/AppContext';
import { languageAPI } from '../utils/api';

const LanguageManager = () => {
    const { addToast } = useAppContext();
    const [languages, setLanguages] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [searchTerm, setSearchTerm] = useState('');
    const [showDisabled, setShowDisabled] = useState(false);

    // Confirmation modal state
    const [confirmModal, setConfirmModal] = useState({
        isOpen: false,
        title: '',
        message: '',
        confirmAction: () => {},
        type: 'warning'
    });

    // Form state
    const [formData, setFormData] = useState({
        id: null,
        name: '',
        code: '',
        is_rtl: false,
        enabled: true
    });

    // Initial fetch of languages
    useEffect(() => {
        fetchLanguages();
    }, [currentPage, searchTerm, showDisabled]);

    // Fetch languages with pagination, search and filters
    const fetchLanguages = async () => {
        setLoading(true);
        try {
            const params = {
                page: currentPage,
                per_page: 10,
            };

            if (searchTerm) {
                params.search = searchTerm;
            }

            if (showDisabled !== null) {
                params.enabled = !showDisabled;
            }

            const response = await languageAPI.getAll(params);
            setLanguages(response.data.data);
            setTotalPages(response.data.pagination.total_pages);
            setError(null);
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to fetch languages';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        } finally {
            setLoading(false);
        }
    };

    // Reset form data
    const resetForm = () => {
        setFormData({
            id: null,
            name: '',
            code: '',
            is_rtl: false,
            enabled: true
        });
        setIsEditing(false);
    };

    // Handle form input changes
    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData({
            ...formData,
            [name]: type === 'checkbox' ? checked : value
        });
    };

    // Handle form submission
    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            if (isEditing) {
                // Update existing language
                await languageAPI.update(formData.id, formData);
                addToast('Language updated successfully', 'success');
            } else {
                // Create new language
                await languageAPI.create(formData);
                addToast('Language created successfully', 'success');
            }

            // Refresh the list and reset form
            fetchLanguages();
            setShowForm(false);
            resetForm();
        } catch (err) {
            const errorMessage = err.response?.data?.error || `Failed to ${isEditing ? 'update' : 'create'} language`;
            setError(errorMessage);
            addToast(errorMessage, 'error');
        }
    };

    // Load language for editing
    const handleEdit = (language) => {
        setFormData({
            id: language.id,
            name: language.name,
            code: language.code,
            is_rtl: language.is_rtl,
            enabled: language.enabled
        });
        setIsEditing(true);
        setShowForm(true);
    };

    // Confirm delete language
    const confirmDeleteLanguage = (language) => {
        const hasTranslations = language.translations_count > 0;
        setConfirmModal({
            isOpen: true,
            title: hasTranslations ? 'Disable Language' : 'Delete Language',
            message: hasTranslations
                ? `This language has ${language.translations_count} translations. It can't be deleted but will be disabled instead. Continue?`
                : `Are you sure you want to delete ${language.name}? This action cannot be undone.`,
            confirmAction: () => handleDelete(language),
            type: 'danger'
        });
    };

    // Handle language deletion or disabling
    const handleDelete = async (language) => {
        try {
            await languageAPI.delete(language.id);

            // Refresh the list
            fetchLanguages();
            addToast(`Language ${language.translations_count > 0 ? 'disabled' : 'deleted'} successfully`, 'success');
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to delete language';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        }
    };

    // Confirm toggle language status
    const confirmToggleLanguageStatus = (language) => {
        setConfirmModal({
            isOpen: true,
            title: language.enabled ? 'Disable Language' : 'Enable Language',
            message: `Are you sure you want to ${language.enabled ? 'disable' : 'enable'} ${language.name}?`,
            confirmAction: () => toggleLanguageStatus(language),
            type: 'warning'
        });
    };

    // Toggle language enabled status
    const toggleLanguageStatus = async (language) => {
        try {
            await languageAPI.update(language.id, {
                enabled: !language.enabled
            });

            // Refresh the list
            fetchLanguages();
            addToast(`Language ${language.enabled ? 'disabled' : 'enabled'} successfully`, 'success');
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to update language status';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        }
    };

    return (
        <div className="language-manager">
            <div className="bg-white rounded-lg shadow-md p-6 mb-8">
                <div className="flex flex-wrap justify-between items-center mb-6">
                    <h2 className="text-2xl font-bold text-gray-800">Language Management</h2>
                    <div className="flex space-x-2 mt-4 sm:mt-0">
                        <button
                            className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors flex items-center"
                            onClick={() => { setShowForm(true); resetForm(); }}
                        >
                            <svg className="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add New Language
                        </button>
                    </div>
                </div>

                <div className="filters flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 mb-6">
                    <div className="search-box flex-grow">
                        <div className="relative">
                            <input
                                type="text"
                                placeholder="Search by name or code..."
                                className="w-full px-3 py-2 pl-10 border border-gray-300 rounded-md"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                            <div className="absolute left-3 top-2.5 text-gray-400">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div className="filter flex items-center">
                        <label className="inline-flex items-center">
                            <input
                                type="checkbox"
                                className="form-checkbox h-5 w-5 text-blue-600"
                                checked={showDisabled}
                                onChange={(e) => setShowDisabled(e.target.checked)}
                            />
                            <span className="ml-2 text-gray-700">Show disabled languages</span>
                        </label>
                    </div>
                </div>

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {error}
                    </div>
                )}

                {showForm && (
                    <div className="language-form bg-gray-100 p-4 rounded-md mb-6">
                        <h3 className="text-xl font-semibold mb-4">{isEditing ? 'Edit Language' : 'Add New Language'}</h3>
                        <form onSubmit={handleSubmit}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label className="block text-gray-700 mb-2">Language Name <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="name"
                                        value={formData.name}
                                        onChange={handleInputChange}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-gray-700 mb-2">Language Code <span className="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        name="code"
                                        value={formData.code}
                                        onChange={handleInputChange}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    />
                                    <span className="text-sm text-gray-500">ISO code (e.g., en, fr, es)</span>
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-4 mb-4">
                                <div className="flex items-center">
                                    <input
                                        type="checkbox"
                                        name="is_rtl"
                                        id="is_rtl"
                                        checked={formData.is_rtl}
                                        onChange={handleInputChange}
                                        className="h-4 w-4 text-blue-600"
                                    />
                                    <label htmlFor="is_rtl" className="ml-2 text-gray-700">
                                        Right-to-left language
                                    </label>
                                </div>

                                <div className="flex items-center">
                                    <input
                                        type="checkbox"
                                        name="enabled"
                                        id="enabled"
                                        checked={formData.enabled}
                                        onChange={handleInputChange}
                                        className="h-4 w-4 text-blue-600"
                                    />
                                    <label htmlFor="enabled" className="ml-2 text-gray-700">
                                        Enabled
                                    </label>
                                </div>
                            </div>

                            <div className="flex space-x-2">
                                <button
                                    type="submit"
                                    className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors"
                                >
                                    {isEditing ? 'Update Language' : 'Create Language'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => { setShowForm(false); resetForm(); }}
                                    className="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                )}

                {loading ? (
                    <LoadingSpinner message="Loading languages..." />
                ) : languages.length === 0 ? (
                    <div className="empty-state text-center py-8 bg-gray-50 rounded-lg">
                        <svg className="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M3 5h12M9 3v4m1.042 9.256A5.5 5.5 0 017 12a5.5 5.5 0 015.5-5.5 5.5 5.5 0 015.5 5.5 5.5 5.5 0 01-5.5 5.5 5.474 5.474 0 01-2.458-.584"></path>
                        </svg>
                        <p className="text-gray-500 mb-2">No languages found</p>
                        <button
                            className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors"
                            onClick={() => { setShowForm(true); resetForm(); }}
                        >
                            Add your first language
                        </button>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto rounded-lg border border-gray-200">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-100">
                                <tr>
                                    <th className="px-6 py-3 text-left text-gray-700 font-semibold">ID</th>
                                    <th className="px-6 py-3 text-left text-gray-700 font-semibold">Name</th>
                                    <th className="px-6 py-3 text-left text-gray-700 font-semibold">Code</th>
                                    <th className="px-6 py-3 text-left text-gray-700 font-semibold">RTL</th>
                                    <th className="px-6 py-3 text-left text-gray-700 font-semibold">Status</th>
                                    <th className="px-6 py-3 text-left text-gray-700 font-semibold">Translations</th>
                                    <th className="px-6 py-3 text-right text-gray-700 font-semibold">Actions</th>
                                </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                {languages.map(language => (
                                    <tr key={language.id} className={!language.enabled ? 'bg-gray-50' : ''}>
                                        <td className="px-6 py-4 whitespace-nowrap text-gray-700">{language.id}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">{language.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-gray-700 uppercase">{language.code}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-gray-700">
                                            {language.is_rtl ? (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    RTL
                                                </span>
                                            ) : 'LTR'}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                                language.enabled
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-red-100 text-red-800'
                                            }`}>
                                                {language.enabled ? 'Enabled' : 'Disabled'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-gray-700">
                                            {language.translations_count ? (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {language.translations_count}
                                                </span>
                                            ) : 0}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button
                                                onClick={() => handleEdit(language)}
                                                className="text-blue-600 hover:text-blue-900 mr-3"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => confirmToggleLanguageStatus(language)}
                                                className={`${
                                                    language.enabled
                                                        ? 'text-amber-600 hover:text-amber-900'
                                                        : 'text-green-600 hover:text-green-900'
                                                } mr-3`}
                                            >
                                                {language.enabled ? 'Disable' : 'Enable'}
                                            </button>
                                            <button
                                                onClick={() => confirmDeleteLanguage(language)}
                                                className="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {totalPages > 1 && (
                            <div className="pagination flex justify-center space-x-1 mt-6">
                                <button
                                    onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                                    disabled={currentPage === 1}
                                    className={`px-3 py-1 rounded-md ${
                                        currentPage === 1
                                            ? 'bg-gray-200 text-gray-500 cursor-not-allowed'
                                            : 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                                    }`}
                                >
                                    Prev
                                </button>

                                {[...Array(totalPages)].map((_, i) => (
                                    <button
                                        key={i + 1}
                                        onClick={() => setCurrentPage(i + 1)}
                                        className={`px-3 py-1 rounded-md ${
                                            currentPage === i + 1
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                                        }`}
                                    >
                                        {i + 1}
                                    </button>
                                ))}

                                <button
                                    onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                                    disabled={currentPage === totalPages}
                                    className={`px-3 py-1 rounded-md ${
                                        currentPage === totalPages
                                            ? 'bg-gray-200 text-gray-500 cursor-not-allowed'
                                            : 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                                    }`}
                                >
                                    Next
                                </button>
                            </div>
                        )}
                    </>
                )}
            </div>

            {/* Confirmation Modal */}
            <ConfirmModal
                isOpen={confirmModal.isOpen}
                onClose={() => setConfirmModal({...confirmModal, isOpen: false})}
                onConfirm={confirmModal.confirmAction}
                title={confirmModal.title}
                message={confirmModal.message}
                type={confirmModal.type}
            />
        </div>
    );
};

export default LanguageManager;
