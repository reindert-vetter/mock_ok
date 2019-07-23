# Twins
Twins in a mock server for Laravel. Every external request first passes through Twins. Twins will execute the request once and save the response. The stored response is used as a mock for the next request. Although Twins runs in a different docker container, you take the mocks with you in your commits. Complete control over your situation!
> We believe that you don't have to change production code to mock external connections.

## Contents

- [Usage](#usage)
    - [How Twins mocks work](#how-twins-mocks-work)
    - [Match a mock file](#match-a-mock-file)
    - [Response](#response)
    - [Transport](#transport)
- [Tips](#tips)
    - [Debug](#debug)
    - [Format](#format)
- [Installation](#installation)
    - [Requirements](#requirements)
    - [Connect the docker volume](#connect-the-docker-volume)
    - [Composer](#composer)
    - [Activate Twins](#activate-twins)
    - [Calling Twins](#calling-twins)
- [Contribute](#contribute)

## Usage

### How Twins mocks work
Twins mocks are divided into 2 parts. The `when` part and the `response` part. If the `when` callback returns true, the `response` callback is returned.
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

return [

    'when' => function (Request $request): bool {
        return
            $request->isMethod('GET') &&
            preg_match('#^https?\://api.webshop.com/products/1#', $request->fullUrl());
    },

    'response' => function (Collection $transport): array {
        return [
            'status'  => 200,
            'headers' => [
                'Content-Type'   => 'application/json;charset=utf-8',
                'content-length' => '275',
            ],
            'body'    => '
                {
                    "id": 1,
                    "name": "Product name",
                    "price": "2.30",
                    "status": "publish"
                }
            ',
        ];
    },
];
```
> Remember: Twin makes this automatically for you. You only have to adjust it to your needs.

### Match a mock file
Because you want complete control over your mock files, you can use regex to determine when your mock file should be used.
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

return [

    'when' => function (Request $request): bool {
        return
            $request->isMethod('GET') &&
            preg_match('#^https?\://api.webshop.com/products/1#', $request->fullUrl());
    },
```

### Response
You can determine yourself what your response should be. You can define what the status, headers and body should be.
```php
    'response' => function (Collection $transport): array {
        return [
            'status'  => 200,
            'headers' => [
                'Content-Type'   => 'application/json;charset=utf-8',
                'content-length' => '275',
            ],
            'body'    => '
                {
                    "id": 1,
                    "name": "Product name",
                    "price": "2.30",
                    "status": "publish"
                }
            ',
        ];
    },
];
```
> Both json and xml (SOAP) responses are supported.

### Transport
Use _transport_ to transport data to your mock file. In your test, you can send a string or array:
`\External\Providers\TwinsClient::transport('product_name', $productName);`.
And you can get the variable in your response with `$transport->get('product_name')`:
```php

'response' => function (Collection $transport): array {
        return [
            'status'  => 200,
            'headers' => [...],
            'body'    => '
                {
                    "id": 1,
                    "name": "' . $transport->get('product_name') . '",
                    "price": "' . $transport->get('product_price') . '",
                    "status": "publish",
                    "sku": "01120000",
                    "meta_data": []
                }
            ',
        ];
    },
];
```
> TwinsClient push the variable to `twins_transform.json`. The variable can be read from the Twins container. Realise that `\External\Providers\TwinsClient::activate()` empties `twins_transform.php` and `twins_transform.php` file is ignored by git.

## Tips
### Debug:
You can debug the Twins requests in the `twins_debug.log` file (in your mock directory).
> You can use Laravel `logger()` helper to log your data to `twins_debug.log`.

### Format
Use your idea to reformat the mocks correctly.

### Mocks from scratch
Twins automatically creates mocks for you. But of course you can also design your own mocks from scratch.

## Installation

### Requirements
You need to run Docker and you need to have knowledge about connecting a volume with your project.

### Connect the docker volume
The mocks must be shared between the mock directory in your project and the mock directory in the Twins container.

Connect Twins container to a volume.
```yaml
  twins:
    image: reindertvetter/twins:latest
    volumes:
      - "twins_mocks:/var/www/html/src/storage/app/mocks/response"
    environment:
      ENVIRONMENT: "test"
```
Depend your project on the Twins container (take the following as an example):
```yaml
  your_project:
    image: your_php_project_image
    (...)
    depends_on:
      - twins
```
 To connect your volume with your local environment, you probably also need to add twins_mocks to your list of available volumes.
```yaml
volumes:
  storage:
  (...)
  twins_mocks:
    driver: local
    driver_opts:
      type: none
      device: /path_to_your_local_project/test/mocks
      o: bind
```
1. Run `docker-compose up`.
1. You can check whether Twins volumes are set correctly. Run `curl "twins/google.nl"`. You should now see your first mock file in `/path_to_your_local_project/test/mocks`.

### Composer
To install the Twins SDK into your project, simply use `$ composer require reindert-vetter/twins-sdk`

### Config
Be sure the twins.php config file is present and configure `mocks_path` in this file.

### Activate Twins
You have to place `\External\Providers\TwinsClient::activate()` in your setUp() or bootstrap method so that Twins knows that a new test is starting.

### Calling Twins
Instead of making a call to an external server, you have to make a call to the Twins container. So you don't have to make a call to `api.facebook.com` but you have to make a call to `twins/api.facebook.com`. If you have configured Twins correctly, this will happen automatically. See `config/twins.php` to configure Twins correctly. To get an idea of ​​how simple Twins works, it's best to first try out a request with a Guzzle Client.
> To get an idea how simple Twins works, I advise you to first mock a request that uses Guzzle Client.

## Contribute

1. Check for open issues or open a new issue to start a discussion around a bug or feature. Also send the contents of debug.log.
1. Fork the repository on GitHub to start making your changes.
1. Write one or more tests for the new feature or that expose the bug.
1. Make code changes to implement the feature or fix the bug.
1. Send a pull request to get your changes merged and published.
