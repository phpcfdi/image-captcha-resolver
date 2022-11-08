# phpcfdi/image-captcha-resolver

[![Source Code][badge-source]][source]
[![Packagist PHP Version Support][badge-php-version]][php-version]
[![Discord][badge-discord]][discord]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![Reliability][badge-reliability]][reliability]
[![Maintainability][badge-maintainability]][maintainability]
[![Code Coverage][badge-coverage]][coverage]
[![Violations][badge-violations]][violations]
[![Total Downloads][badge-downloads]][downloads]

> Conectores para resolver captchas de imágenes

:us: The documentation of this project is in spanish as this is the natural language for the intended audience.

## Acerca de phpcfdi/image-captcha-resolver

Esta librería contiene conectores con algunos servicios populares o de prueba para resolver captchas.

Es utilizado en algunos proyectos de [PhpCfdi](https://github.com/phpcfdi).

## Instalación

Usa [composer](https://getcomposer.org/)

```shell
composer require phpcfdi/image-captcha-resolver
```

Es posible que para su correcta implementación requiera también instalar algunos paquetes adicionales.
Vea <https://docs.php-http.org/en/latest/clients.html>

```shell
# uso de guzzle, con el adaptador del cliente y su contructor de request y response
composer require guzzlehttp/guzzle php-http/guzzle7-adapter guzzlehttp/psr7

# uso de symfony http client con nyholm/psr7 como constructor de request y response
composer require symfony/http-client nyholm/psr7

# uso de cliente basado en curl con laminas/laminas-diactoros como constructor de request y response
composer require php-http/curl-client laminas/laminas-diactoros
```

Sin embargo, si lo que está desarrollando es una librería debería usar como dependencia de cliente
el paquete `php-http/mock-client` y cualquier fábrica de mensajes (en `composer.json:require-dev`).

## Uso básico

### Llamar a resolver un captcha

Para este ejemplo se asume que ya existe un resolvedor de captchas en `$resolver`
y que la imagen del captcha se encuentra como imagen embedida y su contenido en `$theImgElementSrcAtributte`.

```php
<?php declare(strict_types=1);

use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;

/**
 * @var string $theImgElementSrcAtributte
 * @var CaptchaResolverInterface $resolver
 */

$image = CaptchaImage::newFromInlineHtml($theImgElementSrcAtributte);

try {
    $answer = $resolver->resolve($image);
} catch (UnableToResolveCaptchaException $exception) {
    echo 'No se pudo resolver el captcha', PHP_EOL;
    return;
}

echo "Respuesta del captcha: {$answer->getValue()}", PHP_EOL;
```

### Creación de un resolvedor de captchas basado en Anti-Captcha

Para crear el resolvedor se puede hacer de dos formas: de manera específica y por descubrimiento.

Servicio: <https://anti-captcha.com>

```php
<?php declare(strict_types=1);

use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver;
use PhpCfdi\ImageCaptchaResolver\Timer\Timer;

/**
 * @var string $clientKey La clave de cliente para conectarse a anti-captcha 
 */

// Se puede crear el resolvedor usando los parámetros predefinidos
$resolverDefault = AntiCaptchaResolver::create($clientKey);

// O especificando parámetros funcionales
$resolverWithSettings = AntiCaptchaResolver::create(
    $clientKey,
    5, // segundos antes de intentar leer la respuesta
    60, // segundos antes de considerar que el captcha no tiene solución
    500 // milisegundos antes de reintentar obtener la respuesta
);

// O directamente creando los objetos
$resolverConstructed = new AntiCaptchaResolver(
    new AntiCaptchaResolver\AntiCaptchaConnector($clientKey),
    new Timer(4, 60, 500)
);
```

### Creación de un resolvedor de captchas basado en Local Captcha Resolver

Para crear el resolvedor se puede hacer de dos formas: de manera específica y por descubrimiento.

Servicio: <https://github.com/eclipxe13/captcha-local-resolver>

```php
<?php declare(strict_types=1);

use PhpCfdi\ImageCaptchaResolver\Resolvers\CaptchaLocalResolver;
use PhpCfdi\ImageCaptchaResolver\Timer\Timer;

/**
 * @var string $baseUrl La dirección donde está corriendo el servicio, como http://localhost:9095 
 */

// Se puede crear el resolvedor usando los parámetros predefinidos
$resolverDefault = CaptchaLocalResolver::create($baseUrl);

// O especificando parámetros funcionales
$resolverWithSettings = CaptchaLocalResolver::create(
    $baseUrl,
    5, // segundos antes de intentar leer la respuesta
    60, // segundos antes de considerar que el captcha no tiene solución
    500 // milisegundos antes de reintentar obtener la respuesta
);

// O directamente creando los objetos
$resolverConstructed = new CaptchaLocalResolver(
    new CaptchaLocalResolver\CaptchaLocalResolverConnector($baseUrl),
    new Timer(4, 60, 500)
);
```

### Creación de un resolvedor de captchas basado en línea de comandos

La implementación dependerá siempre de la herramienta que se esté utilizando, es probable que fabrique
su propio punto de entrada a la herramienta para que devuelva el *exit code* correcto y la respuesta.

Esta herramienta podría ser útil en caso de que el captcha se pueda resolver utilizando alguna herramienta
como [`tesseract`](https://github.com/tesseract-ocr/tesseract).

El siguiente ejemplo supone que tiene la imagen del captcha a resolver en `$image` y que existe un commando
llamado `my-captcha-breaker` que se le entrega una imagen y devuelve en el último renglón de la salida
la respuesta del captcha.

```php
<?php declare(strict_types=1);

use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\CommandLineResolver;
use PhpCfdi\ImageCaptchaResolver\UnableToResolveCaptchaException;

/**
 * @var CaptchaImageInterface $image 
 */

$resolver = CommandLineResolver::create(explode(' ', 'my-captcha-breaker --in {file} --stdout'));

try {
    $answer = $resolver->resolve($image);
} catch (UnableToResolveCaptchaException $exception) {
    echo 'No se pudo resolver el captcha: ', $exception->getMessage(), PHP_EOL;
    return;
}

echo $answer, PHP_EOL;
```

## Resolvedores

### Multiresolvedor

El resolvedor `MultiResolver` es en sí mismo un resolvedor que intenta resolver el captcha usando un conjunto
predefinido de resolvedores. Podría ser útil para intentar resolver utilizando diferentes estrategias
o reintentando con un mismo resolvedor el número de veces en las que esté incluído.

### Resolvedores para pruebas

El resolvedor `CaptchaLocalResolver` usa servicio de resolución de captchas local y es comúnmente utilizado para pruebas.

También podría usar el resolvedor `ConsoleResolver` en donde se almacena en un archivo predefinido
la imagen del captcha a resolver y se espera que escriba la ventana en la misma terminal donde está
ejecutando el proceso. Solo es útil si puede escribir la respuesta. Si no se recibe la respuesta
en un tiempo predeterminado el resolvedor fallará lo tomará como una respuesta vacía.

Si está haciendo pruebas unitarias, la mejor alternativa es usar el resolvedor `MockResolver`, que se construye
con respuestas prestablecidas `CaptchaAnswerInterface` o excepciones `UnableToResolveCaptchaException` y falla con una
excepción `OutOfRangeException` si se le pide una respuesta y ya no tiene más.

### Nuevos resolvedores

Si ya tienes contratado un servicio de solución de captchas o deseas implementar uno, por ejemplo,
basado en `tesseract`, debes implementar la interfaz `ResolverInterface` en tu proyecto.

Si el resolvedor puede beneficiar a toda la comunidad entonces haz tu solicitud de que se incluya
en esta librería y con gusto lo evaluaremos, tomando en cuenta las dependencias y las pruebas.

Si el servicio requiere de registrar una nueva cuenta, por favor ponte en contacto con nosotros
y podemos crear el resolvedor. Considera que será necesario que patrocines la suscripción
para poder hacer pruebas de funcionamiento punto a punto.

## Especificación de clases e interfaces

### `CaptchaImage`

Contiene la imagen en `base64` de la imagen, se puede construir desde un archivo, desde datos binarios,
desde datos binarios codificados como `base64` o desde un texto de imagen html embedido.

- `static CaptchaImage::newFromFile(string $filename): self`
- `static CaptchaImage::newFromBinary(string $contents): self`
- `static CaptchaImage::newFromBase64(string $contents): self`
- `static CaptchaImage::newFromInlineHtml(string $contents): self`

También contiene métodos para expresar la imagen u obtener el tipo MIME:

- `CaptchaImage::asBinary(): string`
- `CaptchaImage::asBase64(): string`
- `CaptchaImage::asBinary(): string`
- `CaptchaImage::asInlineHtml(): string`
- `CaptchaImage::getMimeType(): string`

También se puede expresar como JSON o como string pues implementa `Stringable` y `JsonSerializable`
y si lo desea, puede establecer su propia implementación usando `CaptchaImageInterface`.

### `CaptchaResolverInterface`

Contiene un único método de resolución en donde toma un `CaptchaImageInterface` y entrega un `CaptchaAnswer`:
`CaptchaResolverInterface::resolve(CaptchaImageInterface $image): CaptchaAnswerInterface`.

Hay diferentes implementaciones y se pueden agregar más a esta librería o en una librería independiente.

### `CaptchaAnswer`

Contiene la respuesta del captcha como un valor de texto, no puede ser una cadena vacía.

La respuesta se obtiene con el método `CaptchaAnswer::getValue(): string`.

Y se puede comparar contra cualquier otro valor usando `CaptchaAnswer::equalsTo($value): bool`.

También se puede expresar como JSON o como string pues implementa `Stringable` y `JsonSerializable`
y si lo desea, puede establecer su propia implementación usando `CaptchaAnswerInterface`.

### `HttpClientInterface` y `HttpClient`

Este método es un adaptador para facilitar las comunicaciones de HTTP con el exterior.

La implementación actual contiene muy pocos métodos y serán agregados nuevos conforme se necesite.

Utiliza los estándares PSR-18 *HTTP Client*, y PSR-17 *HTTP Factories* que usan el PSR-7 *HTTP message interfaces*.
Requieren de un cliente http que implemente el PSR-18 y de una librería que implemente PSR-17 para construir los
mensajes de tipo `Request` o `Response`.

Para que sea fácil poder crear el objeto, se usa el paquete de HTTPlug Discovery
[`php-http/discovery`](https://docs.php-http.org/en/latest/discovery.html) que
permite encontrar implementaciones instaladas y utilizarlas.

El método `HttpClient::discovery()` es el que se utiliza de forma predeterminada para construir el objeto,
aunque también se puede crear utilizando el constructor y entregando los objetos necesarios.

## Soporte

Puedes obtener soporte abriendo un ticket en Github.

Adicionalmente, esta librería pertenece a la comunidad [PhpCfdi](https://www.phpcfdi.com), así que puedes usar los
mismos canales de comunicación para obtener ayuda de algún miembro de la comunidad.

## Compatibilidad

Esta librería se mantendrá compatible con al menos la versión con
[soporte activo de PHP](https://www.php.net/supported-versions.php) más reciente.

También utilizamos [Versionado Semántico 2.0.0](docs/SEMVER.md) por lo que puedes usar esta librería
sin temor a romper tu aplicación.

## Contribuciones

Las contribuciones con bienvenidas. Por favor lee [CONTRIBUTING][] para más detalles
y recuerda revisar el archivo de tareas pendientes [TODO][] y el archivo [CHANGELOG][].

## Copyright and License

The `phpcfdi/image-captcha-resolver` library is copyright © [PhpCfdi](https://www.phpcfdi.com/)
and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[contributing]: https://github.com/phpcfdi/image-captcha-resolver/blob/main/CONTRIBUTING.md
[changelog]: https://github.com/phpcfdi/image-captcha-resolver/blob/main/docs/CHANGELOG.md
[todo]: https://github.com/phpcfdi/image-captcha-resolver/blob/main/docs/TODO.md

[source]: https://github.com/phpcfdi/image-captcha-resolver
[php-version]: https://packagist.org/packages/phpcfdi/image-captcha-resolver
[discord]: https://discord.gg/aFGYXvX
[release]: https://github.com/phpcfdi/image-captcha-resolver/releases
[license]: https://github.com/phpcfdi/image-captcha-resolver/blob/main/LICENSE
[build]: https://github.com/phpcfdi/image-captcha-resolver/actions/workflows/build.yml?query=branch:main
[reliability]:https://sonarcloud.io/component_measures?id=phpcfdi_image-captcha-resolver&metric=Reliability
[maintainability]: https://sonarcloud.io/component_measures?id=phpcfdi_image-captcha-resolver&metric=Maintainability
[coverage]: https://sonarcloud.io/component_measures?id=phpcfdi_image-captcha-resolver&metric=Coverage
[violations]: https://sonarcloud.io/project/issues?id=phpcfdi_image-captcha-resolver&resolved=false
[downloads]: https://packagist.org/packages/phpcfdi/image-captcha-resolver

[badge-source]: https://img.shields.io/badge/source-phpcfdi/image--captcha--resolver-blue?logo=github
[badge-discord]: https://img.shields.io/discord/459860554090283019?logo=discord
[badge-php-version]: https://img.shields.io/packagist/php-v/phpcfdi/image-captcha-resolver?logo=php
[badge-release]: https://img.shields.io/github/release/phpcfdi/image-captcha-resolver?logo=git
[badge-license]: https://img.shields.io/github/license/phpcfdi/image-captcha-resolver?logo=open-source-initiative
[badge-build]: https://img.shields.io/github/workflow/status/phpcfdi/image-captcha-resolver/build/main?logo=github-actions
[badge-reliability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_image-captcha-resolver&metric=reliability_rating
[badge-maintainability]: https://sonarcloud.io/api/project_badges/measure?project=phpcfdi_image-captcha-resolver&metric=sqale_rating
[badge-coverage]: https://img.shields.io/sonar/coverage/phpcfdi_image-captcha-resolver/main?logo=sonarcloud&server=https%3A%2F%2Fsonarcloud.io
[badge-violations]: https://img.shields.io/sonar/violations/phpcfdi_image-captcha-resolver/main?format=long&logo=sonarcloud&server=https%3A%2F%2Fsonarcloud.io
[badge-downloads]: https://img.shields.io/packagist/dt/phpcfdi/image-captcha-resolver?logo=packagist
