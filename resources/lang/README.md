# Translation Maintenance

This project uses English as the source language and Khmer as the translated language.

## Structure

- `en/admin.php` and `km/admin.php`: shared layout, navigation, and main dashboard text.
- `en/admin_<page>.php` and `km/admin_<page>.php`: text for one admin page.
- `km/admin_legacy.php`: temporary fallback dictionary for old hardcoded text translated by `resources/views/partials/legacy-translator.blade.php`.
- `en/admin_legacy.php`: intentionally empty because English is the source UI language.

## Rules

1. Keep English and Khmer files for the same page with the same keys.
2. Add new page text to the page-specific file first. Avoid growing `admin_legacy.php` unless the text is hardcoded in old JavaScript or difficult to convert safely.
3. Use short, stable key names such as `save_changes`, `empty_desc`, or `delete_confirm`.
4. Preserve placeholders exactly in both languages, for example `:count`, `:name`, `:value`.
5. Prefer clear Khmer UI wording over word-for-word translation.

## Check Before Finishing

Run these from the project root:

```bash
docker compose exec app php -l resources/lang/en/admin.php
docker compose exec app php -l resources/lang/km/admin.php
docker compose exec app php artisan view:cache
docker compose exec app php artisan view:clear
```

For a page-specific file, lint both language files after editing them.
