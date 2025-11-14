# Custom Product Designer for WooCommerce

A comprehensive WordPress plugin that allows customers to design and customize products like t-shirts, cups, caps, cards, and more with text, images, shapes, clipart, and visual effects.

## Features

### Core Customization Features
- **Text Customization**: Add and edit text with multiple font options (Google Fonts support)
  - Font family selection
  - Font size adjustment
  - Color picker
  - Text styling (bold, italic, underline)
  - Text alignment (left, center, right)

- **Image Upload**: Allow customers to upload their own images
  - File size limits (configurable)
  - Supported formats: JPG, PNG, GIF
  - Automatic image scaling

- **Shapes**: Add geometric shapes to designs
  - Rectangle
  - Circle
  - Triangle
  - Customizable colors

- **Clipart Library**: Pre-loaded clipart for quick customization
  - Easy-to-use clipart manager
  - Upload and delete clipart from admin panel

- **Visual Effects**: Apply filters to images
  - Grayscale
  - Sepia
  - Blur
  - Invert
  - Emboss
  - Sharpen

### Product Design Capabilities
- **Front & Back Design**: Design both sides of products
- **Real-time Canvas**: HTML5 canvas with Fabric.js
- **Drag & Drop**: Intuitive interface for object manipulation
- **Layer Management**: Organize design elements
- **Design Preview**: See designs in cart and order pages

### Admin Features
- **Product Settings**: Enable designer per product
- **Canvas Configuration**: Set custom canvas dimensions
- **Extra Cost**: Charge additional fees for customization
- **Global Settings**: Configure colors, features, and limits
- **Clipart Manager**: Upload and manage clipart library
- **Order Management**: View customer designs in admin orders

### WooCommerce Integration
- **Cart Integration**: Display design previews in cart
- **Checkout Integration**: Include designs in order details
- **Order Metadata**: Save design data with orders
- **Price Management**: Add extra cost for customization
- **Email Notifications**: Design previews in order emails

## Installation

1. Upload the `custom-product-designer` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce is installed and activated
4. Configure settings under 'Product Designer' menu

## Configuration

### Per-Product Settings

1. Edit a product in WooCommerce
2. Go to the 'Product Designer' tab
3. Check 'Enable Designer'
4. Configure options:
   - Enable back design (optional)
   - Set extra cost for customization
   - Set canvas width and height
   - Add product background image URL

### Global Settings

Navigate to **Product Designer > Settings** to configure:

- **Designer Heading**: Custom heading for designer interface
- **Colors**: Canvas background, text color, button color
- **Features**: Enable/disable text, images, shapes, clipart, effects
- **Upload Limits**: Maximum file size and allowed image types

### Clipart Manager

Navigate to **Product Designer > Clipart Manager** to:

- Upload new clipart images
- View existing clipart library
- Delete unwanted clipart

## Usage

### For Store Owners

1. Create or edit a product
2. Enable the Product Designer in the product settings
3. Set canvas dimensions and optional background image
4. Save the product
5. Customers will see a "Customize Product" button on the product page

### For Customers

1. Click the "Customize Product" button on a product page
2. Use the designer toolbar to:
   - Add text with custom fonts and colors
   - Upload personal images
   - Add shapes and clipart
   - Apply visual effects
   - Design front and back (if enabled)
3. Click "Save & Add to Cart"
4. Design preview appears in cart and checkout
5. Order includes design images for production

## Technical Details

### Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 5.0 or higher

### Technologies Used

- **Fabric.js 5.3.0**: HTML5 canvas library for object manipulation
- **Google Fonts API**: Web font integration
- **WordPress/WooCommerce APIs**: Seamless integration

### File Structure

```
custom-product-designer/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   ├── js/
│   │   ├── admin.js
│   │   └── frontend.js
│   ├── clipart/
│   └── images/
├── includes/
│   ├── class-cpd-admin.php
│   ├── class-cpd-ajax.php
│   ├── class-cpd-cart.php
│   └── class-cpd-frontend.php
├── languages/
├── custom-product-designer.php
├── uninstall.php
└── README.md
```

### Hooks and Filters

