// Fix for Filament FileUpload infinite loading on broken images
document.addEventListener('DOMContentLoaded', function() {
    let uploadTimeouts = new Map();
    
    // Function to clear loading states
    function clearLoadingStates(container) {
        const loadingElements = container.querySelectorAll('.animate-spin, .loading, .fi-fo-file-upload-state-loading');
        loadingElements.forEach(el => {
            el.classList.remove('animate-spin', 'loading', 'fi-fo-file-upload-state-loading');
            if (el.classList.contains('fi-icon-loading')) {
                el.remove();
            }
        });
        
        // Remove any loading classes from parent elements
        container.classList.remove('fi-loading');
        const loadingParents = container.querySelectorAll('.fi-loading');
        loadingParents.forEach(el => el.classList.remove('fi-loading'));
    }
    
    // Function to reset file upload
    function resetFileUpload(container, showError = false) {
        const fileInputs = container.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.value = '';
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        clearLoadingStates(container);
        
        if (showError && window.$wire) {
            setTimeout(() => {
                window.$wire.dispatch('notify', {
                    type: 'danger',
                    title: 'Upload Error',
                    body: 'The image file could not be processed. Please try a different image.'
                });
            }, 100);
        }
    }
    
    // Monitor for new file upload containers (for dynamic content)
    function setupFileUploadHandlers() {
        const fileUploadContainers = document.querySelectorAll('[x-data*="fileUpload"], .fi-fo-file-upload');
        
        fileUploadContainers.forEach(container => {
            if (container.dataset.uploadHandlerSet) return;
            container.dataset.uploadHandlerSet = 'true';
            
            // Add event listener for file selection
            const fileInputs = container.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function(event) {
                    const files = event.target.files;
                    const containerId = container.id || Math.random().toString(36);
                    
                    // Clear any existing timeout for this container
                    if (uploadTimeouts.has(containerId)) {
                        clearTimeout(uploadTimeouts.get(containerId));
                    }
                    
                    if (files && files.length > 0) {
                        // Set aggressive timeout for any processing
                        const timeout = setTimeout(() => {
                            console.warn('Upload processing timeout - resetting upload');
                            resetFileUpload(container, true);
                            uploadTimeouts.delete(containerId);
                        }, 5000); // 5 second timeout
                        
                        uploadTimeouts.set(containerId, timeout);
                        
                        Array.from(files).forEach(file => {
                            // Validate file size immediately
                            if (file.size > 5 * 1024 * 1024) { // 5MB
                                clearTimeout(timeout);
                                resetFileUpload(container, false);
                                if (window.$wire) {
                                    window.$wire.dispatch('notify', {
                                        type: 'danger',
                                        title: 'File Too Large',
                                        body: 'The image must be smaller than 5MB.'
                                    });
                                }
                                return;
                            }
                            
                            // Check if it's an image file
                            if (file.type.startsWith('image/')) {
                                // Create an image to test if it loads
                                const img = new Image();
                                let imageLoaded = false;
                                
                                img.onload = function() {
                                    imageLoaded = true;
                                    clearTimeout(timeout);
                                    uploadTimeouts.delete(containerId);
                                    URL.revokeObjectURL(img.src);
                                };
                                
                                img.onerror = function() {
                                    imageLoaded = true;
                                    clearTimeout(timeout);
                                    console.error('Invalid image file detected');
                                    resetFileUpload(container, false);
                                    uploadTimeouts.delete(containerId);
                                    URL.revokeObjectURL(img.src);
                                    
                                    if (window.$wire) {
                                        window.$wire.dispatch('notify', {
                                            type: 'danger',
                                            title: 'Invalid Image',
                                            body: 'The selected image file is corrupted or invalid.'
                                        });
                                    }
                                };
                                
                                // Additional safety timeout for image loading
                                setTimeout(() => {
                                    if (!imageLoaded) {
                                        console.warn('Image loading timeout');
                                        resetFileUpload(container, true);
                                        URL.revokeObjectURL(img.src);
                                    }
                                }, 3000);
                                
                                // Test load the image
                                img.src = URL.createObjectURL(file);
                            } else {
                                // Clear timeout for non-image files
                                clearTimeout(timeout);
                                uploadTimeouts.delete(containerId);
                            }
                        });
                    } else {
                        // Clear timeout if no files selected
                        clearTimeout(uploadTimeouts.get(containerId));
                        uploadTimeouts.delete(containerId);
                    }
                });
            });
            
            // Add observer to watch for loading state changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && 
                        (mutation.attributeName === 'class' || mutation.attributeName === 'x-data')) {
                        const hasLoading = container.classList.contains('fi-loading') || 
                                         container.querySelector('.animate-spin, .fi-fo-file-upload-state-loading');
                        
                        if (hasLoading) {
                            const containerId = container.id || Math.random().toString(36);
                            if (!uploadTimeouts.has(containerId)) {
                                // Set fallback timeout if loading state detected without file change
                                const fallbackTimeout = setTimeout(() => {
                                    console.warn('Stuck loading state detected - clearing');
                                    clearLoadingStates(container);
                                    uploadTimeouts.delete(containerId);
                                }, 8000);
                                uploadTimeouts.set(containerId, fallbackTimeout);
                            }
                        }
                    }
                });
            });
            
            observer.observe(container, { 
                attributes: true, 
                childList: true, 
                subtree: true,
                attributeFilter: ['class', 'x-data']
            });
        });
    }
    
    // Initial setup
    setupFileUploadHandlers();
    
    // Re-run setup when new content is added (for Livewire updates)
    const bodyObserver = new MutationObserver(function(mutations) {
        let shouldSetup = false;
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && 
                        (node.matches('[x-data*="fileUpload"], .fi-fo-file-upload') ||
                         node.querySelector('[x-data*="fileUpload"], .fi-fo-file-upload'))) {
                        shouldSetup = true;
                    }
                });
            }
        });
        
        if (shouldSetup) {
            setTimeout(setupFileUploadHandlers, 100);
        }
    });
    
    bodyObserver.observe(document.body, { childList: true, subtree: true });
    
    // Handle delete/remove button clicks for stuck uploads
    document.addEventListener('click', function(event) {
        const button = event.target.closest('button');
        if (button) {
            const container = button.closest('[x-data*="fileUpload"], .fi-fo-file-upload');
            if (container) {
                // If it's a remove/delete button, force clear loading states
                if (button.getAttribute('title')?.includes('Remove') || 
                    button.querySelector('[title*="Remove"]') ||
                    button.classList.contains('fi-ac-icon-btn-action--remove')) {
                    
                    setTimeout(() => {
                        clearLoadingStates(container);
                        const containerId = container.id || 'unknown';
                        if (uploadTimeouts.has(containerId)) {
                            clearTimeout(uploadTimeouts.get(containerId));
                            uploadTimeouts.delete(containerId);
                        }
                    }, 100);
                }
            }
        }
    });
});