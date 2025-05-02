import React from 'react';

const LoadingSpinner = ({
                            message = "Loading data...",
                            size = "medium",
                            fullScreen = false
                        }) => {

    // Define spinner sizes
    const sizes = {
        small: {
            container: "py-6",
            spinner: "w-8 h-8 border-3",
            text: "text-sm"
        },
        medium: {
            container: "py-12",
            spinner: "w-12 h-12 border-4",
            text: "text-base"
        },
        large: {
            container: "py-16",
            spinner: "w-16 h-16 border-[5px]",
            text: "text-lg"
        }
    };

    const selectedSize = sizes[size] || sizes.medium;

    // Full screen overlay styles
    const fullScreenStyles = fullScreen ? "fixed inset-0 bg-gray-800 bg-opacity-50 z-50 flex" : "";

    return (
        <div className={`flex flex-col items-center justify-center ${selectedSize.container} ${fullScreenStyles}`}>
            <div
                className={`${selectedSize.spinner} border-blue-500 border-t-transparent rounded-full animate-spin`}
                role="status"
                aria-label="Loading"
            ></div>
            {message && (
                <p className={`mt-4 text-gray-600 ${selectedSize.text}`}>{message}</p>
            )}
        </div>
    );
};

export default LoadingSpinner;
