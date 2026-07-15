# AAI

## Local Setup (DDEV)

Run these commands from the project root:

```bash
ddev start
ddev composer install
ddev import-db --file=database/wordpress-db.sql.gz
ddev launch
```

## Front-End CSS Build

Compile theme CSS with the custom DDEV command:

```bash
ddev fe_compile
```

### Optional: Direct Theme Commands

If you want to run the build scripts directly:

```bash
cd web/wp-content/themes/sd_base
npm install
npm run build:css
```

To watch SCSS and rebuild automatically:

```bash
cd web/wp-content/themes/sd_base
npm run watch:css
```
