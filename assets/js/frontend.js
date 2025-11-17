/**
 * Custom Product Designer Frontend JavaScript
 */

(function($) {
    'use strict';

    let canvasFront, canvasBack;
    let currentCanvas;
    let currentSide = 'front';
    let designData = {
        front: null,
        back: null
    };

    $(document).ready(function() {
        initializeDesigner();
    });

    function initializeDesigner() {
        // Open designer modal
        $('#cpd-open-designer').on('click', function(e) {
            e.preventDefault();
            $('#cpd-designer-modal').fadeIn();
            initializeCanvas();
        });

        // Close modal
        $('.cpd-close, #cpd-cancel-design').on('click', function() {
            $('#cpd-designer-modal').fadeOut();
        });

        // Close modal on outside click
        $(window).on('click', function(e) {
            if ($(e.target).is('#cpd-designer-modal')) {
                $('#cpd-designer-modal').fadeOut();
            }
        });
    }

    function initializeCanvas() {
        if (canvasFront) {
            return; // Already initialized
        }

        // Initialize front canvas
        canvasFront = new fabric.Canvas('cpd-canvas-front', {
            backgroundColor: '#ffffff',
            preserveObjectStacking: true
        });

        currentCanvas = canvasFront;

        // Load product background image if available
        const productImage = $('#cpd-product-image').val();
        if (productImage) {
            fabric.Image.fromURL(productImage, function(img) {
                img.set({
                    selectable: false,
                    evented: false,
                    scaleX: canvasFront.width / img.width,
                    scaleY: canvasFront.height / img.height
                });
                canvasFront.setBackgroundImage(img, canvasFront.renderAll.bind(canvasFront));
            });
        }

        // Initialize back canvas if enabled
        if ($('#cpd-enable-back').val() === '1') {
            canvasBack = new fabric.Canvas('cpd-canvas-back', {
                backgroundColor: '#ffffff',
                preserveObjectStacking: true
            });

            if (productImage) {
                fabric.Image.fromURL(productImage, function(img) {
                    img.set({
                        selectable: false,
                        evented: false,
                        scaleX: canvasBack.width / img.width,
                        scaleY: canvasBack.height / img.height
                    });
                    canvasBack.setBackgroundImage(img, canvasBack.renderAll.bind(canvasBack));
                });
            }

            initializeSideToggle();
        }

        initializeTools();
        initializeObjectEvents();
    }

    function initializeSideToggle() {
        $('#cpd-front-btn').on('click', function() {
            switchSide('front');
        });

        $('#cpd-back-btn').on('click', function() {
            switchSide('back');
        });
    }

    function switchSide(side) {
        currentSide = side;

        if (side === 'front') {
            $('#cpd-canvas-front').show();
            $('#cpd-canvas-back').hide();
            $('#cpd-front-btn').addClass('active');
            $('#cpd-back-btn').removeClass('active');
            currentCanvas = canvasFront;
        } else {
            $('#cpd-canvas-front').hide();
            $('#cpd-canvas-back').show();
            $('#cpd-front-btn').removeClass('active');
            $('#cpd-back-btn').addClass('active');
            currentCanvas = canvasBack;
        }

        currentCanvas.renderAll();
    }

    function initializeTools() {
        // Populate font dropdown
        populateFonts();

        // Add text
        $('#cpd-add-text').on('click', function() {
            $('#cpd-text-options').slideToggle();
        });

        // Text input - update selected text
        $('#cpd-text-input').on('input', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject && activeObject.type === 'i-text') {
                activeObject.set('text', $(this).val());
                currentCanvas.renderAll();
            } else {
                addText($(this).val() || 'Your Text Here');
            }
        });

        // Font family
        $('#cpd-font-family').on('change', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject && (activeObject.type === 'i-text' || activeObject.type === 'text')) {
                activeObject.set('fontFamily', $(this).val());
                currentCanvas.renderAll();
            }
        });

        // Font size
        $('#cpd-font-size').on('input', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject && (activeObject.type === 'i-text' || activeObject.type === 'text')) {
                activeObject.set('fontSize', parseInt($(this).val()));
                currentCanvas.renderAll();
            }
        });

        // Text color
        $('#cpd-text-color').on('change', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject && (activeObject.type === 'i-text' || activeObject.type === 'text')) {
                activeObject.set('fill', $(this).val());
                currentCanvas.renderAll();
            }
        });

        // Text style buttons
        $('#cpd-text-bold').on('click', function() {
            toggleTextStyle('fontWeight', 'bold', 'normal');
        });

        $('#cpd-text-italic').on('click', function() {
            toggleTextStyle('fontStyle', 'italic', 'normal');
        });

        $('#cpd-text-underline').on('click', function() {
            toggleTextProperty('underline');
        });

        // Text alignment
        $('#cpd-text-left').on('click', function() {
            setTextAlign('left');
        });

        $('#cpd-text-center').on('click', function() {
            setTextAlign('center');
        });

        $('#cpd-text-right').on('click', function() {
            setTextAlign('right');
        });

        // Image upload
        $('#cpd-upload-image').on('click', function() {
            $('#cpd-image-upload').click();
        });

        $('#cpd-image-upload').on('change', function(e) {
            const file = e.target.files[0];
            uploadImage(file);
            // Reset input so same file can be uploaded again
            $(this).val('');
        });

        // Shapes
        $('.cpd-add-shape').on('click', function() {
            const shape = $(this).data('shape');
            addShape(shape);
            $('#cpd-shape-options').slideDown();
        });

        $('#cpd-shape-color').on('change', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject && (activeObject.type === 'rect' || activeObject.type === 'circle' || activeObject.type === 'triangle')) {
                activeObject.set('fill', $(this).val());
                currentCanvas.renderAll();
            }
        });

        // Clipart
        loadClipart();

        // Filters
        $('.cpd-apply-filter').on('click', function() {
            const filter = $(this).data('filter');
            applyFilter(filter);
        });

        $('#cpd-remove-filters').on('click', function() {
            removeFilters();
        });

        // Actions
        $('#cpd-delete-object').on('click', function() {
            deleteSelected();
        });

        $('#cpd-clear-canvas').on('click', function() {
            if (confirm(cpdData.i18n.clear_confirm || 'Are you sure you want to clear all objects?')) {
                currentCanvas.clear();
                currentCanvas.backgroundColor = '#ffffff';
                currentCanvas.renderAll();
            }
        });

        // Save design
        $('#cpd-save-design').on('click', function() {
            saveDesign();
        });
    }

    function populateFonts() {
        const fonts = cpdData.fonts || ['Arial', 'Helvetica', 'Times New Roman'];
        const $select = $('#cpd-font-family');

        fonts.forEach(function(font) {
            $select.append($('<option>', {
                value: font,
                text: font
            }));
        });
    }

    function addText(text) {
        const textObj = new fabric.IText(text, {
            left: 100,
            top: 100,
            fontFamily: $('#cpd-font-family').val() || 'Arial',
            fontSize: parseInt($('#cpd-font-size').val()) || 24,
            fill: $('#cpd-text-color').val() || '#000000'
        });

        currentCanvas.add(textObj);
        currentCanvas.setActiveObject(textObj);
        currentCanvas.renderAll();
    }

    function toggleTextStyle(property, value1, value2) {
        const activeObject = currentCanvas.getActiveObject();
        if (activeObject && (activeObject.type === 'i-text' || activeObject.type === 'text')) {
            const currentValue = activeObject.get(property);
            activeObject.set(property, currentValue === value1 ? value2 : value1);
            currentCanvas.renderAll();
        }
    }

    function toggleTextProperty(property) {
        const activeObject = currentCanvas.getActiveObject();
        if (activeObject && (activeObject.type === 'i-text' || activeObject.type === 'text')) {
            activeObject.set(property, !activeObject.get(property));
            currentCanvas.renderAll();
        }
    }

    function setTextAlign(align) {
        const activeObject = currentCanvas.getActiveObject();
        if (activeObject && (activeObject.type === 'i-text' || activeObject.type === 'text')) {
            activeObject.set('textAlign', align);
            currentCanvas.renderAll();
        }
    }

    function uploadImage(file) {
        if (!file) {
            showNotification('Please select a file', 'error');
            return;
        }

        // Client-side validation
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        // Check file size
        if (file.size > maxSize) {
            showNotification('File is too large. Maximum size is 5MB.', 'error');
            return;
        }

        // Check file type
        if (!allowedTypes.includes(file.type)) {
            showNotification('Invalid file type. Please upload JPG, PNG, or GIF.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'cpd_upload_image');
        formData.append('nonce', cpdData.nonce);
        formData.append('image', file);

        showLoading();

        $.ajax({
            url: cpdData.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30 second timeout
            success: function(response) {
                hideLoading();
                if (response.success) {
                    addImageToCanvas(response.data.url);
                    showNotification('Image uploaded successfully!', 'success');
                } else {
                    showNotification(response.data.message || cpdData.i18n.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                let errorMsg = cpdData.i18n.error;

                if (status === 'timeout') {
                    errorMsg = 'Upload timed out. Please try again.';
                } else if (xhr.status === 413) {
                    errorMsg = 'File is too large for the server.';
                } else if (xhr.status === 0) {
                    errorMsg = 'Network error. Please check your connection.';
                }

                showNotification(errorMsg, 'error');
                console.error('Upload error:', status, error);
            }
        });
    }

    function addImageToCanvas(url) {
        if (!url) {
            showNotification('Invalid image URL', 'error');
            return;
        }

        showLoading();

        fabric.Image.fromURL(url, function(img) {
            hideLoading();

            if (!img || !img.width || !img.height) {
                showNotification('Failed to load image. Please try again.', 'error');
                return;
            }

            // Scale image if too large
            const maxWidth = currentCanvas.width * 0.5;
            const maxHeight = currentCanvas.height * 0.5;

            if (img.width > maxWidth || img.height > maxHeight) {
                const scale = Math.min(maxWidth / img.width, maxHeight / img.height);
                img.scale(scale);
            }

            img.set({
                left: currentCanvas.width / 2 - (img.width * img.scaleX) / 2,
                top: currentCanvas.height / 2 - (img.height * img.scaleY) / 2
            });

            currentCanvas.add(img);
            currentCanvas.setActiveObject(img);
            currentCanvas.renderAll();
        }, {
            crossOrigin: 'anonymous' // Handle CORS issues
        });
    }

    function addShape(shape) {
        let shapeObj;
        const color = $('#cpd-shape-color').val() || '#ff0000';

        switch (shape) {
            case 'rect':
                shapeObj = new fabric.Rect({
                    left: 100,
                    top: 100,
                    width: 100,
                    height: 100,
                    fill: color
                });
                break;
            case 'circle':
                shapeObj = new fabric.Circle({
                    left: 100,
                    top: 100,
                    radius: 50,
                    fill: color
                });
                break;
            case 'triangle':
                shapeObj = new fabric.Triangle({
                    left: 100,
                    top: 100,
                    width: 100,
                    height: 100,
                    fill: color
                });
                break;
        }

        if (shapeObj) {
            currentCanvas.add(shapeObj);
            currentCanvas.setActiveObject(shapeObj);
            currentCanvas.renderAll();
        }
    }

    function loadClipart() {
        const clipart = cpdData.clipart || [];
        const $container = $('#cpd-clipart-list');

        clipart.forEach(function(url) {
            const $img = $('<img>', {
                src: url,
                alt: 'Clipart',
                'class': 'cpd-clipart-item'
            });

            $img.on('click', function() {
                addImageToCanvas(url);
            });

            $container.append($img);
        });
    }

    function applyFilter(filterType) {
        const activeObject = currentCanvas.getActiveObject();
        if (!activeObject || activeObject.type !== 'image') {
            showNotification('Please select an image first', 'error');
            return;
        }

        let filter;

        switch (filterType) {
            case 'grayscale':
                filter = new fabric.Image.filters.Grayscale();
                break;
            case 'sepia':
                filter = new fabric.Image.filters.Sepia();
                break;
            case 'blur':
                filter = new fabric.Image.filters.Blur({ blur: 0.5 });
                break;
            case 'invert':
                filter = new fabric.Image.filters.Invert();
                break;
            case 'emboss':
                filter = new fabric.Image.filters.Convolute({
                    matrix: [1, 1, 1, 1, 0.7, -1, -1, -1, -1]
                });
                break;
            case 'sharpen':
                filter = new fabric.Image.filters.Convolute({
                    matrix: [0, -1, 0, -1, 5, -1, 0, -1, 0]
                });
                break;
        }

        if (filter) {
            if (!activeObject.filters) {
                activeObject.filters = [];
            }
            activeObject.filters.push(filter);
            activeObject.applyFilters();
            currentCanvas.renderAll();
        }
    }

    function removeFilters() {
        const activeObject = currentCanvas.getActiveObject();
        if (activeObject && activeObject.type === 'image') {
            activeObject.filters = [];
            activeObject.applyFilters();
            currentCanvas.renderAll();
        }
    }

    function deleteSelected() {
        const activeObject = currentCanvas.getActiveObject();
        if (activeObject) {
            currentCanvas.remove(activeObject);
            currentCanvas.renderAll();
        }
    }

    function initializeObjectEvents() {
        // Update controls when object is selected
        canvasFront.on('selection:created', updateControls);
        canvasFront.on('selection:updated', updateControls);

        if (canvasBack) {
            canvasBack.on('selection:created', updateControls);
            canvasBack.on('selection:updated', updateControls);
        }
    }

    function updateControls() {
        const activeObject = currentCanvas.getActiveObject();
        if (!activeObject) return;

        if (activeObject.type === 'i-text' || activeObject.type === 'text') {
            $('#cpd-text-input').val(activeObject.text);
            $('#cpd-font-family').val(activeObject.fontFamily);
            $('#cpd-font-size').val(activeObject.fontSize);
            $('#cpd-text-color').val(activeObject.fill);
            $('#cpd-text-options').slideDown();
        } else if (activeObject.type === 'rect' || activeObject.type === 'circle' || activeObject.type === 'triangle') {
            $('#cpd-shape-color').val(activeObject.fill);
            $('#cpd-shape-options').slideDown();
        }
    }

    function saveDesign() {
        showLoading();

        // Save design data
        const frontData = canvasFront.toJSON();
        const frontImage = canvasFront.toDataURL({ format: 'png', quality: 1 });

        let backData = null;
        let backImage = null;

        if (canvasBack) {
            backData = canvasBack.toJSON();
            backImage = canvasBack.toDataURL({ format: 'png', quality: 1 });
        }

        designData = {
            front: frontData,
            back: backData
        };

        // Send to server
        $.ajax({
            url: cpdData.ajax_url,
            type: 'POST',
            data: {
                action: 'cpd_save_design',
                nonce: cpdData.nonce,
                design_data: JSON.stringify(designData),
                design_image_front: frontImage,
                design_image_back: backImage
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    // Add hidden fields to form
                    const $form = $('form.cart');
                    $form.find('input[name="cpd_design_data"]').remove();
                    $form.find('input[name="cpd_design_image"]').remove();

                    $form.append($('<input>', {
                        type: 'hidden',
                        name: 'cpd_design_data',
                        value: JSON.stringify(designData)
                    }));

                    $form.append($('<input>', {
                        type: 'hidden',
                        name: 'cpd_design_image',
                        value: frontImage
                    }));

                    showNotification(cpdData.i18n.design_saved || 'Design saved! You can now add to cart.');

                    // Close modal after short delay
                    setTimeout(function() {
                        $('#cpd-designer-modal').fadeOut();
                        // Trigger add to cart
                        $form.find('button[type="submit"]').click();
                    }, 1500);
                } else {
                    showNotification(response.data.message || cpdData.i18n.error, 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotification(cpdData.i18n.error, 'error');
            }
        });
    }

    function showLoading() {
        if ($('.cpd-loading').length === 0) {
            $('.cpd-canvas-container').append(
                '<div class="cpd-loading"><div class="cpd-spinner"></div></div>'
            );
        }
    }

    function hideLoading() {
        $('.cpd-loading').remove();
    }

    function showNotification(message, type) {
        // Remove any existing notifications
        $('.cpd-notification').remove();

        const $notification = $('<div>', {
            'class': 'cpd-notification' + (type === 'error' ? ' error' : '') + (type === 'success' ? ' success' : ''),
            text: message
        });

        $('body').append($notification);

        const duration = type === 'error' ? 4000 : 2500;

        setTimeout(function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        }, duration);
    }

})(jQuery);
