# GoBiz Translation Manager

A file-based localization module designed for the GoBiz SaaS platform. It manages language values directly within standard Laravel `resources/lang` directory files without requiring database connections.

## Features

- **No Database Footprint**: Uses PHP file arrays and JSON maps directly.
- **Support for Nested Arrays**: Dot-notation flattening for administrative editing and automatic nested reconstruction upon save operations.
- **Key Sync & Autocomplete**: Performs difference matching against source baseline templates to identify and add missing entries.
- **Portability**: Exports directory trees into standard ZIP packages and extracts imported ZIP packages safely.

## Configuration & Setup

Publish configuration parameters using standard commands:

```bash
php artisan vendor:publish --tag=translation-manager-config
```
