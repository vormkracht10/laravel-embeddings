# Create embeddings for your Eloquent models to use with OpenAI

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/laravel-embeddings.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/laravel-embeddings)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/laravel-embeddings/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vormkracht10/laravel-embeddings/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/laravel-embeddings/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/vormkracht10/laravel-embeddings/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/laravel-embeddings.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/laravel-embeddings)

OpenAI's text embeddings measure the relatedness of text strings. Using this package you can save embeddings automatically for your Eloquent model in a PostgreSQL vector database. To use the embeddings in your AI requests to the OpenAI API endpoints.

## Installation

You can install the package via composer:

```bash
composer require vormkracht10/laravel-embeddings
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-embeddings-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-embeddings-config"
```

This is the contents of the published config file:

```php
return [
    'enabled' => env('EMBEDDINGS_ENABLED', true),
    'driver' => env('EMBEDDINGS_DRIVER', 'null'), // 'null' / 'openai'
    'queue' => true,
    'database' => [
        'connection' => env('EMBEDDINGS_DATABASE_CONNECTION', 'pgsql'),
        'table' => env('EMBEDDINGS_DB_TABLE', 'embeddings'),
    ],
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-ada-002')
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the embed engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */
    'chunk' => [
        'embeddable' => 500,
        'unembeddable' => 500,
    ],
];
```

Fill in the following env variables

```.env
EMBEDDINGS_DRIVER=openai
OPENAI_API_KEY=
```

## Usage

```php
// Add the Embeddable trait to your Model(s).
class MyModel {
    use Embeddable {
        \Laravel\Scout\Searchable::usesSoftDelete insteadof \Vormkracht10\Embedding\Embeddable;
    }
}

// You can override the embeddable content
class MyModel {
    // ...
    public function toEmbeddableString()
    {
        return strip_tags(implode(', ', $this->toArray()));
    }
}

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Vormkracht10](https://github.com/vormkracht10)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
