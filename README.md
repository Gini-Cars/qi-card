# Qi Card Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ht3aa/qi-card.svg?style=flat-square)](https://packagist.org/packages/ht3aa/qi-card)
[![Total Downloads](https://img.shields.io/packagist/dt/ht3aa/qi-card.svg?style=flat-square)](https://packagist.org/packages/ht3aa/qi-card)

A comprehensive Laravel package that provides all the functionality you need to integrate your application with the Qi Card API, including payments, notifications, and user authentication.

## Features

- 🔐 **User Authentication**: Seamlessly authenticate users via Qi Card authorization codes
- 📊 **User Information Management**: Store and manage user information from Qi Card with configurable scopes
- 💳 **Card List Management**: Optional storage and management of user card lists
- 📬 **Inbox Notifications**: Send notifications directly to users' Qi Card inbox
- 🖼️ **Avatar Management**: Optional S3 storage for user avatars
- 🔄 **Automatic Updates**: Configurable user data updates on each login
- 🏗️ **Laravel Integration**: Built with Laravel best practices and includes migrations, facades, and notifications

## Requirements

- PHP ^8.4
- Laravel ^11.0 || ^12.0

## Installation

You can install the package via Composer:

```bash
composer require ht3aa/qi-card
```

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="qi-card-config"
```

This will create a `config/qi-card.php` file in your config directory.

### Publish and Run Migrations

Publish and run the migrations to create the `qi_card_users` table:

```bash
php artisan vendor:publish --tag="qi-card-migrations"
php artisan migrate
```

The migration creates a table with the following structure:
- `id`: Primary key
- `qi_card_id`: Unique identifier for the Qi Card user
- `user_info`: JSON field storing user information
- `card_list`: JSON field storing user card list (optional)
- `qi_card_access_token`: Access token for API calls
- `timestamps`: Created and updated timestamps

### Publish Views (Optional)

If you need to customize views:

```bash
php artisan vendor:publish --tag="qi-card-views"
```

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file:

```env
QI_CARD_API_BASE_URL=https://api.qi-card.com
QI_CARD_API_CLIENT_ID=your_client_id_here
QI_CARD_API_PRIVATE_KEY=your_private_key_here
QI_CARD_API_PUBLIC_KEY=your_public_key_here
```

> **Note**: Contact the Qi Card team to obtain your `CLIENT_ID` and `PRIVATE_KEY`. These are required for the package to function properly.

### Configuration File

The published configuration file (`config/qi-card.php`) contains the following options:

```php
return [
    // Enable fetching and storing user information
    'user_info_scopes_enabled' => true,

    // Enable fetching and storing card list
    // Requires CARD_LIST scope in your mini app
    'card_list_scope_enabled' => false,

    // Store avatar URLs in S3 storage instead of original URLs
    // Requires USER_AVATAR scope in your mini app and S3 configuration
    'store_avatar_url_in_s3_storage' => false,

    // Update user data (user_info, card_list, access_token) on every login
    'update_user_data_every_login' => false,

    // API configuration (loaded from environment variables)
    'api' => [
        'base_url' => env('QI_CARD_API_BASE_URL'),
        'private_key' => env('QI_CARD_API_PRIVATE_KEY'),
        'public_key' => env('QI_CARD_API_PUBLIC_KEY'),
        'client_id' => env('QI_CARD_API_CLIENT_ID'),
    ],
];
```

### Configuration Options Explained

#### `user_info_scopes_enabled`
When enabled (default: `true`), the package will fetch and store user information from Qi Card. Make sure your mini app has the appropriate scopes configured to receive user data.

#### `card_list_scope_enabled`
When enabled (default: `false`), the package will fetch and store the user's card list. You must use the `CARD_LIST` scope in your mini app to receive this data.

#### `store_avatar_url_in_s3_storage`
When enabled (default: `false`), user avatars will be downloaded and stored in your S3 storage. The original URL will be replaced with the S3 path. Requires:
- `USER_AVATAR` scope in your mini app
- Proper S3 configuration in your Laravel application
- S3 disk configured in `config/filesystems.php`

#### `update_user_data_every_login`
When enabled (default: `false`), user data (user_info, card_list, and access_token) will be updated every time a user logs in. When disabled, data is only stored on first registration.

## Usage

### User Authentication

To authenticate a user with Qi Card, you need to obtain an authorization code from your mini app and use it to create or retrieve a user:

```php
use Ht3aa\QiCard\Facades\QiCard;

// In your authentication controller
public function authenticate(Request $request)
{
    $authCode = $request->input('auth_code');
    
    try {
        $user = QiCard::createUser($authCode);
        
        // The user is now authenticated
        // $user is an instance of Ht3aa\QiCard\QiCardUser
        
        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth')->plainTextToken,
        ]);
    } catch (\Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException $e) {
        return response()->json([
            'message' => 'Authentication failed',
        ], 422);
    }
}
```

The `createUser` method will:
1. Exchange the authorization code for an access token
2. Fetch user information (if `user_info_scopes_enabled` is true)
3. Fetch card list (if `card_list_scope_enabled` is true)
4. Store avatar in S3 (if `store_avatar_url_in_s3_storage` is true)
5. Create a new user or update existing user based on configuration
6. Return a `QiCardUser` model instance

### Working with QiCardUser Model

The `QiCardUser` model provides access to user data:

```php
use Ht3aa\QiCard\QiCardUser;

