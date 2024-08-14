## Outline
Create a small Laravel app to display GeoJSON data (areas) on a map.

## Requirements
-  You must use the latest versions of Laravel and Livewire, but you can use any interactive map library of your choice.
-  A page that lists the areas in a paginated data table and is real-time searchable via name and description.
-  A page to create areas
-  You should be able to draw and save an area with additional information (see below) to a database.
-  You should be able to upload a GeoJSON file via a drag-and-drop uploader and store its contents in the database.
-  We would also like you to display the polygon data of the uploaded GeoJSON file as a preview before creating a new area record.

## Area Specification
-  Name (Required)
-  Description (Optional)
-  List of categories (Required)
-  Valid from (Required)
-  Valid To (Optional)
-  Display in breaches list (Required)
-  GeoJSON data

### Bonus points will be awarded for the following
-  Use of Tailwind
-  UI Considerations
-  The ability to edit areas

## Further information
We can supply you with an example GeoJSON file.

---

## Set up instructions
### Run:
- `cp .env.example .env`
- `composer install`
- `php artisan key:generate`
- `sail npm install`
- `sail up -d && sail artisan migrate:fresh --seed && sail npm run dev`
- Navigate to `http://localhost` (may differ from url provided from CLI) to see the application


### Test: 
- `sail artisan test`
