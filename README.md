# TeleWoo WordPress Plugin

TeleWoo is a WordPress plugin that sends published products to a Telegram channel with images and information.

## Features

- Automatically sends product information to a Telegram channel when published.
- Customizable message composition using shortcodes.
- Option to show product images and include inline buttons.
- Supports custom fields for dynamic content in messages.

## Installation

1. Download the plugin ZIP file from the [Releases](https://github.com/alienflick/TeleWoo/releases) page.
2. In your WordPress admin panel, go to "Plugins" > "Add New" and click "Upload Plugin".
3. Upload the downloaded ZIP file and activate the plugin.

## Usage

1. After activation, go to "TeleWoo" in your WordPress admin menu.
2. Configure the plugin settings, including the Telegram Bot API and Channel Username.
3. Compose a custom message using shortcodes like `{post_title}`, `{terms:product_cat}`, etc.
4. Optionally enable the display of product images and customize the inline button text.
5. Save your settings.

## Shortcode Placeholders

Use these placeholders in your custom message to insert dynamic content:

- `{post_title}`: Product title.
- `{ID}`: Product ID.
- `{terms:product_cat}`: Product categories.
- `{cf:_regular_price}`: Custom field `_regular_price`.
- `{cf:description}`: Custom field `description`.

## Troubleshooting

If you encounter any issues or need assistance, please open an [issue](https://github.com/your-username/TeleWoo/issues) on GitHub.

## Contributing

Contributions are welcome! If you have improvements or bug fixes, feel free to submit a pull request.

## License

This project is licensed under the [MIT License](LICENSE).

---

Feel free to modify and expand on this `README.md` template to provide more detailed information about your TeleWoo WordPress plugin. Make sure to replace placeholders like `[Releases]`, `[YourUsername]`, and `[License]` with the actual links and information for your plugin. Including clear instructions, examples, and troubleshooting steps will help users understand and use your plugin effectively.
