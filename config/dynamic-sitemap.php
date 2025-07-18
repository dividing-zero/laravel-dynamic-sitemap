<?php

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
