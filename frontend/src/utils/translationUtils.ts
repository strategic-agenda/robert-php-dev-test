import {
  type TranslationUnit,
  type Translation,
} from "../services/translationService";

/**
 * Utility functions for working with translations
 */
export const translationUtils = {
  /**
   * Get a readable status label from status code
   */
  getStatusLabel: (status: string): string => {
    switch (status) {
      case "draft":
        return "Draft";
      case "reviewed":
        return "Reviewed";
      case "approved":
        return "Approved";
      case "rejected":
        return "Rejected";
      default:
        return "Unknown";
    }
  },

  /**
   * Get status color class for Bootstrap
   */
  getStatusColorClass: (status: string): string => {
    switch (status) {
      case "draft":
        return "secondary";
      case "reviewed":
        return "info";
      case "approved":
        return "success";
      case "rejected":
        return "danger";
      default:
        return "light";
    }
  },

  /**
   * Check if a unit has translation in target language
   */
  hasTranslation: (unit: TranslationUnit, languageId: number): boolean => {
    return !!unit.translations[languageId];
  },

  /**
   * Get translation for a specific language from a unit
   */
  getTranslation: (
    unit: TranslationUnit,
    languageId: number
  ): Translation | null => {
    return unit.translations[languageId] || null;
  },

  /**
   * Check if a user can edit a translation
   * (in a real app, this would check user roles and permissions)
   */
  canEditTranslation: (
    unit: TranslationUnit,
    languageId: number,
    userId: number
  ): boolean => {
    const translation = unit.translations[languageId];

    // If no translation exists, any user can add one
    if (!translation) {
      return true;
    }

    // For demo purposes: users can edit their own translations
    // or translations in draft status
    return (
      translation.translated_by === userId ||
      translation.status === "draft" ||
      // Add this line in real app to check reviewer role
      true
    );
  },

  /**
   * Check if a user can review a translation
   * (in a real app, this would check user roles)
   */
  canReviewTranslation: (userId: number): boolean => {
    // For demo purposes, all users can review
    // In a real app, this would check user roles
    return true;
  },

  /**
   * Get a summary of translation progress for a document
   */
  getTranslationProgress: (
    units: TranslationUnit[],
    languageId: number
  ): {
    total: number;
    translated: number;
    approved: number;
    reviewed: number;
    rejected: number;
    draft: number;
    percentage: number;
  } => {
    const total = units.length;
    let translated = 0;
    let approved = 0;
    let reviewed = 0;
    let rejected = 0;
    let draft = 0;

    units.forEach((unit) => {
      const translation = unit.translations[languageId];
      if (translation) {
        translated++;

        switch (translation.status) {
          case "approved":
            approved++;
            break;
          case "reviewed":
            reviewed++;
            break;
          case "rejected":
            rejected++;
            break;
          case "draft":
            draft++;
            break;
        }
      }
    });

    const percentage = total > 0 ? Math.round((translated / total) * 100) : 0;

    return {
      total,
      translated,
      approved,
      reviewed,
      rejected,
      draft,
      percentage,
    };
  },

  /**
   * Sort units by sequence number
   */
  sortUnitsBySequence: (units: TranslationUnit[]): TranslationUnit[] => {
    return [...units].sort((a, b) => a.sequence_number - b.sequence_number);
  },

  /**
   * Format date for display
   */
  formatDate: (dateString: string | undefined): string => {
    if (!dateString) {
      return "N/A";
    }

    try {
      return new Date(dateString).toLocaleString();
    } catch (e) {
      return "Invalid date";
    }
  },

  /**
   * Get user display name (in a real app, this would fetch from a user service)
   */
  getUserDisplayName: (userId: number): string => {
    // Mock implementation - in a real app would fetch from user service
    const mockUsers: Record<number, string> = {
      1: "John Doe",
      2: "Jane Smith",
      3: "Robert Johnson",
    };

    return mockUsers[userId] || `User ${userId}`;
  },
};

export default translationUtils;
