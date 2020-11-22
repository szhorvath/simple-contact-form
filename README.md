# Simple Contact Form

Simple contact form. Build with PHP and javascript. Using [Web Components](https://developer.mozilla.org/en-US/docs/Web/Web_Components), Composer and PSR-4 autoloading, Assets bundled with [Webpack](https://webpack.js.org/)


## Docker Development Environment

Run `docker-compose up -d`


This project uses the following ports :

| Server     | Port |
|------------|------|
| MySQL      | 3306 |
| Nginx      | 80   |
| Mailhog    | 8025 |


### MySQL file

```sh
.
├── .docker
│   └── mysql
│       └── tables.sql
```


### MySQL credentials can be set



```sh
.
├── public
│   └── index.php
└── docker-compose.yml
```

Default credentials.

```php
$container['config'] = function () {
    return [
        'db_driver' => 'mysql',
        'db_host' => 'mysql',
        'db_name' => 'townsend',
        'db_user' => 'root',
        'db_pass' => 'secret',
        'admin_email' => 'admin@townsend.com',
    ];
};
```