// Find a user
$user = QiCardUser::where('qi_card_id', $qiCardId)->first();

// Access user information
$userInfo = $user->user_info; // Array of user information
$cardList = $user->card_list; // Array of cards (if enabled)
$accessToken = $user->qi_card_access_token; // Access token

// Access specific user info fields
$userId = $user->user_info['userId'] ?? null;
$nickname = $user->user_info['nickname'] ?? null;
$avatar = $user->user_info['avatar'] ?? null;
```

### Sending Notifications

The package provides a convenient way to send notifications to users' Qi Card inbox.

#### Using the Facade

```php
use Ht3aa\QiCard\Facades\QiCard;
use Ht3aa\QiCard\QiCardUser;

$user = QiCardUser::find($id);

try {
    $response = QiCard::sendSuperQiInboxNotification(
        accessToken: $user->qi_card_access_token,
        title: 'Notification Title',
        content: 'Your notification message here',
        url: 'https://your-app.com/some-page' // Optional
    );
    
    // Notification sent successfully
} catch (\Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException $e) {
    // Handle error
}
```

#### Using Laravel Notifications

The package includes a notification class that integrates with Laravel's notification system:

```php
use Ht3aa\QiCard\Notifications\SuperQiInboxNotification;
use Ht3aa\QiCard\QiCardUser;

$user = QiCardUser::find($id);

$user->notify(new SuperQiInboxNotification(
    title: 'Welcome!',
    message: 'Thank you for using our service.',
    url: 'https://your-app.com/dashboard'
));
```

#### Queued Notifications

You can queue notifications for better performance:

```php
use Illuminate\Bus\Queueable;
use Ht3aa\QiCard\Notifications\SuperQiInboxNotification;

// In your notification class or controller
$user->notify((new SuperQiInboxNotification(
    title: 'Order Update',
    message: 'Your order has been processed.',
    url: 'https://your-app.com/orders/123'
))->delay(now()->addMinutes(5)));
```

### Available Methods

#### `QiCard::createUser(string $authCode): QiCardUser`
Creates or retrieves a Qi Card user using an authorization code.

**Parameters:**
- `$authCode` (string): Authorization code from Qi Card mini app

**Returns:** `QiCardUser` model instance

**Throws:** `UnprocessableEntityHttpException` if the request fails

#### `QiCard::sendSuperQiInboxNotification(string $accessToken, string $title, string $content, string $url = ''): array`
Sends a notification to a user's Qi Card inbox.

**Parameters:**
- `$accessToken` (string): User's Qi Card access token
- `$title` (string): Notification title
- `$content` (string): Notification content/message
- `$url` (string, optional): URL to open when notification is clicked

**Returns:** API response array

**Throws:** `UnprocessableEntityHttpException` if the request fails

## Mini App Scopes

To use this package effectively, you need to configure the appropriate scopes in your Qi Card mini app. The scopes you need depend on which features you want to use:

- **User Information**: Enable `USER_INFO` related scopes to fetch user information
- **Card List**: Enable `CARD_LIST` scope to fetch and store card lists
- **Avatar**: Enable `USER_AVATAR` scope to fetch and store avatars

Consult the Qi Card documentation to understand which scopes are available and how to configure them in your mini app.

## Database Schema

The `qi_card_users` table stores the following information:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `qi_card_id` | string | Unique Qi Card user identifier |
| `user_info` | json | User information from Qi Card API |
| `card_list` | json | List of user's cards (optional) |
| `qi_card_access_token` | string | Access token for API calls |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Record last update time |

## Error Handling

The package throws `UnprocessableEntityHttpException` when API requests fail. Always wrap API calls in try-catch blocks:

```php
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

try {
    $user = QiCard::createUser($authCode);
} catch (UnprocessableEntityHttpException $e) {
    // Handle API errors
    Log::error('Qi Card API error: ' . $e->getMessage());
    return response()->json(['error' => 'Authentication failed'], 422);
} catch (\Exception $e) {
    // Handle other errors (e.g., missing configuration)
    Log::error('Qi Card error: ' . $e->getMessage());
    return response()->json(['error' => 'Configuration error'], 500);
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/qi-card.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/qi-card)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

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
