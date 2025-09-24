# Laravel Dynamic Sitemap

Automatically generate dynamic sitemaps at runtime using Eloquent Models in Laravel.

Easily include records for specific Eloquent models by using the `Sitemappable` trait, and specific routes using the `sitemappable` middleware.

## Installation

```bash
composer require dividing-zero/laravel-dynamic-sitemap
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="DividingZero\LaravelDynamicSitemap\SitemapServiceProvider" --tag="config"
```

Edit `config/sitemap.php` to specify models and settings:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | List fully qualified model class names to include in the sitemap.
    | These models should implement the `Sitemappable` trait.
    |
    | E.g. App\Models\Blog::class
    |
    */
    'models' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Modified Date
    |--------------------------------------------------------------------------
    |
    | The default modified date to use when a model does not define a `getModifiedDate` method.
    | This will also be used for any routes which are manually included via middleware, so it is recommended to set it to the last time the site was updated.
    |
    | E.g. 2025-01-01
    |
    */
    'default_modified_date' => '2025-01-01', // Use null to default to now()

    /*
    |--------------------------------------------------------------------------
    | Default Change Frequency
    |--------------------------------------------------------------------------
    |
    | The default change frequency to use when a model does not define a `getChangeFrequency` method.
    | This will also be used for any routes which are manually included via middleware, so a reasonable default such as 'monthly' is recommended.

    | Options: always, hourly, daily, weekly, monthly, yearly, never
    |
    */
    'default_change_frequency' => 'monthly',

    /*
    |--------------------------------------------------------------------------
    | Default Priority
    |--------------------------------------------------------------------------
    |
    | The default priority to use when a model does not define a `getPriority` method.
    | This will also be used for any routes which are manually included via middleware.
    |
    | Priority values range from 0.0 to 1.0, where 1.0 is the highest priority.
    | A value of 0.5 is a common default.
    |
    |*/
    'default_priority' => 0.5,

    /*
    |--------------------------------------------------------------------------
    | Cache Lifetime
    |--------------------------------------------------------------------------
    |
    | The number of seconds to cache the generated sitemap.
    | This helps improve performance by avoiding frequent regeneration.
    | Set to 0 to disable caching.
    |
    |*/
    'cache_lifetime' => 60
    
];
```

## Sitemappable Trait

To include records for specific models in the sitemap, add the fully qualified class name to the `dynamic-sitemap.models` configuration, and inherit the `sitemappable` trait on the corresponding model.

```php
use DividingZero\LaravelDynamicSitemap\Traits\Sitemappable;

class Post extends Model
{
    use Sitemappable;

    // Required overrides

    /**
     * Define the sitemap URL for this model instance.
     * 
     * @return string
     */
    public function getSitemapUrl(): string;

    // Optional overrides

    /**
     * Get the last modification date for the sitemap entry.
     * 
     * @return \Carbon\Carbon
     */
    public function getSitemapModifiedDate(): Carbon
    {
        return $this->updated_at ?? Carbon::parse(config('sitemap.default_modified_date'));
    }

    /**
     * Get the change frequency for the sitemap entry.
     * 
     * @return string (e.g., 'daily', 'weekly')
     */
    public function getSitemapChangeFreqency(): string
    {
        // Use config default if not overridden
        return config('sitemap.default_change_frequency');
    }

    /**
     * Get the priority for the sitemap entry.
     * 
     * @return float
     */
    public function getSitemapPriority(): float
    {
        // Use config default if not overridden
        return config('sitemap.default_priority');
    }

    /**
     * Scope a query to only include records that should be in the sitemap.
     * Returns unscoped query with all records by default.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSitemapQuery($query)
    {
        return $query;
    }
}
```

## Sitemappable Middleware

To include specific routes in the sitemap, add the `sitemappable` middleware to those routes:

```php
Route::get('/about', 'PageController@about')->middleware('sitemappable');
```

*Note: If any route parameters exist they will be removed, since route parameters cannot be resolved automatically.*

## Usage

Visit `/sitemap.xml` in your browser to view the generated sitemap.

## Caching

The sitemap is cached for the duration specified in `cache_lifetime`. Set to `0` to disable caching (the sitemap will be regenerated on every request).

## License

MIT