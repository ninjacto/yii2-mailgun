Yii2 Mailgun Mailer
===================

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Mailgun mailer for Yii 2 framework.

## Install

Via Composer

``` bash
$ composer require ninjacto/yii2-mailgun
```

## Config

```php
'components' => [
		...
		'mailer' => [
				'class' => 'ninjacto\yii2mailgun\Mailer',
				'domain' => 'example.com',
				'key' => 'key-somekey',
				'tags' => ['yii'],
				'enableTracking' => false,
		],
		...
],
?>
```


## Usage

Once the extension is installed, simply use it in your code by  :

```php
<?php
Yii::$app->mailer->compose('<view_name>', <option>)
->setFrom("<from email>")
->setTo("<to email>")
->setSubject("<subject>")
// ->setHtmlBody("<b> Hello User </b>")
// ->setTextBody("Hello User")
->send();
?>
```


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email ramin.farmani@gmail.com instead of using the issue tracker.

## Credits

- [Ramin Farmani][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ninjacto/yii2-mailgun.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ninjacto/yii2-mailgun/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/ninjacto/yii2-mailgun.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/ninjacto/yii2-mailgun.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ninjacto/yii2-mailgun.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/ninjacto/yii2-mailgun
[link-travis]: https://travis-ci.org/ninjacto/yii2-mailgun
[link-scrutinizer]: https://scrutinizer-ci.com/g/ninjacto/yii2-mailgun/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/ninjacto/yii2-mailgun
[link-downloads]: https://packagist.org/packages/ninjacto/yii2-mailgun
[link-author]: https://www.ninjacto.com
[link-contributors]: ../../contributors
