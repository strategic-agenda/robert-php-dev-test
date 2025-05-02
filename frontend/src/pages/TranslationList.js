import React, { useState, useEffect } from 'react';
import TranslationForm from '../forms/TranslationForm';
import LoadingSpinner from '../components/LoadingSpinner';
import ConfirmModal from '../components/ConfirmModal';
import { useAppContext } from '../context/AppContext';
import { translationAPI, languageAPI } from '../utils/api';
import { formatDate, truncateText } from '../utils/helpers';

const TranslationList = () => {
    const { addToast } = useAppContext();
    const [translationUnits, setTranslationUnits] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [selectedUnit, setSelectedUnit] = useState(null);
    const [isEditing, setIsEditing] = useState(false);
    const [languages, setLanguages] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');

    // Confirmation modal state
    const [confirmModal, setConfirmModal] = useState({
        isOpen: false,
        title: '',
        message: '',
        confirmAction: () => {},
        type: 'warning'
    });

    // Fetch translation units on component mount
    useEffect(() => {
        fetchTranslationUnits();
        fetchLanguages();
    }, [currentPage, searchTerm, statusFilter]);

    // Fetch list of translation units with pagination and filters
    const fetchTranslationUnits = async () => {
        setLoading(true);
        try {
            const params = {
                page: currentPage,
                per_page: 10
            };

            if (searchTerm) {
                params.search = searchTerm;
            }

            if (statusFilter !== 'all') {
                params.status = statusFilter;
            }

            const response = await translationAPI.getAll(params);
            setTranslationUnits(response.data.data);
            setTotalPages(response.data.pagination.total_pages);
            setError(null);
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to fetch translation units';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        } finally {
            setLoading(false);
        }
    };

    // Fetch available languages
    const fetchLanguages = async () => {
        try {
            const response = await languageAPI.getAll({ enabled: true });
            setLanguages(response.data.data);
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to fetch languages';
            addToast(errorMessage, 'error');
        }
    };

    // Fetch a specific translation unit with its translations
    const fetchTranslationUnitDetails = async (unitId) => {
        try {
            const response = await translationAPI.getById(unitId);
            setSelectedUnit(response.data);
            setIsEditing(false);
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to fetch translation unit details';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        }
    };

    // Handle click on a translation unit
    const handleUnitClick = (unitId) => {
        fetchTranslationUnitDetails(unitId);
    };

    // Handle form submission for updating a translation
    const handleUpdateTranslation = async (translationId, content) => {
        try {
            await translationAPI.updateTranslation(selectedUnit.id, translationId, content);

            // Refresh the translation unit data
            fetchTranslationUnitDetails(selectedUnit.id);

            // Show success message
            addToast('Translation updated successfully', 'success');
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to update translation';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        }
    };

    // Handle adding a new translation
    const handleAddTranslation = async (languageId, content) => {
        try {
            await translationAPI.addTranslation(selectedUnit.id, languageId, content);

            // Refresh the translation unit data
            fetchTranslationUnitDetails(selectedUnit.id);

            // Show success message
            addToast('Translation added successfully', 'success');
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to add translation';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        }
    };

    // Handle creating a new translation unit
    const handleCreateUnit = async (sourceContent, context) => {
        try {
            const response = await translationAPI.createUnit(sourceContent, context);

            // Refresh the list
            fetchTranslationUnits();

            // Select the newly created unit
            fetchTranslationUnitDetails(response.data.id);

            // Show success message
            addToast('Translation unit created successfully', 'success');

            // Exit edit mode
            setIsEditing(false);
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to create translation unit';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        }
    };

    // Toggle edit mode
    const toggleEditMode = () => {
        setIsEditing(!isEditing);
    };

    // Create a new translation unit
    const startNewUnit = () => {
        setSelectedUnit({ id: null, source_content: '', context: '' });
        setIsEditing(true);
    };

    // Open confirm dialog for archiving a translation unit
    const confirmArchiveUnit = (unitId) => {
        setConfirmModal({
            isOpen: true,
            title: 'Archive Translation Unit',
            message: 'Are you sure you want to archive this translation unit? This action cannot be undone.',
            confirmAction: () => handleArchiveUnit(unitId),
            type: 'danger'
        });
    };

    // Handle archiving a translation unit
    const handleArchiveUnit = async (unitId) => {
        try {
            await translationAPI.archiveUnit(unitId);

            // Refresh the list
            fetchTranslationUnits();

            // If the archived unit was selected, clear selection
            if (selectedUnit && selectedUnit.id === unitId) {
                setSelectedUnit(null);
            }

            addToast('Translation unit archived successfully', 'success');
        } catch (err) {
            const errorMessage = err.response?.data?.error || 'Failed to archive translation unit';
            setError(errorMessage);
            addToast(errorMessage, 'error');
        }
    };

    if (loading && !translationUnits.length) {
        return <LoadingSpinner message="Loading translation units..." />;
    }

    return (
        <div className="translation-app grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="sidebar bg-white rounded-lg shadow-md p-6 lg:col-span-1">
                <div className="flex justify-between items-center mb-6">
                    <h2 className="text-2xl font-bold text-gray-800">Translation Units</h2>
                    <button
                        className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors"
                        onClick={startNewUnit}
                    >
                        New Unit
                    </button>
                </div>

                <div className="filters space-y-3 mb-6">
                    <div className="search-box">
                        <div className="relative">
                            <input
                                type="text"
                                placeholder="Search translations..."
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

                    <div className="status-filter">
                        <select
                            className="w-full px-3 py-2 border border-gray-300 rounded-md"
                            value={statusFilter}
                            onChange={(e) => setStatusFilter(e.target.value)}
                        >
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {error}
                    </div>
                )}

                <div className="translation-units-list space-y-2 max-h-[calc(100vh-320px)] overflow-y-auto pr-2">
                    {translationUnits.length === 0 ? (
                        <div className="empty-list bg-gray-50 p-4 rounded-md text-center">
                            <p className="text-gray-500">No translation units found</p>
                        </div>
                    ) : (
                        translationUnits.map(unit => (
                            <div
                                key={unit.id}
                                className={`unit-item bg-gray-50 hover:bg-gray-100 rounded-md p-4 cursor-pointer transition-colors ${
                                    selectedUnit && selectedUnit.id === unit.id ? 'border-2 border-blue-500' : ''
                                }`}
                                onClick={() => handleUnitClick(unit.id)}
                            >
                                <div className="unit-content mb-2 font-medium text-gray-700">
                                    {truncateText(unit.source_content, 70)}
                                </div>
                                <div className="unit-meta flex justify-between text-sm text-gray-500">
                                    <span className="translations-count flex items-center">
                                        Translations: {unit.translations_count || 0}
                                    </span>
                                    <span className="updated-at">
                                        {formatDate(unit.updated_at)}
                                    </span>
                                </div>
                            </div>
                        ))
                    )}
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
            </div>

            <div className="main-content bg-white rounded-lg shadow-md p-6 lg:col-span-2">
                {isEditing ? (
                    <div className="edit-panel">
                        <h2 className="text-2xl font-bold text-gray-800 mb-6">
                            {selectedUnit && selectedUnit.id ? 'Edit Translation Unit' : 'Create Translation Unit'}
                        </h2>
                        <TranslationForm
                            unit={selectedUnit}
                            languages={languages}
                            onAddTranslation={handleAddTranslation}
                            onUpdateTranslation={handleUpdateTranslation}
                            onCreateUnit={handleCreateUnit}
                            onCancel={() => setIsEditing(false)}
                        />
                    </div>
                ) : selectedUnit ? (
                    <div className="view-panel">
                        <div className="unit-header flex justify-between items-center mb-6">
                            <h2 className="text-2xl font-bold text-gray-800">
                                Translation Unit #{selectedUnit.id}
                            </h2>
                            <div className="unit-actions space-x-2">
                                <button
                                    className="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors"
                                    onClick={toggleEditMode}
                                >
                                    Edit
                                </button>
                                <button
                                    className="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md transition-colors"
                                    onClick={() => confirmArchiveUnit(selectedUnit.id)}
                                >
                                    Archive
                                </button>
                            </div>
                        </div>

                        <div className="source-content mb-6">
                            <h3 className="text-lg font-semibold text-gray-700 mb-2">Source Content:</h3>
                            <div className="content-box bg-gray-50 p-4 rounded-md border border-gray-200">
                                {selectedUnit.source_content}
                            </div>
                        </div>

                        {selectedUnit.context && (
                            <div className="context mb-6">
                                <h3 className="text-lg font-semibold text-gray-700 mb-2">Context:</h3>
                                <div className="content-box bg-gray-50 p-4 rounded-md border border-gray-200">
                                    {selectedUnit.context}
                                </div>
                            </div>
                        )}

                        <div className="translations">
                            <h3 className="text-lg font-semibold text-gray-700 mb-2">Translations:</h3>
                            <div className="mb-4">
                                <button
                                    className="bg-green-600 hover:bg-green-700 text-white py-1 px-3 rounded-md transition-colors text-sm"
                                    onClick={toggleEditMode}
                                >
                                    Add New Translation
                                </button>
                            </div>

                            {selectedUnit.translations && selectedUnit.translations.length > 0 ? (
                                <div className="translations-list space-y-4">
                                    {selectedUnit.translations.map(translation => (
                                        <div
                                            key={translation.id}
                                            className="translation-item bg-gray-50 p-4 rounded-md border border-gray-200"
                                        >
                                            <div className="translation-header flex justify-between items-center mb-2">
                                                <div>
                                                    <span className="font-semibold text-gray-800 mr-2">
                                                        {translation.language_name}
                                                    </span>
                                                    <span className="text-sm text-gray-500">
                                                        Revision: {translation.revision_number || 1}
                                                    </span>
                                                </div>
                                                <button
                                                    onClick={() => {
                                                        toggleEditMode();
                                                        // Pass information to form to select this translation
                                                        setTimeout(() => {
                                                            const event = new CustomEvent('select-translation', {
                                                                detail: { translation }
                                                            });
                                                            document.dispatchEvent(event);
                                                        }, 0);
                                                    }}
                                                    className="text-blue-600 hover:text-blue-800"
                                                >
                                                    Edit
                                                </button>
                                            </div>
                                            <div className="translation-content whitespace-pre-wrap">
                                                {translation.content}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="no-translations bg-gray-50 p-4 rounded-md text-center">
                                    <p className="text-gray-500">No translations available</p>
                                    <p className="text-sm text-gray-400 mt-1">
                                        Add a translation to make this content available in other languages.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                ) : (
                    <div className="empty-state text-center py-20">
                        <svg
                            className="w-16 h-16 mx-auto text-gray-400 mb-4"
                            fill="none"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="1"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V6a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h2 className="text-2xl font-bold text-gray-700 mb-2">
                            Select a translation unit or create a new one
                        </h2>
                        <p className="text-gray-500 max-w-md mx-auto">
                            Select a translation unit from the list to view its details, or click "New Unit"
                            to create a new one.
                        </p>
                        <button
                            className="mt-6 bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-md transition-colors"
                            onClick={startNewUnit}
                        >
                            Create New Translation Unit
                        </button>
                    </div>
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
}

export default TranslationList;
