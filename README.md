# This is a laravel package that provide all the functionality that you need to integrate your app with Qi card api (payments, notifications, authentications)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ht3aa/qi-card.svg?style=flat-square)](https://packagist.org/packages/ht3aa/qi-card)
[![Total Downloads](https://img.shields.io/packagist/dt/ht3aa/qi-card.svg?style=flat-square)](https://packagist.org/packages/ht3aa/qi-card)


## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/qi-card.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/qi-card)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require ht3aa/qi-card
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="qi-card-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="qi-card-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="qi-card-views"
```

## Usage
This package handle the authentcation part for your app. It will create for you a tables that hold all the users data of the qi card. In the `config/qi-card.php` config file you can specifiy which Qi card user data should be stored in your app. you should check your mini app scopes to know which information you are getting from the qi card. You can map the same scopes that is used in the mini app by enabling them in the `config/qi-card.php`.

```php

```


```php
$qiCard = new Ht3aa\QiCard();
echo $qiCard->echoPhrase('Hello, Ht3aa!');
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

- [Hasan Tahseen](https://github.com/ht3aa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
