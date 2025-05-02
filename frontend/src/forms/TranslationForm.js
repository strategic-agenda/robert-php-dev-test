import React, { useState, useEffect } from 'react';
import { useAppContext } from '../context/AppContext';

const TranslationForm = ({
                             unit,
                             languages,
                             onAddTranslation,
                             onUpdateTranslation,
                             onCreateUnit,
                             onCancel
                         }) => {
    const { addToast } = useAppContext();
    const [sourceContent, setSourceContent] = useState('');
    const [context, setContext] = useState('');
    const [selectedLanguage, setSelectedLanguage] = useState('');
    const [translationContent, setTranslationContent] = useState('');
    const [selectedTranslation, setSelectedTranslation] = useState(null);
    const [errors, setErrors] = useState({});
    const [mode, setMode] = useState('add'); // 'add' or 'edit'
    const [isSaving, setIsSaving] = useState(false);

    // Initialize form when unit changes
    useEffect(() => {
        if (unit) {
            setSourceContent(unit.source_content || '');
            setContext(unit.context || '');
        } else {
            setSourceContent('');
            setContext('');
        }
        resetTranslationForm();
    }, [unit]);

    // Listen for external events to select a translation
    useEffect(() => {
        const handleSelectTranslation = (e) => {
            if (e.detail && e.detail.translation) {
                handleSelectTranslationById(e.detail.translation);
            }
        };

        document.addEventListener('select-translation', handleSelectTranslation);

        return () => {
            document.removeEventListener('select-translation', handleSelectTranslation);
        };
    }, [unit]);

    // Reset the translation part of the form
    const resetTranslationForm = () => {
        setSelectedLanguage('');
        setTranslationContent('');
        setSelectedTranslation(null);
        setMode('add');
        setErrors({});
    };

    // Handle selecting a translation to edit by ID
    const handleSelectTranslationById = (translation) => {
        setSelectedTranslation(translation);
        setTranslationContent(translation.content);
        setSelectedLanguage(translation.language_id.toString());
        setMode('edit');
    };

    // Handle selecting a translation to edit from the UI
    const handleSelectTranslation = (translation) => {
        setSelectedTranslation(translation);
        setTranslationContent(translation.content);
        setSelectedLanguage(translation.language_id.toString());
        setMode('edit');
    };

    // Validate the unit form
    const validateUnitForm = () => {
        const newErrors = {};

        if (!sourceContent.trim()) {
            newErrors.sourceContent = 'Source content is required';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    // Validate the translation form
    const validateTranslationForm = () => {
        const newErrors = {};

        if (!selectedLanguage) {
            newErrors.language = 'Please select a language';
        }

        if (!translationContent.trim()) {
            newErrors.translationContent = 'Translation content is required';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    // Handle unit form submission
    const handleUnitSubmit = async (e) => {
        e.preventDefault();

        if (validateUnitForm()) {
            setIsSaving(true);
            try {
                await onCreateUnit(sourceContent, context);
                addToast('Translation unit created successfully', 'success');
            } catch (error) {
                addToast(error.message || 'Error creating translation unit', 'error');
            } finally {
                setIsSaving(false);
            }
        }
    };

    // Handle translation form submission
    const handleTranslationSubmit = async (e) => {
        e.preventDefault();

        if (validateTranslationForm()) {
            setIsSaving(true);
            try {
                if (mode === 'add') {
                    await onAddTranslation(parseInt(selectedLanguage), translationContent);
                    addToast('Translation added successfully', 'success');
                } else {
                    await onUpdateTranslation(selectedTranslation.id, translationContent);
                    addToast('Translation updated successfully', 'success');
                }
                resetTranslationForm();
            } catch (error) {
                addToast(error.message || 'Error saving translation', 'error');
            } finally {
                setIsSaving(false);
            }
        }
    };

    // Get available languages that don't have translations yet
    const getAvailableLanguages = () => {
        if (!unit || !unit.translations) {
            return languages;
        }

        const translatedLanguageIds = new Set(
            unit.translations.map(translation => translation.language_id)
        );

        if (mode === 'edit' && selectedTranslation) {
            // When editing, include the current language
            translatedLanguageIds.delete(selectedTranslation.language_id);
        }

        return languages.filter(language => !translatedLanguageIds.has(language.id));
    };

    return (
        <div className="translation-form-container">
            {/* Unit Form - only show if creating a new unit or editing an existing one without ID */}
            {!unit || (unit && !unit.id) ? (
                <form className="unit-form" onSubmit={handleUnitSubmit}>
                    <div className="mb-4">
                        <label htmlFor="sourceContent" className="block text-gray-700 font-medium mb-2">
                            Source Content <span className="text-red-500">*</span>
                        </label>
                        <textarea
                            id="sourceContent"
                            value={sourceContent}
                            onChange={(e) => setSourceContent(e.target.value)}
                            rows={5}
                            className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                errors.sourceContent ? 'border-red-500' : 'border-gray-300'
                            }`}
                            placeholder="Enter the original content to be translated"
                            disabled={isSaving}
                        />
                        {errors.sourceContent && (
                            <div className="text-red-500 text-sm mt-1">{errors.sourceContent}</div>
                        )}
                    </div>

                    <div className="mb-6">
                        <label htmlFor="context" className="block text-gray-700 font-medium mb-2">
                            Context (Optional)
                        </label>
                        <textarea
                            id="context"
                            value={context}
                            onChange={(e) => setContext(e.target.value)}
                            rows={3}
                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter any contextual information to help translators"
                            disabled={isSaving}
                        />
                    </div>

                    <div className="flex space-x-3">
                        <button
                            type="submit"
                            className={`bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors flex items-center ${
                                isSaving ? 'opacity-70 cursor-not-allowed' : ''
                            }`}
                            disabled={isSaving}
                        >
                            {isSaving && (
                                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            )}
                            Create Translation Unit
                        </button>
                        <button
                            type="button"
                            onClick={onCancel}
                            className="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition-colors"
                            disabled={isSaving}
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            ) : (
                // Translation form - only show for existing units
                <div className="translation-section">
                    {/* Display source content for reference */}
                    <div className="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                        <div className="text-sm text-gray-500 mb-1">Source Content:</div>
                        <div className="text-gray-700 whitespace-pre-wrap">{unit.source_content}</div>
                        {unit.context && (
                            <>
                                <div className="text-sm text-gray-500 mt-3 mb-1">Context:</div>
                                <div className="text-gray-700 whitespace-pre-wrap text-sm italic">{unit.context}</div>
                            </>
                        )}
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {/* Left column: Existing translations list */}
                        <div className="md:col-span-1">
                            <div className="mb-4">
                                <h3 className="text-lg font-semibold text-gray-700 mb-2">Existing Translations</h3>
                                {unit && unit.translations && unit.translations.length > 0 ? (
                                    <div className="space-y-2 max-h-96 overflow-y-auto pr-2">
                                        {unit.translations.map(translation => (
                                            <div
                                                key={translation.id}
                                                className={`p-3 rounded-md cursor-pointer transition-colors ${
                                                    selectedTranslation && selectedTranslation.id === translation.id
                                                        ? 'bg-blue-100 border border-blue-300'
                                                        : 'bg-gray-50 border border-gray-200 hover:bg-gray-100'
                                                }`}
                                                onClick={() => handleSelectTranslation(translation)}
                                            >
                                                <div className="flex justify-between items-center mb-1">
                          <span className="font-medium flex items-center">
                            {translation.language_name}
                              {translation.language_rtl && (
                                  <span className="ml-1 text-xs bg-yellow-100 text-yellow-800 px-1 rounded">RTL</span>
                              )}
                          </span>
                                                    <span className="text-xs text-gray-500">
                            Rev. {translation.revision_number || 1}
                          </span>
                                                </div>
                                                <div className="text-sm text-gray-600 truncate">
                                                    {translation.content.substring(0, 100)}
                                                    {translation.content.length > 100 ? '...' : ''}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-gray-500 text-sm italic bg-gray-50 p-4 rounded-md text-center">
                                        No translations yet
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Right column: Add/Edit translation form */}
                        <div className="md:col-span-2">
                            <form className="translation-form" onSubmit={handleTranslationSubmit}>
                                <h3 className="text-lg font-semibold text-gray-700 mb-4">
                                    {mode === 'add' ? 'Add New Translation' : 'Edit Translation'}
                                </h3>

                                <div className="mb-4">
                                    <label htmlFor="language" className="block text-gray-700 font-medium mb-2">
                                        Language <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="language"
                                        value={selectedLanguage}
                                        onChange={(e) => setSelectedLanguage(e.target.value)}
                                        disabled={mode === 'edit' || isSaving}
                                        className={`w-full px-3 py-2 border rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                            errors.language ? 'border-red-500' : 'border-gray-300'
                                        } ${mode === 'edit' ? 'bg-gray-100' : ''}`}
                                    >
                                        <option value="">Select a language</option>
                                        {getAvailableLanguages().map(language => (
                                            <option key={language.id} value={language.id}>
                                                {language.name} {language.is_rtl ? '(RTL)' : ''}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.language && (
                                        <div className="text-red-500 text-sm mt-1">{errors.language}</div>
                                    )}
                                </div>

                                <div className="mb-6">
                                    <label htmlFor="translationContent" className="block text-gray-700 font-medium mb-2">
                                        Translation Content <span className="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        id="translationContent"
                                        value={translationContent}
                                        onChange={(e) => setTranslationContent(e.target.value)}
                                        rows={6}
                                        className={`w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                            errors.translationContent ? 'border-red-500' : 'border-gray-300'
                                        }`}
                                        placeholder="Enter the translated content"
                                        disabled={isSaving}
                                        dir={selectedLanguage && languages.find(l => l.id.toString() === selectedLanguage)?.is_rtl ? 'rtl' : 'ltr'}
                                    />
                                    {errors.translationContent && (
                                        <div className="text-red-500 text-sm mt-1">{errors.translationContent}</div>
                                    )}
                                </div>

                                <div className="flex space-x-3">
                                    <button
                                        type="submit"
                                        className={`bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors flex items-center ${
                                            isSaving ? 'opacity-70 cursor-not-allowed' : ''
                                        }`}
                                        disabled={isSaving}
                                    >
                                        {isSaving && (
                                            <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        )}
                                        {mode === 'add' ? 'Add Translation' : 'Update Translation'}
                                    </button>

                                    {mode === 'edit' && (
                                        <button
                                            type="button"
                                            onClick={resetTranslationForm}
                                            className="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-md transition-colors"
                                            disabled={isSaving}
                                        >
                                            Cancel Edit
                                        </button>
                                    )}
                                </div>
                            </form>
                        </div>
                    </div>

                    <div className="mt-8 pt-6 border-t border-gray-200">
                        <button
                            type="button"
                            onClick={onCancel}
                            className="text-gray-600 hover:text-gray-800 transition-colors flex items-center"
                            disabled={isSaving}
                        >
                            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to View Mode
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default TranslationForm;