The plugin provides several hooks for developers to extend functionality:

#### Actions
- `cpd_before_designer_init`: Before designer initialization
- `cpd_after_save_design`: After design is saved
- `cpd_before_add_to_cart`: Before adding customized product to cart

#### Filters
- `cpd_available_fonts`: Modify available fonts list
- `cpd_canvas_settings`: Modify canvas settings
- `cpd_max_upload_size`: Change maximum upload size
- `cpd_allowed_image_types`: Modify allowed image types

## Customization

### Adding Custom Fonts

Add this code to your theme's functions.php:

```php
add_filter('cpd_available_fonts', function($fonts) {
    $fonts[] = 'Your Custom Font';
    return $fonts;
});
```

### Changing Canvas Background

```php
add_filter('cpd_canvas_settings', function($settings) {
    $settings['backgroundColor'] = '#f0f0f0';
    return $settings;
});
```

## Troubleshooting

### Designer button not showing
- Ensure WooCommerce is active
- Check if designer is enabled in product settings
- Clear browser cache

### Images not uploading
- Check file size limits in settings
- Verify upload directory permissions
- Ensure allowed image types match uploaded file

### Design not saving to cart
- Check browser console for JavaScript errors
- Verify AJAX is working properly
- Ensure WooCommerce sessions are enabled

## Support

For issues, questions, or feature requests:
- GitHub: https://github.com/LordLuktor/designtool
- Create an issue in the repository

## Changelog

### Version 1.0.4
- **Enhanced Image Upload Reliability**: Added comprehensive error handling and validation
  - Automatic directory creation and permission checks
  - Detailed error messages for upload failures
  - Client-side retry logic with exponential backoff (up to 3 retries)
  - File validation before upload (type and size checks)
  - 30-second timeout for upload requests
  - MIME type validation for enhanced security
- **Expanded Font Library**: Added 20+ new fonts including script, handwriting, and display fonts
  - Sans-serif: Ubuntu, Nunito, Bebas Neue, Anton, Righteous
  - Serif: Merriweather, Abril Fatface
  - Script & Handwriting: Pacifico, Dancing Script, Lobster, Caveat, Permanent Marker, Indie Flower, Shadows Into Light, Satisfy, Great Vibes
  - Monospace: Courier Prime
- **Font Preview in Dropdown**: Each font name now displays in its own font for easy preview
- **Background Color Control**: Added ability to change canvas background color
- **New Shapes**: Expanded shape library with:
  - Star (5-pointed)
  - Hexagon (regular polygon)
  - Line tool
  - Stroke color and width controls for all shapes
- **Advanced Object Positioning**: New alignment and layer controls
  - Horizontal alignment: Left, Center, Right
  - Vertical alignment: Top, Middle, Bottom
  - Layer order: Bring Forward, Send Backward
- **Improved Shape Controls**: Added stroke color and width options for all shapes
- Applied `cpd_available_fonts` filter for custom font additions

### Version 1.0.3
- Fixed designer modal sizing to fit viewport without scrolling
- Improved responsive layout with flexbox
- Canvas and toolbar now properly constrained to window size
- Better mobile and tablet experience

### Version 1.0.2
- Fixed WooCommerce compatibility warnings
- Added HPOS (High-Performance Order Storage) compatibility declaration
- Added Cart and Checkout Blocks compatibility
- Updated WooCommerce tested up to version 9.0

### Version 1.0.1
- Security release - Fixed critical and high-severity vulnerabilities
- Fixed SQL injection vulnerability
- Enhanced file upload security
- Added authorization checks
- Improved input validation

### Version 1.0.0
- Initial release
- Text customization with Google Fonts
- Image upload and manipulation
- Shapes and clipart support
- Visual effects (filters)
- Front and back design capability
- WooCommerce integration
- Admin settings panel
- Order management

## License

GPL v2 or later - https://www.gnu.org/licenses/gpl-2.0.html

## Credits

- Fabric.js - http://fabricjs.com/
- Google Fonts - https://fonts.google.com/

## Author

Custom Design Tool
GitHub: https://github.com/LordLuktor

---

Made with ❤️ for the WooCommerce community