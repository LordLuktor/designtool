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
            const selectedFont = $(this).val();
            const activeObject = currentCanvas.getActiveObject();

            // Update select element to show selected font
            $(this).css('font-family', selectedFont);

            if (activeObject && (activeObject.type === 'i-text' || activeObject.type === 'text')) {
                activeObject.set('fontFamily', selectedFont);
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
            uploadImage(e.target.files[0]);
        });

        // Shapes
        $('.cpd-add-shape').on('click', function() {
            const shape = $(this).data('shape');
            addShape(shape);
            $('#cpd-shape-options').slideDown();
        });

        $('#cpd-shape-color').on('change', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject && (activeObject.type === 'rect' || activeObject.type === 'circle' || activeObject.type === 'triangle' || activeObject.type === 'polygon' || activeObject.type === 'path')) {
                activeObject.set('fill', $(this).val());
                currentCanvas.renderAll();
            }
        });

        $('#cpd-shape-stroke-color').on('change', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject && (activeObject.type === 'rect' || activeObject.type === 'circle' || activeObject.type === 'triangle' || activeObject.type === 'polygon' || activeObject.type === 'line' || activeObject.type === 'path')) {
                activeObject.set('stroke', $(this).val());
                currentCanvas.renderAll();
            }
        });

        $('#cpd-shape-stroke-width').on('input', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject && (activeObject.type === 'rect' || activeObject.type === 'circle' || activeObject.type === 'triangle' || activeObject.type === 'polygon' || activeObject.type === 'line' || activeObject.type === 'path')) {
                activeObject.set('strokeWidth', parseInt($(this).val()));
                currentCanvas.renderAll();
            }
        });

        // Background color
        $('#cpd-apply-bg-color').on('click', function() {
            const color = $('#cpd-canvas-bg-color').val();
            currentCanvas.setBackgroundColor(color, currentCanvas.renderAll.bind(currentCanvas));
        });

        // Alignment buttons
        $('#cpd-align-left').on('click', function() {
            alignObject('left');
        });

        $('#cpd-align-center').on('click', function() {
            alignObject('center');
        });

        $('#cpd-align-right').on('click', function() {
            alignObject('right');
        });

        $('#cpd-align-top').on('click', function() {
            alignObject('top');
        });

        $('#cpd-align-middle').on('click', function() {
            alignObject('middle');
        });

        $('#cpd-align-bottom').on('click', function() {
            alignObject('bottom');
        });

        // Layer order
        $('#cpd-bring-forward').on('click', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject) {
                currentCanvas.bringForward(activeObject);
                currentCanvas.renderAll();
            }
        });

        $('#cpd-send-backward').on('click', function() {
            const activeObject = currentCanvas.getActiveObject();
            if (activeObject) {
                currentCanvas.sendBackwards(activeObject);
                currentCanvas.renderAll();
            }
        });

        // Clipart
        loadClipart();

        // Free Icons
        loadIconCollections();

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
            const $option = $('<option>', {
                value: font,
                text: font
            });

            // Apply font style to option
            $option.css('font-family', font);
            $select.append($option);
        });

        // Apply font to the select element itself when changed
        $select.css('font-family', fonts[0]);
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

    function uploadImage(file, retryCount = 0) {
        if (!file) return;

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Invalid file type. Please upload a JPG, PNG, or GIF image.', 'error');
            return;
        }

        // Validate file size (5MB default)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showNotification('File size exceeds maximum allowed size of 5 MB', 'error');
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

                // Retry logic for network errors
                if (retryCount < 3 && (status === 'timeout' || status === 'error')) {
                    const delay = Math.pow(2, retryCount) * 1000; // Exponential backoff: 1s, 2s, 4s
                    showNotification('Upload failed. Retrying in ' + (delay/1000) + ' seconds...', 'error');

                    setTimeout(function() {
                        uploadImage(file, retryCount + 1);
                    }, delay);
                } else {
                    let errorMsg = cpdData.i18n.error || 'An error occurred. Please try again.';

                    if (status === 'timeout') {
                        errorMsg = 'Upload timed out. Please check your connection and try again.';
                    } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMsg = xhr.responseJSON.data.message;
                    }

                    showNotification(errorMsg, 'error');
                }
            }
        });
    }

    function addImageToCanvas(url) {
        fabric.Image.fromURL(url, function(img) {
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
        });
    }

    function addShape(shape) {
        let shapeObj;
        const color = $('#cpd-shape-color').val() || '#ff0000';
        const strokeColor = $('#cpd-shape-stroke-color').val() || '#000000';
        const strokeWidth = parseInt($('#cpd-shape-stroke-width').val()) || 0;

        switch (shape) {
            case 'rect':
                shapeObj = new fabric.Rect({
                    left: 100,
                    top: 100,
                    width: 100,
                    height: 100,
                    fill: color,
                    stroke: strokeColor,
                    strokeWidth: strokeWidth
                });
                break;
            case 'circle':
                shapeObj = new fabric.Circle({
                    left: 100,
                    top: 100,
                    radius: 50,
                    fill: color,
                    stroke: strokeColor,
                    strokeWidth: strokeWidth
                });
                break;
            case 'triangle':
                shapeObj = new fabric.Triangle({
                    left: 100,
                    top: 100,
                    width: 100,
                    height: 100,
                    fill: color,
                    stroke: strokeColor,
                    strokeWidth: strokeWidth
                });
                break;
            case 'star':
                // Create a 5-pointed star
                const starPoints = createStarPoints(5, 50, 25);
                shapeObj = new fabric.Polygon(starPoints, {
                    left: 100,
                    top: 100,
                    fill: color,
                    stroke: strokeColor,
                    strokeWidth: strokeWidth
                });
                break;
            case 'polygon':
                // Create a hexagon
                const hexPoints = createPolygonPoints(6, 50);
                shapeObj = new fabric.Polygon(hexPoints, {
                    left: 100,
                    top: 100,
                    fill: color,
                    stroke: strokeColor,
                    strokeWidth: strokeWidth
                });
                break;
            case 'line':
                shapeObj = new fabric.Line([50, 50, 200, 50], {
                    left: 100,
                    top: 100,
                    stroke: strokeColor,
                    strokeWidth: strokeWidth || 2,
                    fill: null
                });
                break;
        }

        if (shapeObj) {
            currentCanvas.add(shapeObj);
            currentCanvas.setActiveObject(shapeObj);
            currentCanvas.renderAll();
        }
    }

    function createStarPoints(points, outerRadius, innerRadius) {
        const step = Math.PI / points;
        const starPoints = [];

        for (let i = 0; i < points * 2; i++) {
            const radius = i % 2 === 0 ? outerRadius : innerRadius;
            const angle = i * step - Math.PI / 2;
            starPoints.push({
                x: radius * Math.cos(angle),
                y: radius * Math.sin(angle)
            });
        }

        return starPoints;
    }

    function createPolygonPoints(sides, radius) {
        const points = [];
        const angle = (Math.PI * 2) / sides;

        for (let i = 0; i < sides; i++) {
            points.push({
                x: radius * Math.cos(angle * i - Math.PI / 2),
                y: radius * Math.sin(angle * i - Math.PI / 2)
            });
        }

        return points;
    }

    function alignObject(alignment) {
        const activeObject = currentCanvas.getActiveObject();
        if (!activeObject) {
            showNotification('Please select an object first', 'error');
            return;
        }

        switch (alignment) {
            case 'left':
                activeObject.set('left', 0);
                break;
            case 'center':
                activeObject.set('left', (currentCanvas.width - activeObject.width * activeObject.scaleX) / 2);
                break;
            case 'right':
                activeObject.set('left', currentCanvas.width - activeObject.width * activeObject.scaleX);
                break;
            case 'top':
                activeObject.set('top', 0);
                break;
            case 'middle':
                activeObject.set('top', (currentCanvas.height - activeObject.height * activeObject.scaleY) / 2);
                break;
            case 'bottom':
                activeObject.set('top', currentCanvas.height - activeObject.height * activeObject.scaleY);
                break;
        }

        activeObject.setCoords();
        currentCanvas.renderAll();
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

    function loadIconCollections() {
        const collections = cpdData.icon_collections || [];
        const $select = $('#cpd-icon-collection');
        const $iconList = $('#cpd-icon-list');

        // Populate collection dropdown
        collections.forEach(function(collection) {
            $select.append($('<option>', {
                value: collection.id,
                text: collection.name,
                'data-icons': JSON.stringify(collection.sample_icons)
            }));
        });

        // Load first collection by default
        if (collections.length > 0) {
            loadIconSet(collections[0].sample_icons);
        }

        // Handle collection change
        $select.on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const icons = JSON.parse(selectedOption.attr('data-icons'));
            loadIconSet(icons);
        });

        function loadIconSet(icons) {
            $iconList.empty();

            icons.forEach(function(iconName) {
                const $iconContainer = $('<div>', {
                    'class': 'cpd-icon-item',
                    'title': iconName
                });

                // Create Iconify icon element
                const $icon = $('<span>', {
                    'class': 'iconify',
                    'data-icon': iconName,
                    'data-width': '32',
                    'data-height': '32'
                });

                $iconContainer.append($icon);

                $iconContainer.on('click', function() {
                    addIconToCanvas(iconName);
                });

                $iconList.append($iconContainer);
            });

            // Trigger Iconify to load the icons
            if (typeof Iconify !== 'undefined') {
                Iconify.scan();
            }
        }
    }

    function addIconToCanvas(iconName) {
        showLoading();

        // Get SVG from Iconify
        if (typeof Iconify !== 'undefined') {
            Iconify.renderSVG(iconName, {
                width: 100,
                height: 100
            }).then(function(svg) {
                if (svg) {
                    // Convert SVG to string
                    const serializer = new XMLSerializer();
                    const svgString = serializer.serializeToString(svg);

                    // Create data URL
                    const svgBlob = new Blob([svgString], {type: 'image/svg+xml'});
                    const url = URL.createObjectURL(svgBlob);

                    // Add to canvas
                    fabric.loadSVGFromURL(url, function(objects, options) {
                        const obj = fabric.util.groupSVGElements(objects, options);

                        obj.set({
                            left: currentCanvas.width / 2 - (obj.width * obj.scaleX) / 2,
                            top: currentCanvas.height / 2 - (obj.height * obj.scaleY) / 2,
                            scaleX: 1,
                            scaleY: 1
                        });

                        currentCanvas.add(obj);
                        currentCanvas.setActiveObject(obj);
                        currentCanvas.renderAll();

                        hideLoading();
                        URL.revokeObjectURL(url);
                    });
                } else {
                    hideLoading();
                    showNotification('Failed to load icon', 'error');
                }
            }).catch(function() {
                hideLoading();
                showNotification('Failed to load icon', 'error');
            });
        } else {
            hideLoading();
            showNotification('Icon library not loaded', 'error');
        }
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
        } else if (activeObject.type === 'rect' || activeObject.type === 'circle' || activeObject.type === 'triangle' || activeObject.type === 'polygon' || activeObject.type === 'line' || activeObject.type === 'path') {
            $('#cpd-shape-color').val(activeObject.fill || '#ff0000');
            $('#cpd-shape-stroke-color').val(activeObject.stroke || '#000000');
            $('#cpd-shape-stroke-width').val(activeObject.strokeWidth || 0);
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
        const $notification = $('<div>', {
            'class': 'cpd-notification' + (type === 'error' ? ' error' : ''),
            text: message
        });

        $('body').append($notification);

        setTimeout(function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);
